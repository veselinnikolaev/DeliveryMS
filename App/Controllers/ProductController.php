<?php

namespace App\Controllers;

use App\Models\Setting;
use Models;
use Core;
use Core\View;
use Core\Controller;

class ProductController extends Controller {

    var $layout = 'admin';
    var $settings;

    public function __construct() {
        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
            exit;
        }
        if ($_SESSION['user']['role'] == 'user') {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }
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
        $productModel = new \App\Models\Product();

        $opts = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['name'])) {
                $opts["name LIKE '%" . $_POST['name'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['description'])) {
                $opts["description LIKE '%" . $_POST['description'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['minPrice'])) {
                $opts["price >= " . $_POST['minPrice'] . " AND 1 "] = "1";
            }
            if (!empty($_POST['maxPrice'])) {
                $opts["price <= " . $_POST['maxPrice'] . " AND 1 "] = "1";
            }
            if (!empty($_POST['minStock'])) {
                $opts["stock >= " . $_POST['minStock'] . " AND 1 "] = "1";
            }
            if (!empty($_POST['maxStock'])) {
                $opts["stock <= " . $_POST['maxStock'] . " AND 1 "] = "1";
            }
        }

        $products = $productModel->getAll($opts);

        $this->view($layout, ['products' => $products, 'currency' => $this->settings['currency_code']]);
    }

    function filter() {
        $this->list('ajax');
    }

    function create() {
        // Create an instance of the Courier model
        $productModel = new \App\Models\Product();

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
        $arr['currency'] = $this->settings['currency_code'];

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

    function bulkDelete() {
        $productModel = new \App\Models\Product();

        if (!empty($_POST['ids']) && is_array($_POST['ids'])) {
            $inProductIds = implode(', ', $_POST['ids']);
            $productModel->deleteBy(["id IN ($inProductIds) AND 1 " => '1']);
        }

        $products = $productModel->getAll();
        $this->view('ajax', ['products' => $products]);
    }

    function edit() {
        $productModel = new \App\Models\Product();

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

        $arr['currency'] = $this->settings['currency_code'];
        // Load the view and pass the data to it
        $this->view($this->layout, $arr);
    }
}
