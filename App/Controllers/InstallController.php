<?php

namespace App\Controllers;

use Core\Controller;

class InstallController extends Controller {

    var $layout = 'front';

    public function __construct() {
        if (INSTALLED) {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }
    }

    function step0() {
        $this->view($this->layout);
    }

    function step1() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hostname = $_POST['hostname'];
            $connectionUsername = $_POST['username'];
            $connectionPassword = $_POST['password'] ?? '';
            $databaseName = $_POST['database'];

            // Проверка за грешка
            $connected = $this->checkDbConnection($hostname, $connectionUsername, $connectionPassword, $databaseName);
            if (!$connected['status']) {
                $errorMessage = $connected['message'];
            }

            if (!isset($errorMessage)) {
                $file = file_get_contents("config\constant.php");

                $file = str_replace('{hostname}', $hostname, $file);
                $file = str_replace('{host_username}', $connectionUsername, $file);
                $file = str_replace('{host_password}', $connectionPassword, $file);
                $file = str_replace('{database_name}', $databaseName, $file);

                file_put_contents("config/constant.php", $file);

                $model = new \Core\Model();
                if (!$model->isDbMigrated($databaseName)) {
                    $migrated = $model->migrate($databaseName);
                    if (!$migrated['status']) {
                        $errorMessage = $migrated['message'];
                    }
                }
            }

            if (!isset($errorMessage)) {
                header("Location: " . INSTALL_URL . "?controller=Install&action=step2", true, 301);
                exit;
            }
        }
        $this->view($this->layout, ['error_message' => $errorMessage ?? null]);
    }

    function step2() {
        $userModel = new \App\Models\User();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminName = $_POST['admin_name'];
            $adminEmail = $_POST['admin_email'];
            $adminPassword = $_POST['admin_password'];
            $adminPasswordConfirm = $_POST['admin_password_confirm'];

            if ($adminPassword != $adminPasswordConfirm) {
                $errorMessage = 'Password do not match';
            }

            if (!isset($errorMessage)) {
                $admin = $userModel->getFirstBy(['role' => 'admin']);
                if (empty($admin)) {
                    $userData = [
                        'name' => $adminName,
                        'email' => $adminEmail,
                        'password_hash' => password_hash($adminPassword, PASSWORD_DEFAULT),
                        'role' => 'admin'
                    ];
                } else {
                    $userData = [
                        'id' => $admin['id'],
                        'name' => $adminName,
                        'email' => $adminEmail,
                        'password_hash' => password_hash($adminPassword, PASSWORD_DEFAULT),
                    ];
                }
                $userModel->save($userData);

                header("Location: " . INSTALL_URL . "?controller=Install&action=step3", true, 301);
                exit;
            }
        }

        $arr['error_message'] = $errorMessage ?? null;
        $admin = $userModel->getFirstBy(['role' => 'admin']);
        if (!empty($admin)) {
            $arr['admin'] = $admin;
        }
        $this->view($this->layout, $arr);
    }

    function step3() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mailHost = $_POST['mail_host'];
            $mailPort = $_POST['mail_port'];
            $mailUsername = $_POST['mail_username'];
            $mailPassword = $_POST['mail_password'];
            $mailer = new \App\Helpers\mailer\Mailer();

            $connected = $this->checkMailConnection($mailHost, $mailPort, $mailUsername, $mailPassword);
            if (!$connected['status']) {
                $errorMessage = $connected['message'];
            }

            if (!isset($errorMessage)) {
                $file = file_get_contents("config/constant.php");

                $file = str_replace('{mail_host}', $mailHost, $file);
                $file = str_replace('{mail_port}', $mailPort, $file);
                $file = str_replace('{mail_username}', $mailUsername, $file);
                $file = str_replace('{mail_password}', $mailPassword, $file);

                file_put_contents("config/constant.php", $file);

                header("Location: " . INSTALL_URL . "?controller=Install&action=step4", true, 301);
                exit;
            }
        }
        $this->view($this->layout, ['error_message' => $errorMessage ?? null]);
    }

    function step4() {
        $file = file_get_contents("config/constant.php");

        $file = str_replace('false', 'true', $file);

        file_put_contents("config/constant.php", $file);

        $this->view($this->layout);
    }

    private function checkDbConnection($host, $user, $password, $database) {
        // Създаване на връзка към MySQL сървъра (без база данни)
        $mysqli = new \mysqli($host, $user, $password);

        // Проверка за грешка при връзка към MySQL сървър
        if ($mysqli->connect_error) {
            return [
                'status' => false,
                'message' => "Connection failed: " . $mysqli->connect_error
            ];
        }

        // Създаване на база данни, ако не съществува
        $query = "CREATE DATABASE IF NOT EXISTS `$database`";
        if (!$mysqli->query($query)) {
            return [
                'status' => false,
                'message' => "Failed to create database: " . $mysqli->error
            ];
        }

        // След създаване на базата данни, свързваме се към нея
        $mysqli->select_db($database);

        // Проверка за грешка при връзка към конкретната база данни
        if ($mysqli->connect_error) {
            return [
                'status' => false,
                'message' => "Connection to database failed: " . $mysqli->connect_error
            ];
        }

        return [
            'status' => true,
            'message' => 'Connection successful!'
        ];
    }

    private function checkMailConnection($host, $port, $username, $password) {
        $phpmailer = new \PHPMailer(true);

        try {
            $phpmailer->isSMTP();
            $phpmailer->Host = $host;
            $phpmailer->SMTPAuth = true;
            $phpmailer->Port = $port;
            $phpmailer->Username = $username;
            $phpmailer->Password = $password;
            $phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $phpmailer->CharSet = 'UTF-8';

            return [
                'status' => true,
                'message' => 'Connection successful!'
            ];
        } catch (\PHPMailer\PHPMailer\Exception) {
            return [
                'status' => false,
                'message' => 'Connection failed: ' . $phpmailer->ErrorInfo
            ];
        }
    }
}
