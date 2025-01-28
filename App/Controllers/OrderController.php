<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class OrderController extends Controller {

    var $layout = 'admin';

    public function list() {
        $orderModel = new \App\Models\Order();
        $userModel = new \App\Models\User();
        $courierModel = new \App\Models\Courier();

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

        $this->view($this->layout, $arr);
    }

    public function create() {
        $orderModel = new \App\Models\Order();
        $orderProductsModel = new \App\Models\OrderProducts();
        $productModel = new \App\Models\Product();
        $userModel = new \App\Models\User();
        $courierModel = new \App\Models\Courier();
        $productIds = $_POST['product_id'];
        $quantities = $_POST['quantity'];

        $priceDetails = $this->calculateOrderTotal($productIds, $quantities, $_POST['shipping_price'], $_POST['tax']);
        if (!empty($_POST['send'])) {
            $orderData = [
                'last_processed' => time(),
                'tracking_number' => \Utility::generateRandomString(),
                'delivery_date' => strtotime($_POST['delivery_date']),
                'total_amount' => $priceDetails['total']
            ];

            $orderId = $orderModel->save($orderData);

            if (!$orderId) {
                // Save order products
                foreach ($productIds as $key => $productId) {
                    $productDetails = $productModel->get($productId);
                    $subtotal = $productDetails['price'] * $quantities[$key];

                    $data = [
                        'order_id' => $orderId,
                        'product_id' => $productId,
                        'quantity' => $quantities[$key],
                        'price' => $productDetails['price'],
                        'subtotal' => $subtotal,
                    ];

                    if (!$orderProductsModel->save($data)) {
                        // Handle product save failure
                        $error_message = "Failed to save order products. Please try again.";
                        break;
                    }
                }

                if (!isset($error_message)) {
                    // Redirect on success
                    header("Location: " . INSTALL_URL . "?controller=Order&action=list", true, 301);
                    exit;
                }
            } else {
                $error_message = "Failed to create the order. Please try again.";
            }
        }

        $arr = [
            'users' => $userModel->getAll(),
            'products' => $productModel->getAll(),
            'couriers' => $courierModel->getAll(),
            'error_message' => $error_message ?? null
        ];
        $this->view($this->layout, $arr);
    }

    public function details() {
        $orderModel = new \App\Models\Order();
        $orderProductsModel = new \App\Models\OrderProducts();
        $productModel = new \App\Models\Product();
        $userModel = new \App\Models\User();
        $courierModel = new \App\Models\Courier();

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
        ];

        $this->view($this->layout, $data);
    }

    function delete() {
        $orderModel = new \App\Models\Order();

        if (!empty($_POST['id'])) {
            $orderModel->delete($_POST['id']);
        }

        $orders = $orderModel->getAll();
        $this->view('ajax', ['orders' => $orders]);
    }

    public function edit() {
        $orderModel = new \App\Models\Order();
        $orderProductsModel = new \App\Models\OrderProducts();
        $productModel = new \App\Models\Product();
        $userModel = new \App\Models\User();
        $courierModel = new \App\Models\Courier();

        // Check if order ID is provided
        if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
            header("Location: " . INSTALL_URL . "?controller=Order&action=list", true, 301);
            exit;
        }

        $orderId = $_GET['order_id'];
        $order = $orderModel->get($orderId);
        if (!$order) {
            header("Location: " . INSTALL_URL . "?controller=Order&action=list", true, 301);
            exit;
        }

        if (!empty($_POST['send'])) {
            $productIds = $_POST['product_id'];
            $quantities = $_POST['quantity'];

            $priceDetails = $this->calculateOrderTotal($productIds, $quantities, $productModel);
            $orderData = [
                'last_processed' => time(),
                'tracking_number' => $order['tracking_number'], // keep the same tracking number
                'delivery_date' => strtotime($_POST['delivery_date']),
                'total_amount' => $priceDetails['total']
            ];

            // Update order data
            $orderModel->update(['id' => $orderId] + $orderData + $_POST);

            // Delete previous order products before saving updated ones
            $orderProductsModel->deleteByOrderId($orderId);

            // Add new products
            foreach ($productIds as $product => $productId) {
                $productDetails = $productModel->get($productId);
                $subtotal = $productDetails['price'] * $quantities[$product];

                // Save the updated order products
                $orderProductsModel->save([
                    'order_id' => $orderId,
                    'product_id' => $productId,
                    'quantity' => $quantities[$product],
                    'price' => $productDetails['price'],
                    'subtotal' => $subtotal
                ]);
            }

            header("Location: " . INSTALL_URL . "?controller=Order&action=list", true, 301);
            exit;
        }

        // Fetch the existing order products
        $orderProducts = $orderProductsModel->getByOrderId($orderId);

        $arr = [
            'order' => $order,
            'orderProducts' => $orderProducts,
            'users' => $userModel->getAll(),
            'products' => $productModel->getAll(),
            'couriers' => $courierModel->getAll(),
            'error_message' => $error_message ?? null
        ];

        // Load the edit view with the order data
        $this->view($this->layout, $arr);
    }

    function calculatePrice() {
        // to here function
        $price_arr = $this->calculateOrderTotal($_POST['product_id'], $_POST['quantity'], 10, 20);
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

        $shippingPrice = 10;
        $tax = ($productPrice * 20) / 100;
        $total = $productPrice + $tax + $shippingPrice;

        return [
            'product_price' => $productPrice,
            'shipping_price' => $shippingPrice,
            'tax' => $tax,
            'total' => $total,
        ];
    }
}
