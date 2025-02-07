<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class SettingsController extends Controller {

    var $layout = 'admin';

    function list() {
        $settingModel = new \App\Models\Setting();

        $this->view($this->layout, ['settings' => $settingModel->get(1)]);
    }

    function edit() {
        $settingModel = new \App\Models\Setting();

        if (!empty($_POST['id'])) {
            if ($settingModel->update($_POST)) {
                header('Location: ' . INSTALL_URL . '?controller=Settings&action=list');
                exit;
            } else {
                $error_message = 'Failed to update setting with id ' . $_POST['id'];
            }
        }

        $arr = array();
        if (isset($error_message)) {
            $arr['error_message'] = $error_message;
        }
        $arr['settings'] = $settingModel->get(1);

        $this->view($this->layout, $arr);
    }
}
