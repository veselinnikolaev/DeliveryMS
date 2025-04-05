<?php

namespace Core;

class Controller {

    public function view($layout, $data = []) {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$isAjax) {
            $uri = $_SERVER['REQUEST_URI'];

            // Пропускаме заявки към файлове със разширения
            if (!preg_match('/\.(jpg|jpeg|png|gif|css|js|ico|svg|pdf)$/i', $uri)) {
                $_SESSION['previous_url'] = $_SESSION['current_url'] ?? null;
                $_SESSION['current_url'] = $uri;
            }
        }

        View::render($layout, $data);
    }
}
