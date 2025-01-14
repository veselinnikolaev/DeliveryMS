<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class SettingsController extends Controller{
    
    var $layout = 'admin';

    public function index() {

        $galleryModel = new \App\Models\Gallery();

        // Извличане на всички записи от таблицата gallery
        $galleries = $galleryModel->getAll();

        // Прехвърляне на данни към изгледа
        $this->view($this->layout, ['galleries' => $galleries]);
    }
}
