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
        // Create an instance of the Courier model
        $orderModel = new \App\Models\Order();

        // Check if the form has been submitted
        if (!empty($_POST['send'])) {
            // Save the data using the Courier model

            $_POST['created_at'] = time();
            $_POST['processed_at'] = strtotime($_POST['processed_at']);


            if ($orderModel->save($_POST)) {
                // Redirect to the list of couriers on successful creation
                header("Location: " . INSTALL_URL . "?controller=Order&action=list", true, 301);
                exit;
            } else {
                // If saving fails, set an error message
                $error_message = "Failed to create the order. Please try again.";
            }
        }

        $userModel = new \App\Models\User();
        $productModel = new \App\Models\Product();
        $arr = array();

        // Извличане на всички записи от таблицата gallery
        $arr['users'] = $userModel->getAll();
        $arr['products'] = $productModel->getAll();
        // Pass any error messages to the view
        if (isset($error_message)) {
            $arr['error_message'] = $error_message;
        }

        // Load the view and pass the data to it
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
