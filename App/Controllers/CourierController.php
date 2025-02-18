<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class CourierController extends Controller {

    var $layout = 'admin';

    function list() {
        $courierModel = new \App\Models\Courier();

        $couriers = $courierModel->getAll();

        $this->view($this->layout, ['couriers' => $couriers]);
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
