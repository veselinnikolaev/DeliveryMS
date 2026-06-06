<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\OrderProducts;
use App\Models\Product;
use App\Models\Notification;
use Core\Services\ExportService;
use Core\Services\MailService;
use Core\Security;
use Core\Controller;
use Core\Exceptions\DatabaseException;

class OrderController extends Controller {

    protected string $layout = 'admin';

    public function __construct() {
        parent::__construct();
    }

    function list($layout = 'admin'): void {
        try {
            $orderModel = new Order();
            $userModel = new User();

            $opts = array();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle customer name filter - fetch matching user IDs first
            if (!empty($this->post('customerName'))) {
                $customerName = '%' . $this->post('customerName') . '%';
                $matchingUsers = $userModel->getAll(['name' => $customerName]);
                if (!empty($matchingUsers)) {
                    $userIds = array_column($matchingUsers, 'id');
                    $opts['user_id'] = $userIds;
                } else {
                    // No matching users, return empty result
                    $opts['user_id'] = [0]; // Impossible ID to return no results
                }
            }
            // Handle courier name filter - fetch matching courier IDs first
            if (!empty($this->post('courierName'))) {
                $courierName = '%' . $this->post('courierName') . '%';
                $matchingCouriers = $userModel->getAll(['name' => $courierName, 'role' => 'courier']);
                if (!empty($matchingCouriers)) {
                    $courierIds = array_column($matchingCouriers, 'id');
                    $opts['courier_id'] = $courierIds;
                } else {
                    // No matching couriers, return empty result
                    $opts['courier_id'] = [0]; // Impossible ID to return no results
                }
            }
            // Handle status filter - use LIKE for partial matching
            if (!empty($this->post('status'))) {
                $opts['status LIKE'] = '%' . $this->post('status') . '%';
            }
            // Handle tracking number filter - use LIKE for partial matching
            if (!empty($this->post('trackingNumber'))) {
                $opts['tracking_number LIKE'] = '%' . $this->post('trackingNumber') . '%';
            }
            // Handle country filter - use LIKE for partial matching
            if (!empty($this->post('country'))) {
                $opts['country LIKE'] = '%' . $this->post('country') . '%';
            }
            // Handle region filter - use LIKE for partial matching
            if (!empty($this->post('region'))) {
                $opts['region LIKE'] = '%' . $this->post('region') . '%';
            }
            // Handle date range filters
            if (!empty($this->post('orderDateFrom'))) {
                $opts['delivery_date >='] = strtotime($this->post('orderDateFrom'));
            }
            if (!empty($this->post('orderDateTo'))) {
                $opts['delivery_date <='] = strtotime($this->post('orderDateTo'));
            }
            // Handle price range filters
            if (!empty($this->post('minTotalPrice'))) {
                $opts['total_amount >='] = Security::float($this->post('minTotalPrice'));
            }
            if (!empty($this->post('maxTotalPrice'))) {
                $opts['total_amount <='] = Security::float($this->post('maxTotalPrice'));
            }
        }

// Retrieve all orders from the database
        if (!empty($this->get('user_id')) && $this->get('user_id') == $_SESSION['user']['id']) { //User role checking orders
            $opts['user_id'] = Security::int($this->get('user_id'));
        }

// Retrieve all orders from the database
        if (!empty($this->get('courier_id')) && $this->get('courier_id') == $_SESSION['user']['id']) { //User role checking orders
            $opts['courier_id'] = Security::int($this->get('courier_id'));
        }

        $orders = $orderModel->getAll($opts);

// Format orders for display
            foreach ($orders as &$order) {
                $customer = $order['user_id'] ? $userModel->get($order['user_id']) : null;
                $order['customer_name'] = $customer['name'] ?? 'Unknown';
                $courier = $order['courier_id'] ? $userModel->get($order['courier_id']) : null;
                $order['courier_name'] = ($courier && $courier['role'] === 'courier') ? $courier['name'] : 'Unknown';
                $order['delivery_date'] = $order['delivery_date'] ? date($this->settings['date_format'], $order['delivery_date']) : '';
            }

// Pass the data to the view
        $arr = [
            'orders' => $orders,
            'currency' => $this->settings['currency_code']
        ];

        $this->view($layout, $arr);
        } catch (DatabaseException $e) {
            error_log("Database error in OrderController::list: " . $e->getMessage());
            $this->view($layout, ['orders' => [], 'currency' => $this->settings['currency_code'], 'error_message' => 'An error occurred while loading orders. Please try again.']);
        }
    }

    function filter() {
        $this->list('ajax');
    }

    function create() {
        try {
            if (empty($_SESSION['user'])) {
            $this->redirect(INSTALL_URL . "?controller=Auth&action=login");
        }
        if ($_SESSION['user']['role'] == 'user') {
            $this->redirect($_SESSION['previous_url']);
        }

        $orderModel = new Order();
        $orderProductsModel = new OrderProducts();
        $productModel = new Product();
        $userModel = new User();
        $notificationModel = new Notification();
        $mailer = new MailService();
        $currency = $this->settings['currency_code'];

        if (!empty($this->post('send'))) {
            $productIds = $this->post('product_id');
            $quantities = $this->post('quantity');

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
                    'delivery_date' => strtotime($this->post('delivery_date')),
                    'total_amount' => $priceDetails['total'],
                    'created_at' => time()
                ];

                $postData = $this->post();
                $orderId = $orderModel->save($orderData + $postData);

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
// Notify customer
                        $notificationModel->save([
                            'user_id' => Security::int($this->post('user_id')),
                            'message' => "New order #{$orderId} has been created. Total: " . \Utility::getDisplayableAmount($priceDetails['total']),
                            'link' => INSTALL_URL . "?controller=Order&action=details&id=$orderId",
                            'created_at' => time()
                        ]);

// Notify courier
                        $notificationModel->save([
                            'user_id' => Security::int($this->post('courier_id')),
                            'message' => "New delivery assigned to you. Order #{$orderId}",
                            'link' => INSTALL_URL . "?controller=Order&action=details&id=$orderId",
                            'created_at' => time()
                        ]);

// Check for low stock and notify admins
                        foreach ($productIds as $key => $productId) {
                            $product = $productModel->get($productId);
                            if ($product['stock'] < 10) { // Threshold for low stock
                                $adminUsers = $userModel->getAll(['role' => 'admin']);
                                foreach ($adminUsers as $admin) {
                                    $notificationModel->save([
                                        'user_id' => $admin['id'],
                                        'message' => "Low stock alert: {$product['name']} (Only {$product['stock']} left)",
                                        'link' => INSTALL_URL . "?controller=Product&action=edit&id=$productId",
                                        'created_at' => time()
                                    ]);
                                }
                            }
                        }

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
                        $this->redirect($_SESSION['previous_url']);
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
        } catch (DatabaseException $e) {
            error_log("Database error in OrderController::create: " . $e->getMessage());
            $arr = [
                'users' => $userModel->getAll() ?? [],
                'products' => $productModel->getAll() ?? [],
                'couriers' => $userModel->getAll(['role' => 'courier']) ?? [],
                'currency' => $currency,
                'error_message' => 'An error occurred while creating the order. Please try again.'
            ];
            $this->view($this->layout, $arr);
        }
    }

    public function changeStatus() {
        try {
            if ($_SESSION['user']['role'] != 'courier') {
            $this->redirect(INSTALL_URL);
        }

        $orderModel = new Order();
        $userModel = new User();

        if (!empty($this->post('ids')) && !empty($this->post('status'))) {
            $status = $this->post('status');
            $ids = $this->post('ids');

            if (in_array($status, ['delivered', 'returned'])) {
                $orderModel->updateBy(['status' => $status], ['id' => $ids]);

                foreach ($ids as $orderId) {
                    $order = $orderModel->get($orderId);
                    if (empty($order)) continue;
                    $notificationModel = new Notification();
                    $notificationModel->save([
                            'user_id' => $order['user_id'],
                            'message' => "Your order #{$orderId} has been " . $status,
                            'link' => INSTALL_URL . "?controller=Order&action=details&id=$orderId",
                            'created_at' => time()
                    ]);
                }
            }
        }

// Return refreshed user list
        $orders = $orderModel->getAll(['courier_id' => Security::int($_SESSION['user']['id'])]);

        foreach ($orders as &$order) {
            $order['customer_name'] = $userModel->get($order['user_id'])['name'] ?? 'Unknown';
            $courier = $userModel->get($order['courier_id']);
            $order['courier_name'] = ($courier && $courier['role'] === 'courier') ? $courier['name'] : 'Unknown';
            $order['delivery_date'] = date($this->settings['date_format'], $order['delivery_date']);
        }

        $this->view('ajax', ['orders' => $orders]);
        } catch (DatabaseException $e) {
            error_log("Database error in OrderController::changeStatus: " . $e->getMessage());
            $this->view('ajax', ['orders' => [], 'error_message' => 'An error occurred while updating order status.']);
        }
    }

    function details(): void {
        try {
            $orderModel = new Order();
        $orderProductsModel = new OrderProducts();
        $productModel = new Product();
        $userModel = new User();

        if (empty($_SESSION['user'])) {
            $this->redirect(INSTALL_URL . "?controller=Auth&action=login");
        }

        if (empty($this->get('id'))) {
            $this->redirect($_SESSION['previous_url']);
        }

        if ($_SESSION['user']['role'] == 'user') {
            $userOrders = $orderModel->getAll(['user_id' => Security::int($_SESSION['user']['id'])]);
            $userOrderIds = array_column($userOrders, 'id');
            if (!in_array($this->get('id'), $userOrderIds)) {
                $this->redirect(INSTALL_URL);
            }
        }

        $orderId = Security::int($this->get('id'));
        $orderData = $orderModel->get($orderId);

        if (!$orderData) {
            $this->redirect($_SESSION['previous_url']);
        }

        $customerData = $userModel->get($orderData['user_id']);
        $courierData = $orderData['courier_id'] ? $userModel->get($orderData['courier_id']) : null;
        
        // Ensure courier has courier role
        if ($courierData && $courierData['role'] !== 'courier') {
            $courierData = null;
        }

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
            'date_format' => 'Y-m-d H:i',
            'currency' => $this->settings['currency_code'],
        ];

        $this->view($this->layout, $data);
        } catch (DatabaseException $e) {
            error_log("Database error in OrderController::details: " . $e->getMessage());
            $this->view($this->layout, ['order' => null, 'customer' => null, 'courier' => null, 'products' => [], 'error_message' => 'An error occurred while loading order details.']);
        }
    }

    function delete(): void {
        try {
            if (empty($_SESSION['user'])) {
            $this->redirect(INSTALL_URL . "?controller=Auth&action=login");
        }
        if ($_SESSION['user']['role'] == 'user') {
            $this->redirect(INSTALL_URL);
        }

        $productModel = new Product();
        $orderModel = new Order();
        $orderProductsModel = new OrderProducts();
        $userModel = new User();

        if (!empty($this->post('id'))) {
            $orderId = Security::int($this->post('id'));

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
                $customer = $order['user_id'] ? $userModel->get($order['user_id']) : null;
                $order['customer_name'] = $customer['name'] ?? 'Unknown';
                $courier = $order['courier_id'] ? $userModel->get($order['courier_id']) : null;
                $order['name'] = ($courier && $courier['role'] === 'courier') ? $courier['name'] : 'Unknown';
                $order['delivery_date'] = $order['delivery_date'] ? date($this->settings['date_format'], $order['delivery_date']) : '';
            }

        $this->view('ajax', ['orders' => $orders, 'currency' => $this->settings['currency_code']]);
        } catch (DatabaseException $e) {
            error_log("Database error in OrderController::delete: " . $e->getMessage());
            $this->view('ajax', ['orders' => [], 'currency' => $this->settings['currency_code'], 'error_message' => 'An error occurred while deleting the order.']);
        }
    }

    function pay(): void {
        try {
            if (!empty($this->get('order_id'))) {
            $orderId = Security::int($this->get('order_id'));
            $orderModel = new Order();
            $userModel = new User();
            $orderProductsModel = new OrderProducts();

            $order = $orderModel->get($orderId);
            if (empty($order)) {
                $this->redirect($_SESSION['previous_url']);
            }
            $user = $userModel->get($order['user_id']);
            $orderProducts = $orderProductsModel->getAll(['order_id' => $orderId]);

            $this->view($this->layout, [
                'currency_code' => $this->settings['currency_code'],
                'order' => $order,
                'user' => $user,
                'order_products' => $orderProducts
            ]);
        }
        } catch (DatabaseException $e) {
            error_log("Database error in OrderController::pay: " . $e->getMessage());
            $this->view($this->layout, ['currency_code' => $this->settings['currency_code'], 'order' => null, 'user' => null, 'order_products' => [], 'error_message' => 'An error occurred while loading payment information.']);
        }
    }

