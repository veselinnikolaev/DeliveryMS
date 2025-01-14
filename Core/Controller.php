<?php

namespace Core;

class Controller{
    

    public function view($layout, $data = []) {
        View::render($layout, $data);
    }
}