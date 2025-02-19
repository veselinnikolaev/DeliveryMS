<?php

namespace App\Controllers;

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

    function list() {
        $orderModel = new \App\Models\Order();
        $userModel = new \App\Models\User();
        $courierModel = new \App\Models\Courier();

        // Retrieve all orders from the database
        if (empty($_GET['user_id'])) {
            $orders = $orderModel->getAll();
        } else if ($_GET['user_id'] == $_SESSION['user']['id']) {
            $orders = $orderModel->getAll(['user_id' => $_GET['user_id']]);
        } else {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }

        // Format orders for display
        foreach ($orders as &$order) {
            $order['customer_name'] = $userModel->get($order['user_id'])['full_name'] ?? 'Unknown';
            $order['courier_name'] = $courierModel->get($order['courier_id'])['courier_name'] ?? 'Unknown';
            $order['delivery_date'] = date('Y-m-d', strtotime($order['delivery_date']));
        }

        // Pass the data to the view
        $arr = [
            'orders' => $orders,
            'currency' => $this->settings['currency_code']
        ];

        $this->view($this->layout, $arr);
    }

    function create() {
        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
            exit;
        }
        if ($_SESSION['user']['role'] != 'admin') {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }

        $orderModel = new \App\Models\Order();
        $orderProductsModel = new \App\Models\OrderProducts();
        $productModel = new \App\Models\Product();
        $userModel = new \App\Models\User();
        $courierModel = new \App\Models\Courier();
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
                        if ($this->settings['email_sending'] == 'enabled') {
                            $order = $orderModel->get($orderId);
                            $customer = $userModel->get($order['user_id']);
                            $courier = $courierModel->get($order['courier_id']);
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
            'couriers' => $courierModel->getAll(),
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
        $courierModel = new \App\Models\Courier();

        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
            exit;
        }

        if (empty($_GET['id'])) {
            header("Location: " . INSTALL_URL . "?controller=Order&action=list", true, 301);
            exit;
        }

        $userOrders = $orderModel->getAll(['user_id' => $_SESSION['user']['id']]);
        if (!in_array($_GET['id'], $userOrders)) {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }

        $orderId = intval($_GET['id']);
        $orderData = $orderModel->get($orderId);

        if (!$orderData) {
            header("Location: " . INSTALL_URL . "?controller=Order&action=list", true, 301);
            exit;
        }

        $customerData = $userModel->get($orderData['user_id']);
        $courierData = $courierModel->get($orderData['courier_id']);

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
        if ($_SESSION['user']['role'] != 'admin') {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }
        
        $orderModel = new \App\Models\Order();
        $orderProductsModel = new \App\Models\OrderProducts();
        $userModel = new \App\Models\User();
        $courierModel = new \App\Models\Courier();

        if (!empty($_POST['id'])) {
            $orderId = $_POST['id'];

            $opts = array();
            $opts['order_id'] = $orderId;
            $orderProductsModel->deleteBy($opts);

            $orderModel->delete($orderId);
        }

        // Retrieve all orders from the database
        $orders = $orderModel->getAll();

        // Format orders for display
        foreach ($orders as &$order) {
            $order['customer_name'] = $userModel->get($order['user_id'])['full_name'] ?? 'Unknown';
            $order['courier_name'] = $courierModel->get($order['courier_id'])['courier_name'] ?? 'Unknown';
            $order['delivery_date'] = date('Y-m-d', strtotime($order['delivery_date']));
        }

        $this->view('ajax', ['orders' => $orders, 'currency' => $this->settings['currency_code']]);
    }

    function edit() {
        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
            exit;
        }
        if ($_SESSION['user']['role'] != 'admin') {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }

        $orderModel = new \App\Models\Order();
        $orderProductsModel = new \App\Models\OrderProducts();
        $productModel = new \App\Models\Product();
        $userModel = new \App\Models\User();
        $courierModel = new \App\Models\Courier();
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
                if ($this->settings['email_sending'] == 'enabled') {
                    $order = $orderModel->get($orderId);
                    $customer = $userModel->get($order['user_id']);
                    $courier = $courierModel->get($order['courier_id']);

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
            'couriers' => $courierModel->getAll(),
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
                    .products-table th, .products-table td {
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
                                <p><strong>Order ID:</strong> <?= htmlspecialchars($order['id']) ?></p>
                                <p><strong>Customer:</strong> <?= htmlspecialchars($customer['full_name']) ?></p>
                                <p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
                                <p><strong>Country:</strong> <?= htmlspecialchars($order['country']) ?></p>
                                <p><strong>Region:</strong> <?= htmlspecialchars($order['region']) ?></p>
                            </div>
                            <div class="detail-column">
                                <p><strong>Courier:</strong> <?= htmlspecialchars($courier['courier_name']) ?></p>
                                <p><strong>Delivery Date:</strong> <?= date('Y-m-d', strtotime($order['delivery_date'])) ?></p>
                                <p><strong>Status:</strong> <?= \Utility::$order_status[$order['status']] ?? 'Unknown' ?></p>
                                <p><strong>Total Price:</strong> <?= \Utility::getDisplayableAmount(htmlspecialchars(number_format($order['total_amount'], 2))) ?></p>
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
                                        <td><?= htmlspecialchars($product['name']) ?></td>
                                        <td><?= htmlspecialchars($product['quantity']) ?></td>
                                        <td><?= \Utility::getDisplayableAmount(htmlspecialchars(number_format($product['price'], 2))) ?></td>
                                        <td><?= \Utility::getDisplayableAmount(htmlspecialchars(number_format($product['subtotal'], 2))) ?></td>
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
