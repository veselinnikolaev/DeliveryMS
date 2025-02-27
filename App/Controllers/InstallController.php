<?php

namespace App\Controllers;

use Core\Controller;

class InstallController extends Controller {

    var $layout = 'front';

    function step1() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hostname = $_POST['hostname'];
            $connectionUsername = $_POST['username'];
            $connectionPassword = $_POST['password'];
            $databaseName = $_POST['database'];

            $file = file_get_contents("constant.php");

            str_replace('{hostname}', $hostname, $file);
            str_replace('{host_username}', $connectionUsername, $file);
            str_replace('{host_password}', $connectionPassword, $file);
            str_replace('{database_name}', $databaseName, $file);

            header("Location: " . INSTALL_URL . "?controller=Install&action=step2", true, 301);
            exit;
        }
        $this->view($layout);
    }

    function step2() {
        $userModel = new App\Models\User();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminName = $_POST['admin_name'];
            $adminEmail = $_POST['admin_email'];
            $adminPassword = $_POST['admin_password'];
            $adminPasswordConfirm = $_POST['admin_password_confirm'];

            if ($adminPassword != $adminPasswordConfirm) {
                $errorMessage = 'Password do not match';
            }

            if (!isset($errorMessage)) {
                $userData = [
                    'full_name' => $adminName,
                    'email' => $adminEmail,
                    'password_hash' => password_hash($adminPassword, PASSWORD_DEFAULT),
                    'role' => 'admin'
                    ];
                $userModel->save($userData);

                header("Location: " . INSTALL_URL . "?controller=Install&action=step3", true, 301);
                exit;
            }
        }
        $this->view($layout, ['error_message' => $errorMessage]);
    }

}
