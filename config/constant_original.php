<?php

define("DEFAULT_HOST", "{hostname}");
define("DEFAULT_USER", "{host_username}");
define("DEFAULT_PASS", "{host_password}");
define("DEFAULT_DB", "{database_name}");
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