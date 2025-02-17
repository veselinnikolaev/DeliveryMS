<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class ProductController extends Controller {

    var $layout = 'admin';

    public function __construct() {
        if(empty($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
            header("Location: " . INSTALL_URL . "?controller=auth&action=login", true, 301);
            exit;
        }
    }
    
    function list() {
        $productModel = new \App\Models\Product();
        $settingModel = new \App\Models\Setting();

        $products = $productModel->getAll();
        $settings = $settingModel->get(1);

        $this->view($this->layout, ['products' => $products, 'settings' => $settings]);
    }

    function create() {
        // Create an instance of the Courier model
        $productModel = new \App\Models\Product();
        $settingModel = new \App\Models\Setting();

        // Check if the form has been submitted
        if (!empty($_POST['send'])) {
            // Save the data using the Courier model
            if ($productModel->save($_POST)) {
                // Redirect to the list of couriers on successful creation
                header("Location: " . INSTALL_URL . "?controller=Product&action=list", true, 301);
                exit;
            } else {
                // If saving fails, set an error message
                $error_message = "Failed to create the product. Please try again.";
            }
        }

        // Pass any error messages to the view
        $arr = array();
        if (isset($error_message)) {
            $arr['error_message'] = $error_message;
        }
        $arr['settings'] = $settingModel->get(1);

        // Load the view and pass the data to it
        $this->view($this->layout, $arr);
    }

    function delete() {
        $productModel = new \App\Models\Product();

        if (!empty($_POST['id'])) {
            $productModel->delete($_POST['id']);
        }

        $products = $productModel->getAll();
        $this->view('ajax', ['products' => $products]);
    }

    function edit() {
        $productModel = new \App\Models\Product();
        $settingModel = new \App\Models\Setting();

        $arr = $productModel->get($_GET['id']);

        // Check if the form has been submitted
        if (!empty($_POST['id'])) {

            // Save the data using the Courier model
            if ($productModel->update($_POST)) {
                // Redirect to the list of couriers on successful creation
                header("Location: " . INSTALL_URL . "?controller=Product&action=list", true, 301);
                exit;
            } else {
                // If saving fails, set an error message
                $arr['error_message'] = "Failed to create the product. Please try again.";
            }
        }

        $arr['settings'] = $settingModel->get(1);
        // Load the view and pass the data to it
        $this->view($this->layout, $arr);
    }
}
