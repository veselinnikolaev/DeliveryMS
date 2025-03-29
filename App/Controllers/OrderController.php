<?php

namespace App\Controllers;

use App\Models\Order;
use Core\Controller;

class OrderController extends Controller {

    var $layout = 'admin';
    var $settings;

    public function __construct() {
        $this->settings = $this->loadSettings();
    }

    function loadSettings() {
        $settingModel = new \App\Models\Setting();
        $settings = $settingModel->getAll();
        $app_settings = [];
        foreach ($settings as $setting) {
            $app_settings[$setting['key']] = $setting['value'];
        }
        return $app_settings;
    }

    function list($layout = 'admin') {
        $orderModel = new \App\Models\Order();
        $userModel = new \App\Models\User();

        $opts = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['customerName'])) {
                $opts["user_id IN (SELECT id FROM users WHERE name LIKE '%" . $_POST['customerName'] . "%')"] = "1";
            }
            if (!empty($_POST['courierName'])) {
                $opts["courier_id IN (SELECT id FROM couriers WHERE name LIKE '%" . $_POST['courierName'] . "%')"] = "1";
            }
            if (!empty($_POST['status'])) {
                $opts["status LIKE '%" . $_POST['status'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['trackingNumber'])) {
                $opts["tracking_number LIKE '%" . $_POST['trackingNumber'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['country'])) {
                $opts["country LIKE '%" . $_POST['country'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['region'])) {
                $opts["region LIKE '%" . $_POST['region'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['orderDateFrom'])) {
                $opts["delivery_date >= '" . strtotime($_POST['orderDateFrom']) . "'"] = "1";
            }
            if (!empty($_POST['orderDateTo'])) {
                $opts["delivery_date <= '" . strtotime($_POST['orderDateTo']) . "'"] = "1";
            }
            if (!empty($_POST['minTotalPrice'])) {
                $opts["total_amount >= '" . $_POST['minTotalPrice'] . "'"] = "1";
            }
            if (!empty($_POST['maxTotalPrice'])) {
                $opts["total_amount <= '" . $_POST['maxTotalPrice'] . "'"] = "1";
            }
        }

        // Retrieve all orders from the database
        if (!empty($_GET['user_id']) && $_GET['user_id'] == $_SESSION['user']['id']) { //User role checking orders
            $opts['user_id'] = $_GET['user_id'];
        }

        // Retrieve all orders from the database
        if (!empty($_GET['courier_id']) && $_GET['courier_id'] == $_SESSION['user']['id']) { //User role checking orders
            $opts['courier_id'] = $_GET['courier_id'];
        }
        
        $orders = $orderModel->getAll($opts);

        // Format orders for display
        foreach ($orders as &$order) {
            $order['customer_name'] = $userModel->get($order['user_id'])['name'] ?? 'Unknown';
            $order['courier_name'] = $userModel->get($order['courier_id'])['name'] ?? 'Unknown';
            $order['delivery_date'] = date($this->settings['date_format'], $order['delivery_date']);
        }

        // Pass the data to the view
        $arr = [
            'orders' => $orders,
            'currency' => $this->settings['currency_code']
        ];

        $this->view($layout, $arr);
    }

    function filter() {
        $this->list('ajax');
    }

    function create() {
        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
            exit;
        }
        if ($_SESSION['user']['role'] == 'user') {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }

        $orderModel = new \App\Models\Order();
        $orderProductsModel = new \App\Models\OrderProducts();
        $productModel = new \App\Models\Product();
        $userModel = new \App\Models\User();
        $notificationModel = new \App\Models\Notification();
        $mailer = new \App\Helpers\mailer\Mailer();
        $currency = $this->settings['currency_code'];

        if (!empty($_POST['send'])) {
            $productIds = $_POST['product_id'];
            $quantities = $_POST['quantity'];

            // Validate quantities against available product quantities
            $quantityError = false;
            $error_message = null;

            foreach ($productIds as $key => $productId) {
                $product = $productModel->get($productId);
                if ($quantities[$key] > $product['stock']) {
                    $error_message = "Quantity for {$product['name']} exceeds available stock.";
                    $quantityError = true;
                    break;
                }
            }

            if (!$quantityError) {
                $priceDetails = $this->calculateOrderTotal($productIds, $quantities);
                $orderData = [
                    'last_processed' => time(),
                    'tracking_number' => \Utility::generateRandomString(),
                    'delivery_date' => strtotime($_POST['delivery_date']),
                    'total_amount' => $priceDetails['total'],
                    'created_at' => time()
                ];

                $orderId = $orderModel->save($orderData + $_POST);

                if ($orderId) {
                    // Save order products and update product quantities
                    foreach ($productIds as $key => $productId) {
                        $productDetails = $productModel->get($productId);
                        $subtotal = $productDetails['price'] * $quantities[$key];

                        $orderProductData = [
                            'order_id' => $orderId,
                            'product_id' => $productId,
                            'quantity' => $quantities[$key],
                            'price' => $productDetails['price'],
                            'subtotal' => $subtotal,
                        ];

                        if (!$orderProductsModel->save($orderProductData)) {
                            $error_message = "Failed to save order products. Please try again.";
                            break;
                        }

                        // Update product quantity after order product is saved
                        $updatedQuantity = $productDetails['stock'] - $quantities[$key];
                        $updateSuccess = $productModel->update([
                            'id' => $productId,
                            'stock' => $updatedQuantity
                        ]);

                        if (!$updateSuccess) {
                            $error_message = "Failed to update product stock for {$productDetails['name']}. Please try again.";
                            break;
                        }
                    }

                    if (!isset($error_message)) {
                        $notificationModel->save([
                            'user_id' => $_POST['user_id'],
                            'message' => "Your order #$orderId has been created successfully!",
                            'link' => INSTALL_URL . "?controller=Order&action=details&id=$orderId",
                            'created_at' => time()
                        ]);
                        if ($this->settings['email_sending'] == 'enabled') {
                            $order = $orderModel->get($orderId);
                            $customer = $userModel->get($order['user_id']);
                            $courier = $userModel->get($order['courier_id']);
                            $orderProducts = $orderProductsModel->getAll(['order_id' => $orderId]);

                            foreach ($orderProducts as &$product) {
                                $productDetails = $productModel->get($product['product_id']);
                                $product['name'] = $productDetails['name'] ?? 'Unknown';
                            }

                            $emailContent = $this->generateOrderEmail($order, $customer, $courier, $orderProducts, "Order Confirmation");

                            $mailer->sendMail($customer['email'], "Order Confirmation #{$orderId}", $emailContent);
                        }
                        header("Location: " . INSTALL_URL . "?controller=Order&action=list", true, 301);
                        exit;
                    }
                } else {
                    $error_message = "Failed to create the order. Please try again.";
                }
            }
        }

        $arr = [
            'users' => $userModel->getAll(),
            'products' => $productModel->getAll(),
            'couriers' => $userModel->getAll(['role' => 'courier']),
            'currency' => $currency,
            'error_message' => $error_message ?? null
        ];
        $this->view($this->layout, $arr);
    }

    function details() {
        $orderModel = new \App\Models\Order();
        $orderProductsModel = new \App\Models\OrderProducts();
        $productModel = new \App\Models\Product();
        $userModel = new \App\Models\User();

        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
            exit;
        }

        if (empty($_GET['id'])) {
            header("Location: " . INSTALL_URL . "?controller=Order&action=list", true, 301);
            exit;
        }

        if ($_SESSION['user']['role'] == 'user') {
            $userOrders = $orderModel->getAll(['user_id' => $_SESSION['user']['id']]);
            $userOrderIds = array_column($userOrders, 'id');
            if (!in_array($_GET['id'], $userOrderIds)) {
                header("Location: " . INSTALL_URL, true, 301);
                exit;
            }
        }

        $orderId = intval($_GET['id']);
        $orderData = $orderModel->get($orderId);

        if (!$orderData) {
            header("Location: " . INSTALL_URL . "?controller=Order&action=list", true, 301);
            exit;
        }

        $customerData = $userModel->get($orderData['user_id']);
        $courierData = $userModel->get($orderData['courier_id']);

        $opts = array();
        $opts['order_id'] = $orderId;
        $orderProducts = $orderProductsModel->getAll($opts);

        foreach ($orderProducts as &$product) {
            $productDetails = $productModel->get($product['product_id']);
            $product['name'] = $productDetails['name'] ?? 'Unknown';
        }

        $data = [
            'order' => $orderData,
            'customer' => $customerData,
            'courier' => $courierData,
            'products' => $orderProducts,
            'currency' => $this->settings['currency_code']
        ];

        $this->view($this->layout, $data);
    }

    function delete() {
        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
            exit;
        }
        if ($_SESSION['user']['role'] == 'user') {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }

        $productModel = new \App\Models\Product();
        $orderModel = new \App\Models\Order();
        $orderProductsModel = new \App\Models\OrderProducts();
        $userModel = new \App\Models\User();

        if (!empty($_POST['id'])) {
            $orderId = $_POST['id'];

            $orderProducts = $orderProductsModel->getAll(['order_id' => $orderId]);
            foreach ($orderProducts as $orderProduct) {
                $product = $productModel->getFirstBy(['id' => $orderProduct['product_id']]);
                $product['stock'] += $orderProduct['quantity'];
                $productModel->update($product);
            }
            $orderProductsModel->deleteBy(['order_id' => $orderId]);

            $orderModel->delete($orderId);
        }

        // Retrieve all orders from the database
        $orders = $orderModel->getAll();

        // Format orders for display
        foreach ($orders as &$order) {
            $order['customer_name'] = $userModel->get($order['user_id'])['name'] ?? 'Unknown';
            $order['name'] = $userModel->get($order['courier_id'])['name'] ?? 'Unknown';
            $order['delivery_date'] = date($this->settings['date_format'], $order['delivery_date']);
        }

        $this->view('ajax', ['orders' => $orders, 'currency' => $this->settings['currency_code']]);
    }

    function pay() {
        if (!empty($_GET['order_id'])) {
            $orderId = $_GET['order_id'];
            $orderModel = new \App\Models\Order();
            $userModel = new \App\Models\User();
            $orderProductsModel = new \App\Models\OrderProducts();

            $order = $orderModel->get($orderId);
            $user = $userModel->get($order['user_id']);
            $orderProducts = $orderProductsModel->getAll(['order_id' => $orderId]);

            $this->view($this->layout, [
                'currency_code' => $this->settings['currency_code'],
                'order' => $order,
                'user' => $user,
                'order_products' => $orderProducts
            ]);
        }
    }

    // Controller method to handle the return from PayPal
    public function pay_success() {
        // Get the order ID from the URL parameter
        $orderId = $_GET['order_id'];

        $orderModel = new \App\Models\Order();
        $userModel = new \App\Models\User();
        // Load the order from the database
        $order = $orderModel->get($orderId);
        $user = $userModel->getFirstBy(['id' => $order['user_id']]);

        // If the order exists and the payment was successful, mark it as paid
        if ($order) {
            // Show a success message or redirect to a success page
            $this->view($this->layout, ['order' => $order, 'user' => $user]);
        }
    }

    // Controller method to handle the cancellation from PayPal
    public function pay_cancel() {
        // Get the order ID from the URL parameter
        $orderId = $_GET['order_id'];

        $orderModel = new \App\Models\Order();
        $userModel = new \App\Models\User();
        // Load the order from the database
        $order = $orderModel->get($orderId);
        $user = $userModel->getFirstBy(['id' => $order['user_id']]);

        if ($order) {
            // Show a cancellation message or redirect to a cancellation page
            $this->view($this->layout, ['order' => $order, 'user' => $user]);
        }
    }

    function paypal_ipn() {
        // PayPal verifies the IPN message
        $orderModel = new \App\Models\Order();
        $notificationModel = new \App\Models\Notification();
        $userModel = new \App\Models\User();
        $orderId = $_POST['custom']; // Get the order ID from PayPal's "custom" field
        $order = $orderModel->get($orderId);
        $user = $userModel->getFirstBy(['id' => $order['user_id']]);

        // Step 1: Verify IPN message with PayPal (to avoid fraud)
        $url = 'https://www.paypal.com/cgi-bin/webscr';
        $data = array(
            'cmd' => '_notify-validate',
            'tx' => $_POST['txn_id'], // PayPal transaction ID
            'amt' => $_POST['mc_gross'], // Total amount paid
            'currency_code' => $_POST['mc_currency'], // Currency code
        );

        // Send the IPN data back to PayPal for validation
        $response = file_get_contents($url . '?' . http_build_query($data));

        // Step 2: If PayPal confirms the payment is valid
        if ($response == "VERIFIED") {
            // Update the order status based on payment confirmation
            if ($_POST['payment_status'] == 'Completed') {
                // Payment is successful, update order status
                $order['status'] = 'shipped';
                $orderModel->update($order);
                $notificationModel->save([
                    'user_id' => $user['id'],
                    'message' => "Your order #$orderId has been paid successfully!",
                    'link' => INSTALL_URL . "?controller=Order&action=pay_success&order_id=$orderId",
                    'created_at' => time()
                ]);
            }
        } else {
            // Payment not verified, handle the error (perhaps log it)
            error_log("Invalid IPN message: " . json_encode($_POST));
        }

        // Step 3: Handle canceled or failed payment (if needed)
        if ($_POST['payment_status'] == 'Failed' || $_POST['payment_status'] == 'Canceled') {
            // Update the order status as canceled
            $order['status'] = 'cancelled';
            $orderModel->update($order);
            $notificationModel->save([
                'user_id' => $user['id'],
                'message' => "Your order #$orderId has been cancelled!",
                'link' => INSTALL_URL . "?controller=Order&action=pay_cancel&order_id=$orderId",
                'created_at' => time()
            ]);
        }
    }

    function bulkDelete() {
        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
            exit;
        }
        if ($_SESSION['user']['role'] == 'user') {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }

        $orderModel = new \App\Models\Order();
        $orderProductsModel = new \App\Models\OrderProducts();
        $userModel = new \App\Models\User();

        if (!empty($_POST['ids']) && is_array($_POST['ids'])) {
            $orderIds = $_POST['ids'];

            $inOrderIds = implode(', ', $orderIds);
            $optsForOrderProduct = ["order_id IN ($inOrderIds) AND 1 " => '1'];
            $orderProductsModel->deleteBy($optsForOrderProduct);
            $optsForOrder = ["id IN ($inOrderIds) AND 1 " => '1'];
            $orderModel->deleteBy($optsForOrder);
        }

