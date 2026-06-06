<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Force load .env.testing
if (file_exists(__DIR__ . '/../.env.testing')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..', '.env.testing');
    $dotenv->load();
}

// Ensure the environment variables are accessible to $_ENV
// If they aren't, load them into superglobals
foreach ($_ENV as $key => $value) {
    if (!isset($_SERVER[$key])) {
        $_SERVER[$key] = $value;
    }
}

// Simulate $_SERVER vars index.php normally provides
$_SERVER['HTTPS']     = 'off';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF']  = '/index.php';
$_SERVER['REQUEST_URI'] = '/';

// Define constants index.php sets before requiring constant.php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__) . '/');
}

if (!defined('INSTALL_FOLDER')) {
    define('INSTALL_FOLDER', '/');
}

if (!defined('INSTALL_URL')) {
    define('INSTALL_URL', 'http://localhost/index.php');
}

// Load app config
require_once __DIR__ . '/../config/constant.php';
require_once __DIR__ . '/../config/function.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}