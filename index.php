<?php

if (!headers_sent()) {
    session_start();
}

header("Content-type: text/html; charset=utf-8");

if (!defined("ROOT_PATH")) {
    define("ROOT_PATH", dirname(__FILE__) . '/');
}

if (!defined("INSTALL_FOLDER")) {
    $pathinfo = pathinfo($_SERVER["PHP_SELF"]);
    define("INSTALL_FOLDER", $pathinfo['dirname'] . '/');
}

if (!defined("INSTALL_URL")) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

// Get the domain (host)
    $domain = $_SERVER['HTTP_HOST'];

// Get the current script path (excluding query parameters)
    $path = dirname($_SERVER['PHP_SELF']);

// Combine the protocol, domain, and path
    $fullUrl = $protocol . "://" . $domain . $path . '/index.php';
    define("INSTALL_URL", $fullUrl);
}

require_once 'config/constant.php';
require_once 'config/function.php';

if (!INSTALLED) {
    // Пренасочване към инсталационната страница
    if (empty($_REQUEST['controller'])) {
        $_REQUEST['controller'] = 'Install';
    }

    if (empty($_REQUEST['action'])) {
        $_REQUEST['action'] = 'step0';
    }
} else {
    if (empty($_REQUEST['controller'])) {
        $_REQUEST['controller'] = 'Home';
    }

    if (empty($_REQUEST['action'])) {
        $_REQUEST['action'] = 'index';
    }
}

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    } else {
        echo "Class file '{$file}' not found.<br>";
    }
});

$router = new Core\Router();
$router->resolve();
