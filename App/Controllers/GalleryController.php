<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class GalleryController extends Controller {

    var $layout = 'admin';
    
    function __construct() {
        if(empty($_SESSION['users'])){
            //header("Location: " . INSTALL_URL . "?controller=Admin&action=login", true, 301);
        }
    }

    public function index() {

        $galleryModel = new \App\Models\Gallery();

        // Извличане на всички записи от таблицата gallery
        $galleries = $galleryModel->getAll();

        // Прехвърляне на данни към изгледа
        $this->view($this->layout, ['galleries' => $galleries]);
    }

    public function edit() {
        $galleryModel = new \App\Models\Gallery();
        $imageModel = new \App\Models\Image();

        if (!empty($_POST['send_frm'])) {
            $data = array();
            $data['id'] = $_POST['id'];
            $data['gallery_name'] = $_POST['gallery_name'];

            $galleryModel->update($data);

            header("Location: " . INSTALL_URL . "?controller=Gallery&action=index", true, 301);
        }

        $arr = array();
        $arr['gallery'] = $galleryModel->get($_REQUEST['id']);

        $opts = array();
        $opts['gallery_id'] = $_REQUEST['id'];
        $arr['images'] = $imageModel->getAll($opts);

        // Прехвърляне на данни към изгледа
        $this->view($this->layout, $arr);
    }

    public function upload() {
        $imageModel = new \App\Models\Image();

        require 'App\Helpers\uploader\src\class.upload.php';

        $handle = new \Verot\Upload\Upload($_FILES['file']);

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $img_name = time();

            if ($handle->uploaded) {

                $thumb_dest = 'web/upload';

                $handle->file_new_name_body = $img_name;
                $handle->image_resize = true;
                $handle->image_x = 200;
                $handle->image_ratio_y = true;

                $handle->process($thumb_dest);

                if ($handle->processed) {
                    $handle->clean();
                } else {
                    echo 'error : ' . $handle->error;
                }

                $data = array();
                $data['image_name'] = $handle->file_dst_name;
                $data['gallery_id'] = $_POST['id'];
                $imageModel->save($data);
            }
        } else {
            echo "Error: " . $_FILES['file']['error'];
        }

        $arr = array();

        $opts = array();
        $opts['gallery_id'] = $_POST['id'];
        $arr['images'] = $imageModel->getAll($opts);

        $this->view('ajax', $arr);
    }

    function deleteImage() {

        $imageModel = new \App\Models\Image();

        $arr = array();

        $img_arr = $imageModel->get($_POST['id']);

        $imageModel->delete($_POST['id']);

        $path = ROOT_PATH . "web/upload/" . $img_arr['image_name'];

        if (file_exists($path)) {
            unlink($path);
        }

        $this->view('ajax', $arr);
    }
}
