<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Notification;
use Core;
use Core\View;
use Core\Controller;

class AuthController extends Controller {

    public string $layout = 'admin';

    public function register(): void {
        if (!empty($_SESSION['user'])) {
            $this->redirect(INSTALL_URL);
        }

        $userModel = new User();

        if (!empty($this->post('send'))) {
            if ($userModel->existsBy(['email' => $this->post('email')])) {
                $error_message = "User with this email already exists.";
            } else if ($this->post('password') !== $this->post('repeat_password')) {
                $error_message = "Passwords do not match.";
            } else {
                $postData = $this->post();
                $postData['password_hash'] = password_hash($this->post('password'), PASSWORD_DEFAULT);
                $postData['role'] = 'user';

                if ($userModel->save($postData)) {
                    $this->redirect(INSTALL_URL . "?controller=Auth&action=login");
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

    public function login(): void {
        if (!empty($_SESSION['user'])) {
            $this->redirect($_SESSION['previous_url'] ?? INSTALL_URL);
        }

        $userModel = new User();

        if (!empty($this->post('send'))) {
            $user = $userModel->getFirstBy(['email' => $this->post('email')]);

            if ($user && password_verify($this->post('password'), $user['password_hash'])) {
                $_SESSION['user'] = $user;

                $notificationModel = new Notification();
                $notificationModel->save([
                    'user_id' => $user['id'],
                    'message' => 'New login detected.',
                    'link' => INSTALL_URL . '?controller=Home&action=index',
                    'created_at' => time()
                ]);

                $this->redirect($_SESSION['previous_url'] ?? INSTALL_URL);
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

    public function logout(): void
    {
        if (empty($_SESSION['user'])) {
            $this->redirect(INSTALL_URL);
        }

        $_SESSION = [];
        session_destroy();

        $this->redirect(INSTALL_URL . "?controller=Auth&action=login");
    }
}