// Retrieve all orders from the database
        $orders = $orderModel->getAll();

// Format orders for display
        foreach ($orders as &$order) {
            $order['customer_name'] = $userModel->get($order['user_id'])['name'] ?? 'Unknown';
            $order['name'] = $userModel->get($order['courier_id'])['name'] ?? 'Unknown';
            $order['delivery_date'] = date($this->settings['date_format'], $order['delivery_date']);
        }

        $this->view('ajax', ['orders' => $orders, 'currency' => $this->settings['currency_code']]);
    }

    function print() {
        if (isset($_POST['orderData'])) {
            // Decode the JSON data
            $orders = json_decode($_POST['orderData'], true);

            if (!$orders || empty($orders)) {
                echo "No orders to print";
                exit;
            }
        }

        $this->view('ajax', ['orders' => $orders]);
    }

    function edit() {
        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
            exit;
        }
        if ($_SESSION['user']['role'] == 'user') {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }

        $orderModel = new \App\Models\Order();
        $orderProductsModel = new \App\Models\OrderProducts();
        $productModel = new \App\Models\Product();
        $userModel = new \App\Models\User();
        $notificationModel = new \App\Models\Notification();
        $mailer = new \App\Helpers\mailer\Mailer();
        $currency = $this->settings['currency_code'];

        if (!empty($_POST['id'])) {
            $orderId = $_POST['id'];
            $order = $orderModel->get($orderId);
            $currentOrderProducts = $orderProductsModel->getAll(['order_id' => $orderId]);

            $currentQuantities = [];
            $productIds = array_column($currentOrderProducts, 'product_id');
            $productData = $productModel->getMultiple($productIds);

            foreach ($currentOrderProducts as $product) {
                $currentQuantities[$product['product_id']] = ($currentQuantities[$product['product_id']] ?? 0) + $product['quantity'];
            }

            $quantityError = false;
            $newQuantities = [];
            $newOrderProducts = [];

            foreach ($_POST['product_id'] as $key => $productId) {
                $quantity = $_POST['quantity'][$key];
                $newQuantities[$productId] = ($newQuantities[$productId] ?? 0) + $quantity;
                $newOrderProducts[] = ['product_id' => $productId, 'quantity' => $quantity];
            }

            foreach ($newQuantities as $productId => $newTotalQuantity) {
                $product = $productData[$productId] ?? $productModel->get($productId);
                $currentTotalQuantity = $currentQuantities[$productId] ?? 0;
                $stockChange = $newTotalQuantity - $currentTotalQuantity;
                $updatedStock = $product['stock'] - $stockChange;

                if ($updatedStock < 0) {
                    $error_message = "Quantity for {$product['name']} exceeds available stock.";
                    $quantityError = true;
                    break;
                }
            }

            if (!$quantityError) {
                $priceDetails = $this->calculateOrderTotal(array_keys($newQuantities), array_values($newQuantities));

                $orderData = [
                    'last_processed' => time(),
                    'tracking_number' => $order['tracking_number'],
                    'delivery_date' => strtotime($_POST['delivery_date']),
                    'total_amount' => $priceDetails['total']
                ];

                if (!$orderModel->update(['id' => $orderId] + $orderData + $_POST)) {
                    $error_message = "Failed to update order with id " . $orderId;
                }

                $orderProductsModel->deleteBy(['order_id' => $orderId]);

                // Update stock for products based on total difference
                foreach ($newOrderProducts as $orderProduct) {
                    $productId = $orderProduct['product_id'];
                    $quantity = $orderProduct['quantity'];
                    $productDetails = $productData[$productId] ?? $productModel->get($productId);
                    $subtotal = $productDetails['price'] * $quantity;

                    $orderProductsModel->save([
                        'order_id' => $orderId,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $productDetails['price'],
                        'subtotal' => $subtotal
                    ]);
                }

                foreach ($newQuantities as $productId => $newTotalQuantity) {
                    $productDetails = $productData[$productId] ?? $productModel->get($productId);
                    $currentTotalQuantity = $currentQuantities[$productId] ?? 0;
                    $stockChange = $newTotalQuantity - $currentTotalQuantity;
                    $updatedStock = $productDetails['stock'] - $stockChange;

                    if (!$productModel->update(['id' => $productId, 'stock' => $updatedStock])) {
                        $error_message = "Failed to update product stock for {$productDetails['name']}. Please try again.";
                        break;
                    }
                }
            }

            if (!isset($error_message)) {
                $notificationModel->save([
                    'user_id' => $_POST['user_id'],
                    'message' => "Your order #$orderId has been edited successfully!",
                    'link' => INSTALL_URL . "?controller=Order&action=details&id=$orderId",
                    'created_at' => time()
                ]);
                if ($this->settings['email_sending'] == 'enabled') {
                    $order = $orderModel->get($orderId);
                    $customer = $userModel->get($order['user_id']);
                    $courier = $userModel->get($order['courier_id']);

                    $orderProducts = $orderProductsModel->getAll(['order_id' => $orderId]);
                    foreach ($orderProducts as &$orderProduct) {
                        $orderProductDetails = $productModel->get($orderProduct['product_id']);
                        $orderProduct['name'] = $orderProductDetails['name'] ?? 'Unknown';
                    }

                    $emailContent = $this->generateOrderEmail($order, $customer, $courier, $orderProducts, "Order Update");

                    $mailer->sendMail($customer['email'], "Order Update #{$orderId}", $emailContent);
                }
                header("Location: " . INSTALL_URL . "?controller=Order&action=list", true, 301);
                exit;
            }
        }

        $orderId = $_GET['order_id'];
        $orderProducts = $orderProductsModel->getAll(['order_id' => $orderId]);

        $productQuantities = [];
        foreach ($orderProducts as $orderProduct) {
            $productId = $orderProduct['product_id'];
            if (!isset($productQuantities[$productId])) {
                $productQuantities[$productId] = 0;
            }
            $productQuantities[$productId] += $orderProduct['quantity'];
        }

        $arr = [
            'order' => $orderModel->get($orderId),
            'orderProducts' => $orderProducts,
            'users' => $userModel->getAll(),
            'products' => $productModel->getAll(),
            'couriers' => $userModel->getAll(['role' => 'courier']),
            'productQuantities' => $productQuantities,
            'currency' => $currency,
            'error_message' => $error_message ?? null
        ];

        $this->view($this->layout, $arr);
    }

    function calculatePrice() {
        $price_arr = $this->calculateOrderTotal($_POST['product_id'], $_POST['quantity']);
        header('Content-Type: application/json');

        echo json_encode($price_arr);
    }

    private function calculateOrderTotal(array $productIds, array $quantities): array {
        $productModel = new \App\Models\Product();
        $productPrice = 0;

        foreach ($productIds as $key => $productId) {
            $product = $productModel->get($productId);
            $productPrice += $product['price'] * $quantities[$key];
        }

        $shippingPrice = ($productPrice * $this->settings['shipping_rate']) / 100;
        $tax = ($productPrice * $this->settings['tax_rate']) / 100;
        $total = $productPrice + $tax + $shippingPrice;

        return [
            'product_price' => number_format($productPrice, 2),
            'shipping_price' => number_format($shippingPrice, 2),
            'tax' => number_format($tax, 2),
            'total' => number_format($total, 2),
        ];
    }

    function export() {
// Check if orderData is provided
        if (isset($_POST['orderData'])) {
            // Decode the JSON data
            $orders = json_decode($_POST['orderData'], true);

            if (!$orders || empty($orders)) {
                echo "No orders to export";
                exit;
            }
        }

        $format = isset($_POST['format']) ? $_POST['format'] : 'pdf';

// Export based on format
        switch ($format) {
            case 'pdf':
                $this->exportAsPDF($orders);
                break;
            case 'excel':
                $this->exportAsExcel($orders);
                break;
            case 'csv':
                $this->exportAsCSV($orders);
                break;
            default:
                echo "Invalid export format";
                exit;
        }
    }

    private function exportAsPDF($orders) {
        if (ob_get_level()) {
            ob_end_clean();
        }
        require_once(__DIR__ . '/../Helpers/export/tcpdf/tcpdf.php');

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('Your App');
        $pdf->SetTitle('Orders Export');
        $pdf->SetHeaderData('', 0, 'Orders List', '');
        $pdf->setHeaderFont(array('helvetica', '', 12));
        $pdf->setFooterFont(array('helvetica', '', 10));
        $pdf->SetDefaultMonospacedFont('courier');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        $pdf->AddPage();

// Generate HTML table with dynamic headers
        $html = $this->generateDynamicOrderTable($orders);
        $pdf->writeHTML($html, true, false, true, false, '');

// Output PDF
        $pdf->Output('orders_export.pdf', 'D');
        exit;
    }

    private function generateDynamicOrderTable($orders) {
// Start HTML table
        $html = '<table border="1" cellpadding="5">
<thead>
    <tr>';

// Define preferred header names
        $preferredHeaders = [
            'id' => 'Order ID',
            'tracking_number' => 'Tracking Number',
            'customer_name' => 'Customer',
            'courier_name' => 'Courier',
            'delivery_date' => 'Delivery Date',
            'formatted_total' => 'Total Price',
            'address' => 'Address',
            'country' => 'Country',
            'region' => 'Region',
            'status_text' => 'Status'
        ];

        if (!empty($orders) && is_array($orders[0])) {
            $orderedKeys = array_keys($orders[0]); // Get the exact order from first item
            // Generate headers in the exact same order as the first item in $orders
            foreach ($orderedKeys as $key) {
                if (!in_array($key, ['user_id', 'courier_id', 'status', 'total_amount'])) {
                    $displayName = $preferredHeaders[$key] ?? ucwords(str_replace('_', ' ', $key));
                    $html .= '<th>' . htmlspecialchars($displayName) . '</th>';
                }
            }

            $html .= '</tr>
    </thead>
    <tbody>';

            // Add order data
            foreach ($orders as $order) {
                $html .= '<tr>';

                foreach ($orderedKeys as $key) {
                    if (!in_array($key, ['user_id', 'courier_id', 'status', 'total_amount'])) {
                        $value = $order[$key] ?? 'N/A';
                        $html .= '<td>' . htmlspecialchars($value) . '</td>';
                    }
                }

                $html .= '</tr>';
            }
        } else {
            // Fallback for no data
            $html .= '<th>No Data Available</th></tr></thead><tbody><tr><td>No orders found</td></tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    private function exportAsExcel($orders) {
        require(__DIR__ . '/../Helpers/export/simplexlsxgen/src/SimpleXLSXGen.php');

        $data = [];

        if (!empty($orders) && is_array($orders[0])) {
            // Вземи оригиналния ред на колоните от първия елемент
            $headerRow = array_keys($orders[0]);
            $data[] = array_map(fn($h) => ucwords(str_replace('_', ' ', $h)), $headerRow);

            // Добави данните в същия ред
            foreach ($orders as $order) {
                $data[] = array_map(fn($key) => $order[$key] ?? 'N/A', $headerRow);
            }
        } else {
            $data[] = ['No Data Available'];
        }

        \Shuchkin\SimpleXLSXGen::fromArray($data)->downloadAs('orders_export.xlsx');
        exit;
    }

    private function exportAsCSV($orders) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="orders_export.csv"');

        $output = fopen('php://output', 'w');

        if (!empty($orders) && is_array($orders[0])) {
            // Вземи оригиналния ред на колоните
            $headerRow = array_keys($orders[0]);
            fputcsv($output, array_map(fn($h) => ucwords(str_replace('_', ' ', $h)), $headerRow));

            foreach ($orders as $order) {
                fputcsv($output, array_map(fn($key) => $order[$key] ?? 'N/A', $headerRow));
            }
        } else {
            fputcsv($output, ['No data available']);
        }

        fclose($output);
        exit;
    }

    private function generateOrderEmail($order, $customer, $courier, $products, $title) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>

            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Order Confirmation</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f4;
                        margin: 0;
                        padding: 0;
                    }

                    .email-container {
                        max-width: 600px;
                        margin: 20px auto;
                        background: #ffffff;
                        border-radius: 10px;
                        overflow: hidden;
                        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    }

                    .header {
                        background: #0073e6;
                        color: #ffffff;
                        text-align: center;
                        padding: 20px;
                        font-size: 24px;
                    }

                    .content {
                        padding: 20px;
                    }

                    .order-details {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 20px;
                        margin-bottom: 20px;
                    }

                    .detail-column {
                        flex: 1 1 45%;
                    }

                    .detail-column p {
                        margin: 5px 0;
                        font-size: 14px;
                    }

                    .products-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 20px;
                    }

                    .products-table th,
                    .products-table td {
                        padding: 12px;
                        border: 1px solid #ddd;
                        text-align: left;
                    }

                    .products-table th {
                        background: #0073e6;
                        color: white;
                        font-weight: bold;
                    }

                    .footer {
                        text-align: center;
                        padding: 15px;
                        background: #f8f8f8;
                        font-size: 12px;
                        color: #666;
                    }

                    @media screen and (max-width: 600px) {
                        .order-details {
                            flex-direction: column;
                        }

                        .detail-column {
                            width: 100%;
                        }
                    }
                </style>
            </head>

            <body>
                <div class="email-container">
                    <div class="header">
                        <?= htmlspecialchars($title) ?>
                    </div>
                    <div class="content">
                        <p style="font-size: 16px; color: #333;">Thank you for your order! Below are the details:</p>
                        <div class="order-details">
                            <div class="detail-column">
                                <p><strong>Order ID:</strong>
                                    <?= htmlspecialchars($order['id']) ?>
                                </p>
                                <p><strong>Customer:</strong>
                                    <?= htmlspecialchars($customer['name']) ?>
                                </p>
                                <p><strong>Address:</strong>
                                    <?= htmlspecialchars($order['address']) ?>
                                </p>
                                <p><strong>Country:</strong>
                                    <?= htmlspecialchars($order['country']) ?>
                                </p>
                                <p><strong>Region:</strong>
                                    <?= htmlspecialchars($order['region']) ?>
                                </p>
                            </div>
                            <div class="detail-column">
                                <p><strong>Tracking Number:</strong>
                                    <?php echo htmlspecialchars($order['tracking_number']); ?>
                                </p>
                                <p><strong>Courier:</strong>
                                    <?= htmlspecialchars($courier['name']) ?>
                                </p>
                                <p><strong>Delivery Date:</strong>
                                    <?= date($this->settings['date_format'], $order['delivery_date']) ?>
                                </p>
                                <p><strong>Status:</strong>
                                    <?= \Utility::$order_status[$order['status']] ?? 'Unknown' ?>
                                </p>
                                <p><strong>Total Price:</strong>
                                    <?= \Utility::getDisplayableAmount(htmlspecialchars(number_format($order['total_amount'], 2))) ?>
                                </p>
                            </div>
                        </div>
                        <h3 style="color: #0073e6; margin-top: 20px;">Order Summary</h3>
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product) { ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($product['name']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($product['quantity']) ?>
                                        </td>
                                        <td>
                                            <?= \Utility::getDisplayableAmount(htmlspecialchars(number_format($product['price'], 2))) ?>
                                        </td>
                                        <td>
                                            <?= \Utility::getDisplayableAmount(htmlspecialchars(number_format($product['subtotal'], 2))) ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="footer">
                        <p>If you have any questions, please contact our customer service.</p>
                        <p>This is an automated email, please do not reply.</p>
                    </div>
                </div>
            </body>

        </html>
        <?php
        return ob_get_clean();
    }
}
