<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class AuthController extends Controller
{

    var $layout = 'admin';

    function register()
    {
        if (!empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }

        $userModel = new \App\Models\User();

        if (!empty($_POST['send'])) {
            if ($userModel->existsBy(['email' => $_POST['email']])) {
                $error_message = "User with this email already exists.";
            } else if ($_POST['password'] !== $_POST['repeat_password']) {
                $error_message = "Passwords do not match.";
            } else {
                $_POST['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $_POST['role'] = 'user';

                if ($userModel->save($_POST)) {
                    header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
                    exit;
                } else {
                    $error_message = "Failed to register. Please try again.";
                }
            }
        }

        $arr = [];
        if (isset($error_message)) {
            $arr['error_message'] = $error_message;
        }

        $this->view($this->layout, $arr);
    }

    function login()
    {
        if (!empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }

        $userModel = new \App\Models\User();

        if (!empty($_POST['send'])) {
            $user = $userModel->getFirstBy(['email' => $_POST['email']]);

            if ($user && password_verify($_POST['password'], $user['password_hash'])) {
                $_SESSION['user'] = $user;
                header("Location: " . INSTALL_URL . "?controller=Home&action=index", true, 301);
                exit;
            } else {
                $error_message = "Invalid email or password.";
            }
        }

        $arr = [];
        if (isset($error_message)) {
            $arr['error_message'] = $error_message;
        }

        $this->view($this->layout, $arr);
    }

    function logout()
    {
        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }

        session_destroy();

        header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
        exit;
    }
}