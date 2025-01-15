<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class OrderController extends Controller {

    var $layout = 'admin';

    public function list() {
        $orderModel = new App\Models\Order();

        $orders = $orderModel->getAll();

        $this->view($this->layout, ['orders' => $orders]);
    }

    public function create() {
        $orderModel = new \App\Models\Order();
        $orderProductsModel = new \App\Models\OrderProducts();
        $productModel = new \App\Models\Product();
        $userModel = new \App\Models\User();
        $courierModel = new \App\Models\Courier();

        if (!empty($_POST['send'])) {
            $products = $_POST['product_id'];
            $quantities = $_POST['quantity'];
            $totalAmount = 0;
            $orderId = $orderModel->save($_POST);

            foreach ($products as $index => $productId) {
                $productDetails = $productModel->get($productId);
                $subtotal = $productDetails['price'] * $quantities[$index];
                $totalAmount += $subtotal;
                $orderProductsModel->save([
                    'order_id' => $orderId, 'product_id' => $productId,
                    'quantity' => $quantities[$index], 'price' => $productDetails['price'],
                    'subtotal' => $subtotal
                ]);
            }

            if ($orderModel->update(['id' => $orderId, 'total_amount' => $totalAmount])) {
                header("Location: " . INSTALL_URL . "?controller=Order&action=list", true, 301);
                exit;
            }

            $error_message = "Failed to create the order. Please try again.";
        }

        $arr = [
            'users' => $userModel->getAll(),
            'products' => $productModel->getAll(),
            'couriers' => $courierModel->getAll(),
            'error_message' => $error_message ?? null
        ];
        $this->view($this->layout, $arr);
    }

    function delete() {
        $orderModel = new \App\Models\Order();

        if (!empty($_POST['id'])) {
            $orderModel->delete($_POST['id']);
        }

        $orders = $orderModel->getAll();
        $this->view('ajax', ['orders' => $orders]);
    }

    function edit() {
        $orderModel = new \App\Models\Order();

        $arr = $orderModel->get($_GET['id']);

        // Check if the form has been submitted
        if (!empty($_POST['id'])) {

            // Save the data using the Courier model
            if ($orderModel->update($_POST)) {
                // Redirect to the list of couriers on successful creation
                header("Location: " . INSTALL_URL . "?controller=Order&action=list", true, 301);
                exit;
            } else {
                // If saving fails, set an error message
                $error_message = "Failed to create the order. Please try again.";
            }
        }

        // Load the view and pass the data to it
        $this->view($this->layout, $arr);
    }

}
