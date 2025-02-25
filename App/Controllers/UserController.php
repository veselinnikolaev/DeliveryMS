<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class UserController extends Controller {

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

    function list() {

        $userModel = new \App\Models\User();

        $tpl = array();

        // Извличане на всички записи от таблицата gallery
        $tpl['users'] = $userModel->getAll();

        // Прехвърляне на данни към изгледа
        $this->view($this->layout, $tpl);
    }

    public function changeRole() {
        $userModel = new \App\Models\User();

        if (!empty($_POST['id']) && !empty($_POST['role'])) {
            $role = $_POST['role'];

            if (in_array($role, ['user', 'admin'])) {
                $userModel->update($_POST);
            }
        }

        // Return refreshed user list
        $users = $userModel->getAll();
        $this->view('ajax', ['users' => $users]);
    }

    function create() {
        // Create an instance of the User model
        $userModel = new \App\Models\User();

        // Check if the form has been submitted
        if (!empty($_POST['send'])) {
            $_POST['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            if ($userModel->existsBy(['email' => $_POST['email']])) {
                $error_message = "User with this email already exists.";
            } else if (!$userModel->save($_POST)) {
                // If saving fails, set an error message
                $error_message = "Failed to create the user. Please try again.";
            } else {
                // Redirect to the list of users on successful creation
                header("Location: " . INSTALL_URL . "?controller=User&action=list", true, 301);
                exit;
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
            if($_POST['id'] == $_SESSION['user']['id']){
                session_destroy();
                header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
                exit;
            }
            $userModel->delete($_POST['id']);
        }
        if ($_SESSION['user']['id'] == $_POST['id']) {
            session_destroy();
        }
        $users = $userModel->getAll();
        $this->view('ajax', ['users' => $users]);
    }

    function edit() {
        $userModel = new \App\Models\User();

        $arr = $userModel->get($_GET['id']);

        // Check if the form has been submitted
        if (!empty($_POST['id'])) {
            if ($userModel->existsBy(['email' => $_POST['email']])) {
                $arr['error_message'] = "User with this email already exists.";
            } else if (!$userModel->update($_POST)) {
                // If saving fails, set an error message
                $arr['error_message'] = "Failed to create the courier. Please try again.";
            } else {
                // Redirect to the list of users on successful creation
                header("Location: " . INSTALL_URL . "?controller=User&action=list", true, 301);
                exit;
            }
        }

        // Load the view and pass the data to it
        $this->view($this->layout, $arr);
    }
}
