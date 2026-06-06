<?php

declare(strict_types=1);

namespace Core;

use App\Models\Notification;
use App\Models\Setting;

class View
{
    public static function render($layout, array $tpl = []): void
    {
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

        if (INSTALLED) {
            //Notifications
            if (!empty($_SESSION['user'])) {
                $notificationModel = new Notification();
                $tpl['notifications'] = $notificationModel->getAll(['user_id' => $_SESSION['user']['id']], 'created_at DESC');
            }

            $settingModel = new Setting();
            date_default_timezone_set($settingModel->getFirstBy(['key' => 'timezone'])['value']);
            $tpl['date_format'] = $settingModel->getFirstBy(['key' => 'date_format'])['value'];
        }
        $defaults = [
            'csrf_token'    => Security::getCsrfToken(),
            'user'          => $_SESSION['user'] ?? null,
            'notifications' => [],
            'tpl'           => $tpl, // Keep the original array available as $tpl
        ];
        $tpl = array_merge($defaults, $tpl);

        extract($tpl);

        // Now include the layout, which likely contains the include $viewPath;
        include "App/Views/Layouts/" . $layout . ".php";
    }
}
