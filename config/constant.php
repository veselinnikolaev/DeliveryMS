<?php

define("DEFAULT_HOST", "{hostname}");
define("DEFAULT_USER", "{host_username}");
define("DEFAULT_PASS", "{host_password}");
define("DEFAULT_DB", "{database_name}");
define("MAIL_HOST", "{mail_host}");
define("MAIL_PORT", "{mail_port}");
define("MAIL_USERNAME", "{mail_username}");
define("MAIL_PASSWORD", "{mail_password}");

if (DEFAULT_HOST == "{hostname}" || DEFAULT_USER == "{host_username}" ||
        DEFAULT_PASS == "{host_password}" || DEFAULT_DB == "{database_name}") {
    // Пренасочване към инсталационната страница
    header("Location: " . INSTALL_URL . "?controller=Install&action=step0", true, 301);
    exit;
}