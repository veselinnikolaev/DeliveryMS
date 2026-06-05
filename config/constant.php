<?php

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Define constants from environment variables
define("DEFAULT_HOST", $_ENV['DB_HOST'] ?? '{hostname}');
define("DEFAULT_USER", $_ENV['DB_USER'] ?? '{host_username}');
define("DEFAULT_PASS", $_ENV['DB_PASS'] ?? '{host_password}');
define("DEFAULT_DB", $_ENV['DB_NAME'] ?? '{database_name}');
define("PAYPAL_EMAIL", $_ENV['PAYPAL_EMAIL'] ?? '{paypal_email}');
define("MAIL_HOST", $_ENV['MAIL_HOST'] ?? '{mail_host}');
define("MAIL_PORT", $_ENV['MAIL_PORT'] ?? '{mail_port}');
define("MAIL_USERNAME", $_ENV['MAIL_USERNAME'] ?? '{mail_username}');
define("MAIL_PASSWORD", $_ENV['MAIL_PASSWORD'] ?? '{mail_password}');
define("INSTALLED", filter_var($_ENV['INSTALLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN));
define("MAIL_CONFIGURED", filter_var($_ENV['MAIL_CONFIGURED'] ?? 'false', FILTER_VALIDATE_BOOLEAN));
define("UPLOAD_PATH", __DIR__ . '/../web/upload/');