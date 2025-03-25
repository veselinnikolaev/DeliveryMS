<?php

namespace App\Controllers;

use Core\Controller;

class InstallController extends Controller
{

    var $layout = 'front';

    public function __construct()
    {
        if (INSTALLED && MAIL_CONFIGURED && $_REQUEST['action'] != 'step5') {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }
    }

    function step0()
    {
        if (INSTALLED && !MAIL_CONFIGURED) {
            header("Location: " . INSTALL_URL . '?controller=Install&action=step4', true, 301);
            exit;
        }

        $this->view($this->layout);
    }

    function step1()
    {
        if (INSTALLED && !MAIL_CONFIGURED) {
            header("Location: " . INSTALL_URL . '?controller=Install&action=step4', true, 301);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hostname = $_POST['hostname'];
            $connectionUsername = $_POST['username'];
            $connectionPassword = $_POST['password'] ?? '';
            $databaseName = $_POST['database'];
            $model = new \Core\Model();

            // Проверка за грешка
            $connected = $model->checkConnection($hostname, $connectionUsername, $connectionPassword, $databaseName);
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

                $migrated = $model->migrate();
                if (!$migrated['status']) {
                    $errorMessage = $migrated['message'];
                }
            }

            if (isset($errorMessage)) {
                $file = file_get_contents("config\constant.php");

                $file = str_replace($hostname, '{hostname}', $file);
                $file = str_replace($connectionUsername, '{host_username}', $file);
                $file = str_replace($connectionPassword, '{host_password}', $file);
                $file = str_replace($databaseName, '{database_name}', $file);

                file_put_contents("config/constant.php", $file);
            }

            if (!isset($errorMessage)) {
                header("Location: " . INSTALL_URL . "?controller=Install&action=step2", true, 301);
                exit;
            }
        }
        $this->view($this->layout, ['error_message' => $errorMessage ?? null]);
    }

    function step2()
    {
        if (INSTALLED && !MAIL_CONFIGURED) {
            header("Location: " . INSTALL_URL . '?controller=Install&action=step4', true, 301);
            exit;
        }

        $model = new \Core\Model();
        try {
            if (!$model->isDbMigrated(DEFAULT_DB)) {
                header("Location: " . INSTALL_URL . "?controller=Install&action=step1", true, 301);
                exit;
            }
        } catch (\Throwable) {
            header("Location: " . INSTALL_URL . "?controller=Install&action=step1", true, 301);
            exit;
        }

        $userModel = new \App\Models\User();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rootName = $_POST['root_name'];
            $rootEmail = $_POST['root_email'];
            $rootPassword = $_POST['root_password'];
            $rootPasswordConfirm = $_POST['root_password_confirm'];

            if ($rootPassword != $rootPasswordConfirm) {
                $errorMessage = 'Password do not match';
            }

            if (!isset($errorMessage)) {
                $root = $userModel->getFirstBy(['role' => 'root']);
                if (empty($root)) {
                    $userData = [
                        'name' => $rootName,
                        'email' => $rootEmail,
                        'password_hash' => password_hash($rootPassword, PASSWORD_DEFAULT),
                        'role' => 'root'
                    ];
                    $userModel->save($userData);
                } else {
                    $userData = [
                        'id' => $root['id'],
                        'name' => $rootName,
                        'email' => $rootEmail,
                        'password_hash' => password_hash($rootPassword, PASSWORD_DEFAULT)
                    ];
                    $userModel->update($userData);
                }

                header("Location: " . INSTALL_URL . "?controller=Install&action=step3", true, 301);
                exit;
            }
        }

        $arr['error_message'] = $errorMessage ?? null;
        $root = $userModel->getFirstBy(['role' => 'root']);
        if (!empty($root)) {
            $arr['root'] = $root;
        }
        $this->view($this->layout, $arr);
    }

    function step3()
    {
        if (INSTALLED && !MAIL_CONFIGURED) {
            header("Location: " . INSTALL_URL . '?controller=Install&action=step4', true, 301);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $file = file_get_contents("config/constant.php");
            $paypalEmail = $_POST['paypal_email'];
            $file = str_replace('{paypal_email}', $paypalEmail, $file);
            file_put_contents("config/constant.php", $file);

            header("Location: " . INSTALL_URL . "?controller=Install&action=step4", true, 301);
            exit;
        }

        $this->view($this->layout);
    }

    function step4()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $file = file_get_contents("config/constant.php");
            if (!empty($_POST['skip_mail'])) {
                try {
                    $settingModel = new \App\Models\Setting();
                    $settingModel->updateBy(['value' => 'disabled'], ['key' => 'email_sending']);
                } catch (\Throwable) {
                    echo json_encode(["error" => "Failed to update settings."]);
                    exit();
                }

                echo json_encode(["success" => true]);
                exit;
            }

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
                $file = file_get_contents("config/constant.php");

                $file = str_replace('{mail_host}', $mailHost, $file);
                $file = str_replace('{mail_port}', $mailPort, $file);
                $file = str_replace('{mail_username}', $mailUsername, $file);
                $file = str_replace('{mail_password}', $mailPassword, $file);
                $file = str_replace('"MAIL_CONFIGURED", false', '"MAIL_CONFIGURED", true', $file);

                file_put_contents("config/constant.php", $file);

                header("Location: " . INSTALL_URL . "?controller=Install&action=step5", true, 301);
                exit;
            }
        }
        $this->view($this->layout, ['error_message' => $errorMessage ?? null]);
    }

    function step5()
    {
        if (INSTALLED && !MAIL_CONFIGURED) {
            header("Location: " . INSTALL_URL . '?controller=Install&action=step4', true, 301);
            exit;
        }

        $model = new \Core\Model();
        try {
            if (!$model->isDbMigrated(DEFAULT_DB)) {
                header("Location: " . INSTALL_URL . "?controller=Install&action=step1", true, 301);
                exit;
            }
        } catch (\Throwable) {
            header("Location: " . INSTALL_URL . "?controller=Install&action=step1", true, 301);
            exit;
        }

        $userModel = new \App\Models\User();
        try {
            if (empty($userModel->getFirstBy(['role' => 'root']))) {
                header("Location: " . INSTALL_URL . "?controller=Install&action=step2", true, 301);
                exit;
            }
        } catch (\Throwable) {
            header("Location: " . INSTALL_URL . "?controller=Install&action=step1", true, 301);
            exit;
        }

        if (PAYPAL_EMAIL == '{paypal_email}') {
            header("Location: " . INSTALL_URL . "?controller=Install&action=step3", true, 301);
            exit;
        }

        $file = file_get_contents("config/constant.php");

        $file = str_replace('"INSTALLED", false', '"INSTALLED", true', $file);

        file_put_contents("config/constant.php", $file);

        $this->view($this->layout);
    }
}