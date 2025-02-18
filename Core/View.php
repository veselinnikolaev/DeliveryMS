<?php

namespace Core;

class View {

    public static function render($layout, array $tpl = []): void {
        $controller = $_REQUEST['controller'] ?? null;
        $action = $_REQUEST['action'] ?? null;

        if (!$controller || !$action) {
            echo "Controller or action not specified for view.";
            return;
        }

        $viewPath = "App/Views/" . strtolower($controller) . "/" . strtolower($action) . ".php";

        if (!file_exists($viewPath)) {
            echo "View '{$viewPath}' not found.";
            return;
        }

        // Извличане на променливите
        extract($tpl);
        // Зареждане на изгледа
        include "App/Views/Layouts/" . $layout . ".php";
    }
}
