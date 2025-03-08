<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class SettingsController extends Controller {

    var $layout = 'admin';
    
    public function __construct() {
        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
            exit;
        }
        if ($_SESSION['user']['role'] == 'user') {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }
    }
    
    function index() {
        $settingModel = new \App\Models\Setting();
    
        if (!empty($_POST['settings'])) {
            $updateData = [];
    
            foreach ($_POST['settings'] as $key => $value) {
                $updateData[] = [
                    'key' => $key,
                    'value' => $value
                ];
            }
    
            if ($settingModel->updateBatch($updateData, 'key')) {
                echo json_encode(['success' => true, 'message' => 'Settings updated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update settings.']);
            }
            exit;
        }
    
        $settings = $settingModel->getAll();
        $this->view($this->layout, ['settings' => $settings]);
    }
}
