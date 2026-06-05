<?php

declare(strict_types=1);

namespace Core;

class Router {

    public function resolve(): void {
        $controllerName = $_REQUEST['controller'] ?? null;
        $actionName = $_REQUEST['action'] ?? null;

        if (!$controllerName || !$actionName) {
            http_response_code(400);
            echo "Controller or action not specified!";
            return;
        }

        $controllerClass = "\\App\\Controllers\\" . ucfirst($controllerName) . "Controller";

        if (!class_exists($controllerClass)) {
            http_response_code(404);
            echo "Controller '{$controllerClass}' not found!";
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $actionName)) {
            http_response_code(404);
            echo "Action '{$actionName}' not found in '{$controllerClass}'!";
            return;
        }

        // Call the controller method
        $controller->$actionName();
    }
}
