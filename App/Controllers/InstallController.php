<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Model;
use Core\Services\MailService;
use App\Models\User;
use App\Models\Setting;
use Core\Controller;

class InstallController extends Controller
{
    public string $layout = 'front';

    public function __construct()
    {
        parent::__construct();
        if (INSTALLED && MAIL_CONFIGURED && !str_contains($_SESSION['previous_url'], '?controller=Settings&action=index')) {
            $this->redirect($_SESSION['previous_url']);
        }
    }

    protected function loadSettings(): array
    {
        return [];
    }

    public function step0()
    {
        if (INSTALLED && !MAIL_CONFIGURED) {
            $this->redirect(INSTALL_URL . '?controller=Install&action=step4');
        }

        $this->view($this->layout);
    }

    public function step1(): void
    {
        if (INSTALLED && !MAIL_CONFIGURED) {
            $this->redirect(INSTALL_URL . '?controller=Install&action=step4');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hostname = $this->post('hostname');
            $connectionUsername = $this->post('username');
            $connectionPassword = $this->post('password', '');
            $databaseName = $this->post('database');
            $model = new Model();

            // Check for error
            $connected = $model->checkConnection($hostname, $connectionUsername, $connectionPassword, $databaseName);
            if (!$connected['status']) {
                $errorMessage = $connected['message'];
            }

            if (!isset($errorMessage)) {
                // Write to .env file instead of config/constant.php
                $envPath = __DIR__ . '/../../.env';
                $envContent = file_get_contents($envPath);

                // Update .env with database credentials
                $envContent = preg_replace('/DB_HOST=.*/', 'DB_HOST=' . $hostname, $envContent);
                $envContent = preg_replace('/DB_NAME=.*/', 'DB_NAME=' . $databaseName, $envContent);
                $envContent = preg_replace('/DB_USER=.*/', 'DB_USER=' . $connectionUsername, $envContent);
                $envContent = preg_replace('/DB_PASS=.*/', 'DB_PASS=' . $connectionPassword, $envContent);

                file_put_contents($envPath, $envContent);

                $migrated = $model->migrate();
                if (!$migrated['status']) {
                    $errorMessage = $migrated['message'];
                }
            }

            if (isset($errorMessage)) {
                // Revert .env changes on error
                $envPath = __DIR__ . '/../../.env';
                $envContent = file_get_contents($envPath);

                $envContent = preg_replace('/DB_HOST=.*/', 'DB_HOST={hostname}', $envContent);
                $envContent = preg_replace('/DB_NAME=.*/', 'DB_NAME={database_name}', $envContent);
                $envContent = preg_replace('/DB_USER=.*/', 'DB_USER={host_username}', $envContent);
                $envContent = preg_replace('/DB_PASS=.*/', 'DB_PASS={host_password}', $envContent);

                file_put_contents($envPath, $envContent);
            }

            if (!isset($errorMessage)) {
                $this->redirect(INSTALL_URL . "?controller=Install&action=step2");
            }
        }
        $this->view($this->layout, ['error_message' => $errorMessage ?? null]);
    }

