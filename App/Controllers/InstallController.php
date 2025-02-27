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
            $model = \Core\Model();

            // Проверка за грешка
            $connected = $model->checkConnection($hostname, $connectionUsername, $connectionPassword, $databaseName);
            if (!$connected['status']) {
                $errorMessage = $connected['message'];
            }

            if (!$model->isDbMigrated($databaseName)) {
                $migrated = $model->migrate($databaseName);
                if (!$migrated['status']) {
                    $errorMessage = $migrated['message'];
                }
            }

            if (!isset($errorMessage)) {
                $file = file_get_contents("constant.php");

                str_replace('{hostname}', $hostname, $file);
                str_replace('{host_username}', $connectionUsername, $file);
                str_replace('{host_password}', $connectionPassword, $file);
                str_replace('{database_name}', $databaseName, $file);

                file_put_contents("constant.php", $file);

                header("Location: " . INSTALL_URL . "?controller=Install&action=step2", true, 301);
                exit;
            }
        }
        $this->view($this->layout, ['error_message' => $errorMessage]);
    }

    function step2() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminName = $_POST['admin_name'];
            $adminEmail = $_POST['admin_email'];
            $adminPassword = $_POST['admin_password'];
            $adminPasswordConfirm = $_POST['admin_password_confirm'];
            $userModel = new App\Models\User();

            if ($adminPassword != $adminPasswordConfirm) {
                $errorMessage = 'Password do not match';
            }

            if (!isset($errorMessage)) {
                if (!$userModel->existsBy(['email' => $adminEmail])) {
                    $userData = [
                        'name' => $adminName,
                        'email' => $adminEmail,
                        'password_hash' => password_hash($adminPassword, PASSWORD_DEFAULT),
                        'role' => 'admin'
                    ];
                    $userModel->save($userData);
                }

                header("Location: " . INSTALL_URL . "?controller=Install&action=step3", true, 301);
                exit;
            }
        }
        $this->view($this->layout, ['error_message' => $errorMessage]);
    }

    function step3() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mailHost = $_POST['mail_host'];
            $mailPort = $_POST['mail_port'];
            $mailUsername = $_POST['mail_username'];
            $mailPassword = $_POST['mail_password'];

            $mailer = new \App\Helpers\mailer\Mailer();

            $connected = $mailer->checkConnection($mailHost, $mailPort, $mailUsername, $mailPassword);
            if (!$connected['status']) {
                $errorMessage = $connected['message'];
            }

            if (!isset($errorMessage)) {
                $file = file_get_contents("constant.php");

                str_replace('{mail_host}', $mailHost, $file);
                str_replace('{mail_port}', $mailPort, $file);
                str_replace('{mail_username}', $mailUsername, $file);
                str_replace('{mail_password}', $mailPassword, $file);

                file_put_contents("constant.php", $file);

                header("Location: " . INSTALL_URL . "?controller=Install&action=step4", true, 301);
                exit;
            }
        }
        $this->view($this->layout, ['error_message' => $errorMessage]);
    }
}
