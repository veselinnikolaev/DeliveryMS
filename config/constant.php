<?php

define("DEFAULT_HOST", "127.0.0.1:8111");
define("DEFAULT_USER", "root");
define("DEFAULT_PASS", "");
define("DEFAULT_DB", "deliverymanagementsystem");
define("MAIL_HOST", "{mail_host}");
define("MAIL_PORT", "{mail_port}");
define("MAIL_USERNAME", "{mail_username}");
define("MAIL_PASSWORD", "{mail_password}");
define("INSTALLED", false);

if (!INSTALLED) {
    // Пренасочване към инсталационната страница
    if (empty($_REQUEST['action'])) {
        $_REQUEST['controller'] = 'Install';
        $_REQUEST['action'] = 'step0';
    }
}