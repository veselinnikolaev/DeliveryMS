<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Notification;
use Core;
use Core\Security;
use Core\View;
use Core\Controller;

class NotificationController extends Controller
{
    public string $layout = 'admin';

    public function __construct()
    {
        parent::__construct();
        if (empty($_SESSION['user'])) {
            $this->redirect(INSTALL_URL . "?controller=Auth&action=login");
        }
    }

    public function index(): void
    {
        $this->view($this->layout);
    }

    public function markAsSeen(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $notificationModel = new Notification();
            $notificationModel->update(['id' => Security::int($this->post('id')), 'is_seen' => 1]);

            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error"]);
        }
    }

    public function markAllSeen(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $notificationModel = new Notification();
            $notificationModel->updateBy(['is_seen' => 1], ['user_id' => $_SESSION['user']['id']]);

            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error"]);
        }
    }
}