    public function step2(): void
    {
        if (INSTALLED && !MAIL_CONFIGURED) {
            $this->redirect(INSTALL_URL . '?controller=Install&action=step4');
        }

        $model = new Model();
        try {
            if (!$model->isDbMigrated(DEFAULT_DB)) {
                $this->redirect(INSTALL_URL . "?controller=Install&action=step1");
            }
        } catch (\Throwable $e) {
            $this->redirect(INSTALL_URL . "?controller=Install&action=step1");
        }

        $userModel = new User();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rootName = $this->post('root_name');
            $rootEmail = $this->post('root_email');
            $rootPassword = $this->post('root_password');
            $rootPasswordConfirm = $this->post('root_password_confirm');

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

                $this->redirect(INSTALL_URL . "?controller=Install&action=step3");
            }
        }

        $arr['error_message'] = $errorMessage ?? null;
        $root = $userModel->getFirstBy(['role' => 'root']);
        if (!empty($root)) {
            $arr['root'] = $root;
        }
        $this->view($this->layout, $arr);
    }

    public function step3(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paypalEmail = $this->post('paypal_email');

            // Write to .env file instead of config/constant.php
            $envPath = __DIR__ . '/../../.env';
            $envContent = file_get_contents($envPath);

            // Update PAYPAL_EMAIL in .env
            $envContent = preg_replace('/PAYPAL_EMAIL=.*/', 'PAYPAL_EMAIL=' . $paypalEmail, $envContent);

            file_put_contents($envPath, $envContent);

            if (str_contains($_SESSION['previous_url'], '?controller=Settings&action=index')) {
                $this->redirect(INSTALL_URL . "?controller=Settings&action=index");
            }
            $this->redirect(INSTALL_URL . "?controller=Install&action=step4");
        }

        $this->view($this->layout);
    }

    public function step4(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($this->post('skip_mail'))) {
                try {
                    $settingModel = new Setting();
                    $settingModel->updateBy(['value' => 'disabled'], ['key' => 'email_sending']);
                } catch (\Throwable $e) {
                    echo json_encode(["error" => $e->getMessage()]);
                    $this->terminate();
                }

                echo json_encode(["success" => true]);
                $this->terminate();
            }

            $mailHost = $this->post('mail_host');
            $mailPort = $this->post('mail_port');
            $mailUsername = $this->post('mail_username');
            $mailPassword = $this->post('mail_password');
            $mailer = new MailService();

            $connected = $mailer->checkConnection($mailHost, $mailPort, $mailUsername, $mailPassword);
            if (!$connected['status']) {
                $errorMessage = $connected['message'];
            }

            if (!isset($errorMessage)) {
                // Write to .env file instead of config/constant.php
                $envPath = __DIR__ . '/../../.env';
                $envContent = file_get_contents($envPath);

                // Update mail settings in .env
                $envContent = preg_replace('/MAIL_HOST=.*/', 'MAIL_HOST=' . $mailHost, $envContent);
                $envContent = preg_replace('/MAIL_PORT=.*/', 'MAIL_PORT=' . $mailPort, $envContent);
                $envContent = preg_replace('/MAIL_USERNAME=.*/', 'MAIL_USERNAME=' . $mailUsername, $envContent);
                $envContent = preg_replace('/MAIL_PASSWORD=.*/', 'MAIL_PASSWORD=' . $mailPassword, $envContent);
                $envContent = preg_replace('/MAIL_CONFIGURED=.*/', 'MAIL_CONFIGURED=true', $envContent);

                file_put_contents($envPath, $envContent);

                $this->redirect(INSTALL_URL . "?controller=Install&action=step5");
            }
        }
        $this->view($this->layout, ['error_message' => $errorMessage ?? null]);
    }

    public function step5(): void
    {
        if (INSTALLED && !MAIL_CONFIGURED) {
            $this->redirect(INSTALL_URL . '?controller=Install&action=step4');
        }

        $model = new Model();
        try {
            if (!$model->isDbMigrated(DEFAULT_DB)) {
                $this->redirect(INSTALL_URL . "?controller=Install&action=step1");
            }
        } catch (\Throwable) {
            $this->redirect(INSTALL_URL . "?controller=Install&action=step1");
        }

        $userModel = new User();
        try {
            if (empty($userModel->getFirstBy(['role' => 'root']))) {
                $this->redirect(INSTALL_URL . "?controller=Install&action=step2");
            }
        } catch (\Throwable $e) {
            $this->redirect(INSTALL_URL . "?controller=Install&action=step1");
        }

        if (PAYPAL_EMAIL == '{paypal_email}') {
            $this->redirect(INSTALL_URL . "?controller=Install&action=step3");
        }

        // Write to .env file instead of config/constant.php
        $envPath = __DIR__ . '/../../.env';
        $envContent = file_get_contents($envPath);

        // Update INSTALLED in .env
        $envContent = preg_replace('/INSTALLED=.*/', 'INSTALLED=true', $envContent);

        file_put_contents($envPath, $envContent);

        $this->view($this->layout);
    }
}
