<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class SettingsController extends Controller {

    var $layout = 'admin';
    
    public function __construct() {
        if(empty($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
            header("Location: " . INSTALL_URL . "?controller=auth&action=login", true, 301);
            exit;
        }
    }
    
    function index() {
        $settingModel = new \App\Models\Setting();

        if (!empty($_POST['id'])) {
            if ($settingModel->update($_POST)) {
                echo json_encode(['success' => true, 'message' => 'Settings updated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update settings.']);
            }
            exit;
        }

        $settings = $settingModel->get(1);       
        $this->view($this->layout, ['settings' => $settings]);
    }
}
