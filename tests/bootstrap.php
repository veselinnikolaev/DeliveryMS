<?php

declare(strict_types=1);

// Load your constants/config first
require_once __DIR__ . '/../config/constant.php';

// Then start the session before PHPUnit outputs anything
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}