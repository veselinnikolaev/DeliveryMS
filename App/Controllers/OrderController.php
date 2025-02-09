<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;
use Helpers\mailer\Mailer;

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
        $settings = $settingModel->get(1);
        $mailer = new \App\Helpers\mailer\Mailer();

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
                        $order = $orderModel->get($orderId);
                        $customer = $userModel->get($order['user_id']);
                        $courier = $courierModel->get($order['courier_id']);
                        $orderProducts = $orderProductsModel->getAll(['order_id' => $orderId]);

                        foreach ($orderProducts as &$product) {
                            $productDetails = $productModel->get($product['product_id']);
                            $product['name'] = $productDetails['name'] ?? 'Unknown';
                        }

                        $emailContent = $this->generateOrderEmail($order, $customer, $courier, $orderProducts, $settings);

                        $mailer->sendMail($customer['email'], "Order Confirmation #{$orderId}", $emailContent);

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
            'settings' => $settings,
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
            'settings' => $settingModel->get(1)
        ];

        $this->view($this->layout, $data);
    }

    function delete() {
        $orderModel = new \App\Models\Order();
        $orderProductsModel = new \App\Models\OrderProducts();
        $userModel = new \App\Models\User();
        $courierModel = new \App\Models\Courier();
        $settingModel = new \App\Models\Setting();

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

        $this->view('ajax', ['orders' => $orders, 'settings' => $settingModel->get(1)]);
    }

    function edit() {
        $orderModel = new \App\Models\Order();
        $orderProductsModel = new \App\Models\OrderProducts();
        $productModel = new \App\Models\Product();
        $userModel = new \App\Models\User();
        $courierModel = new \App\Models\Courier();
        $settingModel = new \App\Models\Setting();
        $settings = $settingModel->get(1);
        $mailer = new \App\Helpers\mailer\Mailer();

        if (!empty($_POST['id'])) {
            $orderId = $_POST['id'];
            $order = $orderModel->get($orderId);
            $orderProducts = $orderProductsModel->getBy(['order_id' => $orderId]);

            $currentOrderProducts = $orderProductsModel->getAll(['order_id' => $orderId]);
            $currentQuantities = [];
            foreach ($currentOrderProducts as $product) {
                $currentQuantities[$product['product_id']] = $product['quantity'];
            }

            $quantityError = false;
            foreach ($_POST['product_id'] as $key => $productId) {
                $quantity = $_POST['quantity'][$key];
                $product = $productModel->get($productId);
                $currentQuantity = $currentQuantities[$productId] ?? 0;

                if ($quantity !== $currentQuantity) {
                    if ($quantity > $product['stock']) {
                        $error_message = "Quantity for {$product['name']} exceeds available stock.";
                        $quantityError = true;
                        break;
                    }
                }
            }

            if (!$quantityError) {
                $priceDetails = $this->calculateOrderTotal($_POST['product_id'], $_POST['quantity']);
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

                foreach ($_POST['product_id'] as $key => $productId) {
                    $quantity = $_POST['quantity'][$key];
                    $productDetails = $productModel->get($productId);
                    $currentQuantity = $currentQuantities[$productId] ?? 0;
                    $subtotal = $productDetails['price'] * $quantity;

                    // Save the updated order products
                    $orderProductsModel->save([
                        'order_id' => $orderId,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $productDetails['price'],
                        'subtotal' => $subtotal
                    ]);

                    if ($quantity !== $currentQuantity) {
                        $stockChange = $quantity - $currentQuantity;
                        $updatedStock = $productDetails['stock'] - $stockChange;

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
                }

                if (!isset($error_message)) {
                    $order = $orderModel->get($orderId);
                    $customer = $userModel->get($order['user_id']);
                    $courier = $courierModel->get($order['courier_id']);

                    $orderProducts = $orderProductsModel->getAll(['order_id' => $orderId]);
                    foreach ($orderProducts as &$orderProduct) {
                        $orderProductDetails = $productModel->get($orderProduct['product_id']);
                        $orderProduct['name'] = $orderProductDetails['name'] ?? 'Unknown';
                    }

                    $emailContent = $this->generateOrderEmail($order, $customer, $courier, $orderProducts, $settings);

                    $mailer->sendMail($customer['email'], "Order Update #{$orderId}", $emailContent);

                    header("Location: " . INSTALL_URL . "?controller=Order&action=list", true, 301);
                    exit;
                }
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
            'settings' => $settings,
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
        $settingModel = new \App\Models\Setting();
        $productPrice = 0;

        foreach ($productIds as $key => $productId) {
            $product = $productModel->get($productId);
            $productPrice += $product['price'] * $quantities[$key];
        }

        $shippingPrice = ($productPrice * $settingModel->get(1)['shipping_rate']) / 100;

        $tax = ($productPrice * $settingModel->get(1)['tax_rate']) / 100;
        $total = $productPrice + $tax + $shippingPrice;

        return [
            'product_price' => $productPrice,
            'shipping_price' => $shippingPrice,
            'tax' => $tax,
            'total' => $total,
        ];
    }

    private function generateOrderEmail($order, $customer, $courier, $products, $settings) {
        ob_start();
        ?>
        <div class="container-scroller">
            <div class="row">
                <div class="col-sm-12">
                    <div class="home-tab">
                        <div class="card card-rounded mt-3">
                            <div class="card-body">
                                <h4 class="card-title">Order Details</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Order ID:</strong> <?= htmlspecialchars($order['id']) ?></p>
                                        <p><strong>Customer:</strong> <?= htmlspecialchars($customer['full_name']) ?></p>
                                        <p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
                                        <p><strong>Country:</strong> <?= htmlspecialchars($order['country']) ?></p>
                                        <p><strong>Region:</strong> <?= htmlspecialchars($order['region']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Courier:</strong> <?= htmlspecialchars($courier['courier_name']) ?></p>
                                        <p><strong>Delivery Date:</strong> <?= date('Y-m-d', strtotime($order['delivery_date'])) ?></p>
                                        <p><strong>Status:</strong> <?php
                                            foreach (Utility::$order_status as $k => $v) {
                                                if ($k == $order['status']) {
                                                    echo $v;
                                                }
                                            }
                                            ?></p>
                                        <p><strong>Total Price:</strong> <?= \Utility::getDisplayableAmount(htmlspecialchars(number_format($order['total_amount'], 2)), $settings['currency_code']) ?></p>
                                    </div>
                                </div>
                                <hr>
                                <h5>Products</h5>
                                <div class="table-responsive">
                                    <table class="table select-table">
                                        <thead>
                                            <tr>
                                                <th>Product Name</th>
                                                <th>Quantity</th>
                                                <th>Price</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($products as $product) { ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                                    <td><?= htmlspecialchars($product['quantity']) ?></td>
                                                    <td><?= \Utility::getDisplayableAmount(htmlspecialchars(number_format($product['price'], 2)), $settings['currency_code']) ?></td>
                                                    <td><?= \Utility::getDisplayableAmount(htmlspecialchars(number_format($product['subtotal'], 2)), $settings['currency_code']) ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
