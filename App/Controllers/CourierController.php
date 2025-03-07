<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class CourierController extends Controller {

    var $layout = 'admin';

    public function __construct() {
        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
            exit;
        }
        if ($_SESSION['user']['role'] != 'admin') {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }
    }

    function list($layout = 'admin') {
        $courierModel = new \App\Models\Courier();

        $opts = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['name'])) {
                $opts["name LIKE '%" . $_POST['name'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['phone_number'])) {
                $opts["phone_number LIKE '%" . $_POST['phone_number'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['email'])) {
                $opts["email LIKE '%" . $_POST['email'] . "%' AND 1 "] = "1";
            }
        }
        $couriers = $courierModel->getAll($opts);

        $this->view($layout, ['couriers' => $couriers]);
    }

    function filter() {
        $this->list('ajax');
    }

    function create() {
        // Create an instance of the Courier model
        $courierModel = new \App\Models\Courier();

        // Check if the form has been submitted
        if (!empty($_POST['send'])) {
            // Save the data using the Courier model
            if ($courierModel->save($_POST)) {
                // Redirect to the list of couriers on successful creation
                header("Location: " . INSTALL_URL . "?controller=Courier&action=list", true, 301);
                exit;
            } else {
                // If saving fails, set an error message
                $error_message = "Failed to create the courier. Please try again.";
            }
        }

        // Pass any error messages to the view
        $arr = array();
        if (isset($error_message)) {
            $arr['error_message'] = $error_message;
        }

        // Load the view and pass the data to it
        $this->view($this->layout, $arr);
    }

    function delete() {
        $courierModel = new \App\Models\Courier();

        if (!empty($_POST['id'])) {
            $courierModel->delete($_POST['id']);
        }

        $couriers = $courierModel->getAll();
        $this->view('ajax', ['couriers' => $couriers]);
    }

    function bulkDelete() {
        $courierModel = new \App\Models\Courier();

        if (!empty($_POST['ids']) && is_array($_POST['ids'])) {
            $inCourierIds = implode(', ', $_POST['ids']);
            $courierModel->deleteBy(["id IN ($inCourierIds) AND 1 " => '1']);
        }

        $couriers = $courierModel->getAll();
        $this->view('ajax', ['couriers' => $couriers]);
    }

    function edit() {
        $courierModel = new \App\Models\Courier();

        $arr = $courierModel->get($_GET['id']);

        // Check if the form has been submitted
        if (!empty($_POST['id'])) {

            // Save the data using the Courier model
            if ($courierModel->update($_POST)) {
                // Redirect to the list of couriers on successful creation
                header("Location: " . INSTALL_URL . "?controller=Courier&action=list", true, 301);
                exit;
            } else {
                // If saving fails, set an error message
                $arr['error_message'] = "Failed to create the courier. Please try again.";
            }
        }

        // Load the view and pass the data to it
        $this->view($this->layout, $arr);
    }
}
