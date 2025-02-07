<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class OrderController extends Controller {

    var $layout = 'admin';

    function list() {
        $orderModel = new \App\Models\Order();
        $userModel = new \App\Models\User();
        $courierModel = new \App\Models\Courier();
        $settingModel = new \App\Models\Setting();

        // Retrieve all orders from the database
        $orders = $orderModel->getAll();

        // Format orders for display
        foreach ($orders as &$order) {
            $order['customer_name'] = $userModel->get($order['user_id'])['full_name'] ?? 'Unknown';
            $order['courier_name'] = $courierModel->get($order['courier_id'])['courier_name'] ?? 'Unknown';
            $order['delivery_date'] = date('Y-m-d', strtotime($order['delivery_date']));
        }

        // Pass the data to the view
        $arr = [
            'orders' => $orders,
            'settings' => $settingModel->get(1)
        ];

        $this->view($this->layout, $arr);
    }

    function create() {
        $orderModel = new \App\Models\Order();
        $orderProductsModel = new \App\Models\OrderProducts();
        $productModel = new \App\Models\Product();
        $userModel = new \App\Models\User();
        $courierModel = new \App\Models\Courier();
        $settingModel = new \App\Models\Setting();

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
            'settings' => $settingModel->get(1),
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
        $settingModel = new \App\Models\Setting();

        if (empty($_GET['id'])) {
            header("Location: " . INSTALL_URL . "?controller=Order&action=list", true, 301);
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
        $opts['product_id'] = $orderId;
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
            'setting' => $settingModel->get(1)
        ];

        $this->view($this->layout, $data);
    }

    function delete() {
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

        // Pass the data to the view
        $arr = [
            'orders' => $orders,
        ];

        $this->view('ajax', $arr);
    }

    function edit() {
        $orderModel = new \App\Models\Order();
        $orderProductsModel = new \App\Models\OrderProducts();
        $productModel = new \App\Models\Product();
        $userModel = new \App\Models\User();
        $courierModel = new \App\Models\Courier();
        $settingModel = new \App\Models\Setting();
        
        if (!empty($_POST['id'])) {
            $productIds = $_POST['product_id'];
            $quantities = $_POST['quantity'];
            $orderId = $_POST['id'];
            $order = $orderModel->get($orderId);

            // Fetch current order products
            $currentOrderProducts = $orderProductsModel->getAll(['order_id' => $orderId]);

            // Store current quantities of each product in the order
            $currentQuantities = [];
            foreach ($currentOrderProducts as $product) {
                $currentQuantities[$product['product_id']] = $product['quantity'];
            }

            // Validate new quantities before proceeding
            $quantityError = false;
            foreach ($productIds as $key => $productId) {
                $newQuantity = $quantities[$key];
                $product = $productModel->get($productId);
                $currentQuantity = $currentQuantities[$productId] ?? 0; // Default to 0 if not in current order
                // Check if we're exceeding available stock
                $stockChange = $newQuantity - $currentQuantity; // Difference to adjust
                if ($stockChange > 0 && $stockChange > $product['stock']) {
                    $error_message = "Quantity for {$product['name']} exceeds available stock.";
                    $quantityError = true;
                    break;
                }
            }

            if (!$quantityError) {
                // Calculate updated total amount
                $priceDetails = $this->calculateOrderTotal($productIds, $quantities);
                $orderData = [
                    'last_processed' => time(),
                    'tracking_number' => $order['tracking_number'], // Keep the same tracking number
                    'delivery_date' => strtotime($_POST['delivery_date']),
                    'total_amount' => $priceDetails['total']
                ];

                // Update order data
                if (!$orderModel->update(['id' => $orderId] + $orderData + $_POST)) {
                    $error_message = "Failed to update order with id " . $orderId;
                }

                // Remove old products from the order
                $opts = ['order_id' => $orderId];
                $orderProductsModel->deleteBy($opts);

                // Add new products and update the stock properly
                foreach ($productIds as $key => $productId) {
                    $productDetails = $productModel->get($productId);
                    $newQuantity = $quantities[$key];
                    $currentQuantity = $currentQuantities[$productId] ?? 0; // Old quantity

                    $subtotal = $productDetails['price'] * $newQuantity;

                    // Save the updated order products
                    $orderProductsModel->save([
                        'order_id' => $orderId,
                        'product_id' => $productId,
                        'quantity' => $newQuantity,
                        'price' => $productDetails['price'],
                        'subtotal' => $subtotal
                    ]);

                    // Adjust stock based on quantity difference
                    $stockChange = $newQuantity - $currentQuantity;
                    $updatedStock = $productDetails['stock'] - $stockChange;

                    // Ensure stock never goes negative
                    if ($updatedStock < 0) {
                        $updatedStock = 0;
                    }

                    $updateSuccess = $productModel->update([
                        'id' => $productId,
                        'stock' => $updatedStock
                    ]);

                    if (!$updateSuccess) {
                        $error_message = "Failed to update product stock for {$productDetails['name']}. Please try again.";
                        break;
                    }
                }

                // If no errors, redirect to the orders list
                if (!isset($error_message)) {
                    header("Location: " . INSTALL_URL . "?controller=Order&action=list", true, 301);
                    exit;
                }
            }
        }

        // Fetch the current order details and related products
        $orderId = $_GET['order_id'];
        $opts = ['order_id' => $orderId];
        $orderProducts = $orderProductsModel->getAll($opts);

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
            'settings' => $settingModel->get(1),
            'error_message' => $error_message ?? null
        ];

        // Load the edit view with the order data
        $this->view($this->layout, $arr);
    }

    function calculatePrice() {
        $price_arr = $this->calculateOrderTotal($_POST['product_id'], $_POST['quantity']);
        header('Content-Type: application/json');

        echo json_encode($price_arr);
    }

    private function calculateOrderTotal(array $productIds, array $quantities): array {
        $productModel = new \App\Models\Product();
        $settingModel = new \App\Models\Setting();
        $productPrice = 0;

        foreach ($productIds as $key => $productId) {
            $product = $productModel->get($productId);
            $productPrice += $product['price'] * $quantities[$key];
        }

        $shippingPrice = ($productPrice * $settingModel->get(1)['shipping_rate']) / 100;;
        $tax = ($productPrice * $settingModel->get(1)['tax_rate']) / 100;
        $total = $productPrice + $tax + $shippingPrice;

        return [
            'product_price' => $productPrice,
            'shipping_price' => $shippingPrice,
            'tax' => $tax,
            'total' => $total,
        ];
    }
}
