<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class UserController extends Controller {

    var $layout = 'admin';

    function list() {

        $userModel = new \App\Models\User();

        $tpl = array();

        // Извличане на всички записи от таблицата gallery
        $tpl['users'] = $userModel->getAll();

        // Прехвърляне на данни към изгледа
        $this->view($this->layout, $tpl);
    }

    function create() {
        // Create an instance of the User model
        $userModel = new \App\Models\User();

        // Check if the form has been submitted
        if (!empty($_POST['send'])) {
            // Save the data using the User model
            if ($userModel->save($_POST)) {
                // Redirect to the list of users on successful creation
                header("Location: " . INSTALL_URL . "?controller=User&action=list", true, 301);
                exit;
            } else {
                // If saving fails, set an error message
                $error_message = "Failed to create the user. Please try again.";
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
        $userModel = new \App\Models\User();

        if (!empty($_POST['id'])) {
            $userModel->delete($_POST['id']);
        }

        $users = $userModel->getAll();
        $this->view('ajax', ['users' => $users]);
    }
    
    function edit() {
        $userModel = new \App\Models\User();
        
        $arr = $userModel->get($_GET['id']);

        // Check if the form has been submitted
        if (!empty($_POST['id'])) {
            
            // Save the data using the Courier model
            if ($userModel->update($_POST)) {
                // Redirect to the list of users on successful creation
                header("Location: " . INSTALL_URL . "?controller=User&action=list", true, 301);
                exit;
            } else {
                // If saving fails, set an error message
                $error_message = "Failed to create the courier. Please try again.";
            }
        }

        // Load the view and pass the data to it
        $this->view($this->layout, $arr);
    }
}
