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

    function list($layout = 'admin') {

        $userModel = new \App\Models\User();

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
            if (!empty($_POST['role'])) {
                $opts["role LIKE '%" . $_POST['role'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['address'])) {
                $opts["address LIKE '%" . $_POST['address'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['country'])) {
                $opts["country LIKE '%" . $_POST['country'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['region'])) {
                $opts["region LIKE '%" . $_POST['region'] . "%' AND 1 "] = "1";
            }
        }

        // Извличане на всички записи от таблицата gallery
        $users = $userModel->getAll($opts);

        // Прехвърляне на данни към изгледа
        $this->view($layout, ['users' => $users]);
    }

    function filter() {
        $this->list('ajax');
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
            if ($userModel->existsBy(['email' => $_POST['email']])) {
                $error_message = "User with this email already exists.";
            } else if ($_POST['password'] !== $_POST['repeat_password']) {
                $error_message = "Passwords do not match.";
            } else {
                $_POST['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $_POST['role'] = 'user';

                if ($userModel->save($_POST)) {
                    header("Location: " . INSTALL_URL . "?controller=User&action=list", true, 301);
                    exit;
                } else {
                    $error_message = "Failed to register. Please try again.";
                }
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
            if ($_POST['id'] == $_SESSION['user']['id']) {
                session_destroy();
                header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
                exit;
            }
        }

        $users = $userModel->getAll();
        $this->view('ajax', ['users' => $users]);
    }

    function bulkDelete() {
        $userModel = new \App\Models\User();

        if (!empty($_POST['ids']) && is_array($_POST['ids'])) {
            $inUserIds = implode(', ', $_POST['ids']);
            $userModel->deleteBy(["id IN ($inUserIds) AND 1 " => '1']);
            if (in_array($_SESSION['user']['id'], $_POST['ids'])) {
                session_destroy();
                header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
                exit;
            }
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
