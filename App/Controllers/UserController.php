<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class UserController extends Controller {

    var $layout = 'admin';

    public function index() {

        $userModel = new \App\Models\User();

        $tpl = array();

        // Извличане на всички записи от таблицата gallery
        $tpl['users'] = $userModel->getAll();

        // Прехвърляне на данни към изгледа
        $this->view($this->layout, $tpl);
    }
}
