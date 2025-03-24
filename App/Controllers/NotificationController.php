<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class NotificationController extends Controller {

    var $layout = 'admin';

    public function __construct() {
        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
            exit;
        }
    }

    function index() {
        $this->view($this->layout);
    }

    function markAsSeen() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $notificationModel = new \App\Models\Notification();
            $notificationModel->update(['id' => $_POST['id'], 'is_seen' => 1]);

            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error"]);
        }
    }

    function markAllSeen() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $notificationModel = new \App\Models\Notification();
            $notificationModel->updateBy(['is_seen' => 1], ['user_id' => $_SESSION['user']['id']]);

            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error"]);
        }
    }
}
