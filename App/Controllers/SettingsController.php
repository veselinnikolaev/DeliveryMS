<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class SettingsController extends Controller {

    var $layout = 'admin';
    
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