// Controller method to handle the return from PayPal
    public function pay_success(): void {
        try {
// Get the order ID from the URL parameter
            $orderId = Security::int($this->get('order_id'));

        $orderModel = new Order();
        $userModel = new User();
// Load the order from the database
        $order = $orderModel->get($orderId);
        $user = $userModel->getFirstBy(['id' => $order['user_id']]);

// If the order exists and the payment was successful, mark it as paid
        if ($order) {
// Show a success message or redirect to a success page
            $this->view($this->layout, ['order' => $order, 'user' => $user]);
        }
        } catch (DatabaseException $e) {
            error_log("Database error in OrderController::pay_success: " . $e->getMessage());
            $this->view($this->layout, ['order' => null, 'user' => null, 'error_message' => 'An error occurred while processing payment success.']);
        }
    }

// Controller method to handle the cancellation from PayPal
    public function pay_cancel(): void {
        try {
// Get the order ID from the URL parameter
            $orderId = Security::int($this->get('order_id'));

        $orderModel = new Order();
        $userModel = new User();
// Load the order from the database
        $order = $orderModel->get($orderId);
        $user = $userModel->getFirstBy(['id' => $order['user_id']]);

        if ($order) {
// Show a cancellation message or redirect to a cancellation page
            $this->view($this->layout, ['order' => $order, 'user' => $user]);
        }
        } catch (DatabaseException $e) {
            error_log("Database error in OrderController::pay_cancel: " . $e->getMessage());
            $this->view($this->layout, ['order' => null, 'user' => null, 'error_message' => 'An error occurred while processing payment cancellation.']);
        }
    }

    function paypal_ipn(): void {
        try {
            // Override CSRF validation for PayPal webhook
            // This is an external webhook, not a form submission

// PayPal verifies the IPN message
        $orderModel = new Order();
        $notificationModel = new Notification();
        $userModel = new User();
        $orderId = Security::int($this->post('custom')); // Get the order ID from PayPal's "custom" field
        $order = $orderModel->get($orderId);
        $user = $userModel->getFirstBy(['id' => $order['user_id']]);

// Step 1: Verify IPN message with PayPal (to avoid fraud)
        $url = 'https://www.paypal.com/cgi-bin/webscr';
        $data = array(
            'cmd' => '_notify-validate',
            'tx' => $this->post('txn_id'), // PayPal transaction ID
            'amt' => $this->post('mc_gross'), // Total amount paid
            'currency_code' => $this->post('mc_currency'), // Currency code
        );

// Send the IPN data back to PayPal for validation
        $response = file_get_contents($url . '?' . http_build_query($data));

// Step 2: If PayPal confirms the payment is valid
        if ($response == "VERIFIED") {
// Update the order status based on payment confirmation
            if ($this->post('payment_status') == 'Completed') {
// Payment is successful, update order status
                $order['status'] = 'shipped';
                $orderModel->update($order);
                $notificationModel->save([
                    'user_id' => $user['id'],
                    'message' => "Your order #$orderId has been paid successfully!",
                    'link' => INSTALL_URL . "?controller=Order&action=pay_success&order_id=$orderId",
                    'created_at' => time()
                ]);
            } else if ($this->post('payment_status') == 'Failed') {
                $notificationModel->save([
                    'user_id' => $user['id'],
                    'message' => "Payment failed for order #{$orderId}. Please try again.",
                    'link' => INSTALL_URL . "?controller=Order&action=pay&order_id=$orderId",
                    'created_at' => time()
                ]);
            }
        } else {
// Payment not verified, handle the error (perhaps log it)
            error_log("Invalid IPN message: " . json_encode($this->post()));
        }

// Step 3: Handle canceled or failed payment (if needed)
        if ($this->post('payment_status') == 'Failed' || $this->post('payment_status') == 'Canceled') {
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
        } catch (DatabaseException $e) {
            error_log("Database error in OrderController::paypal_ipn: " . $e->getMessage());
            http_response_code(500);
            echo "Error processing IPN notification";
        }
    }

    function bulkDelete(): void {
        try {
            if (empty($_SESSION['user'])) {
            $this->redirect(INSTALL_URL . "?controller=Auth&action=login");
        }
        if ($_SESSION['user']['role'] == 'user') {
            $this->redirect(INSTALL_URL);
        }

        $orderModel = new Order();
        $orderProductsModel = new OrderProducts();
        $userModel = new User();

        if (!empty($this->post('ids')) && is_array($this->post('ids'))) {
            $orderIds = $this->post('ids');

            $orderProductsModel->deleteBy(['order_id' => $orderIds]);
            $orderModel->deleteBy(['id' => $orderIds]);
        }

// Retrieve all orders from the database
        $orders = $orderModel->getAll();

// Format orders for display
            foreach ($orders as &$order) {
                $order['customer_name'] = $userModel->get($order['user_id'])['name'] ?? 'Unknown';
                $courier = $order['courier_id'] ? $userModel->get($order['courier_id']) : null;
                $order['courier_name'] = ($courier && $courier['role'] === 'courier') ? $courier['name'] : 'Unknown';
                $order['delivery_date'] = $order['delivery_date'] ? date($this->settings['date_format'], $order['delivery_date']) : '';
            }

        $this->view('ajax', ['orders' => $orders, 'currency' => $this->settings['currency_code']]);
        } catch (DatabaseException $e) {
            error_log("Database error in OrderController::bulkDelete: " . $e->getMessage());
            $this->view('ajax', ['orders' => [], 'currency' => $this->settings['currency_code'], 'error_message' => 'An error occurred while deleting orders.']);
        }
    }

    function print(): void {
        if ($this->post('orderData') !== null) {
// Decode the JSON data
            $orders = json_decode($_POST['orderData'] ?? 'null', true);

            if (!$orders || empty($orders)) {
                echo "No orders to print";
                $this->terminate();
            }
        }

        $this->view('ajax', ['orders' => $orders]);
    }

    function edit(): void {
        try {
            if (empty($_SESSION['user'])) {
            $this->redirect(INSTALL_URL . "?controller=Auth&action=login");
        }
        if ($_SESSION['user']['role'] == 'user') {
            $this->redirect(INSTALL_URL);
        }

        $orderModel = new Order();
        $orderProductsModel = new OrderProducts();
        $productModel = new Product();
        $userModel = new User();
        $notificationModel = new Notification();
        $mailer = new MailService();
        $currency = $this->settings['currency_code'];

        if (!empty($this->post('id'))) {
            $orderId = Security::int($this->post('id'));
            $order = $orderModel->get($orderId);
            $originalCourierId = $order['courier_id'];
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

            foreach ($this->post('product_id') as $key => $productId) {
                $quantity = $this->post('quantity')[$key];
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
                    'delivery_date' => strtotime($this->post('delivery_date')),
                    'total_amount' => $priceDetails['total']
                ];

                $postData = $this->post();
                if (!$orderModel->update(['id' => $orderId] + $orderData + $postData)) {
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
                unset($orderProduct);

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
                    'user_id' => Security::int($this->post('user_id')),
                    'message' => "Your order #$orderId has been edited successfully!",
                    'link' => INSTALL_URL . "?controller=Order&action=details&id=$orderId",
                    'created_at' => time()
                ]);

                if ($originalCourierId != Security::int($this->post('courier_id'))) {
                    $notificationModel->save([
                        'user_id' => Security::int($this->post('courier_id')),
                        'message' => "New delivery assigned to you. Order #{$orderId}",
                        'link' => INSTALL_URL . "?controller=Order&action=details&id=$orderId",
                        'created_at' => time()
                    ]);
                }

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
                $this->redirect($_SESSION['previous_url']);
            }
        }

        $orderId = Security::int($this->get('order_id'));
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
        } catch (DatabaseException $e) {
            error_log("Database error in OrderController::edit: " . $e->getMessage());
            $arr = [
                'order' => $orderModel->get($orderId) ?? null,
                'orderProducts' => $orderProductsModel->getAll(['order_id' => $orderId]) ?? [],
                'users' => $userModel->getAll() ?? [],
                'products' => $productModel->getAll() ?? [],
                'couriers' => $userModel->getAll(['role' => 'courier']) ?? [],
                'productQuantities' => $productQuantities ?? [],
                'currency' => $currency,
                'error_message' => 'An error occurred while editing the order. Please try again.'
            ];
            $this->view($this->layout, $arr);
        }
    }

    function calculatePrice(): void {
        try {
            $price_arr = $this->calculateOrderTotal($this->post('product_id'), $this->post('quantity'));
            $this->setHeader('Content-Type: application/json');

            echo json_encode($price_arr);
        } catch (DatabaseException $e) {
            error_log("Database error in OrderController::calculatePrice: " . $e->getMessage());
            $this->setHeader('Content-Type: application/json');
            echo json_encode(['error' => 'An error occurred while calculating price.']);
        }
    }

    private function calculateOrderTotal(array $productIds, array $quantities): array {
        try {
            $productModel = new Product();
        $productPrice = 0;

            foreach ($productIds as $key => $productId) {
                $product = $productModel->get($productId);
                if (empty($product)) continue;
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
        } catch (DatabaseException $e) {
            error_log("Database error in OrderController::calculateOrderTotal: " . $e->getMessage());
            return [
                'product_price' => '0.00',
                'shipping_price' => '0.00',
                'tax' => '0.00',
                'total' => '0.00',
                'error' => 'An error occurred while calculating total.'
            ];
        }
    }

    function export(): void {
        try {
// Check if orderData is provided
            if ($this->post('orderData') !== null) {
// Decode the JSON data
                $orders = json_decode($_POST['orderData'] ?? 'null', true);

            if (!$orders || empty($orders)) {
                echo "No orders to export";
                $this->terminate();
            }
        }

        $format = $this->post('format') !== null ? $this->post('format') : 'pdf';

// Export based on format
        switch ($format) {
            case 'pdf':
                ExportService::exportToPDF($orders, 'Orders Export', 'orders_export.pdf');
                break;
            case 'excel':
                ExportService::exportToExcel($orders, 'orders_export.xlsx');
                break;
            case 'csv':
                ExportService::exportToCSV($orders, 'orders_export.csv');
                break;
            default:
                echo "Invalid export format";
                $this->terminate();
            }
        } catch (DatabaseException $e) {
            error_log("Database error in OrderController::export: " . $e->getMessage());
            echo "An error occurred while exporting orders.";
            $this->terminate();
        }
    }

    private function generateOrderEmail($order, $customer, $courier, $products, $title) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">

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
