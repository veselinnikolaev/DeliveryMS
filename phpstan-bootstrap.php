<?php
// Define constants PHPStan needs to know about
if (!defined('INSTALL_URL')) {
    define('INSTALL_URL', 'http://localhost/index.php');
}
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__ . '/');
}
if (!defined('INSTALL_FOLDER')) {
    define('INSTALL_FOLDER', '/');
}
if (!defined('INSTALLED')) {
    define('INSTALLED', false);
}
if (!defined('MAIL_CONFIGURED')) {
    define('MAIL_CONFIGURED', false);
}
if (!defined('DEFAULT_HOST')) {
    define('DEFAULT_HOST', 'localhost');
}
if (!defined('DEFAULT_USER')) {
    define('DEFAULT_USER', 'root');
}
if (!defined('DEFAULT_PASS')) {
    define('DEFAULT_PASS', '');
}
if (!defined('DEFAULT_DB')) {
    define('DEFAULT_DB', 'deliveryms');
}
if (!defined('PAYPAL_EMAIL')) {
    define('PAYPAL_EMAIL', 'test@test.com');
}
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', __DIR__ . '/web/upload/');
}