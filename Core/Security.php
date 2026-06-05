<?php

namespace Core;

class Security {

    /**
     * Sanitize input data to prevent XSS attacks
     * 
     * @param mixed $data The data to sanitize
     * @return mixed Sanitized data
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        
        // Convert special characters to HTML entities
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        
        // Remove any NULL bytes
        $data = str_replace(chr(0), '', $data);
        
        return $data;
    }

    /**
     * Get sanitized POST data
     * 
     * @param string|null $key Specific key to retrieve, or null for all POST data
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Sanitized POST data
     */
    public static function post($key = null, $default = null) {
        if ($key === null) {
            return self::sanitize($_POST);
        }
        
        return isset($_POST[$key]) ? self::sanitize($_POST[$key]) : $default;
    }

    /**
     * Get sanitized GET data
     * 
     * @param string|null $key Specific key to retrieve, or null for all GET data
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Sanitized GET data
     */
    public static function get($key = null, $default = null) {
        if ($key === null) {
            return self::sanitize($_GET);
        }
        
        return isset($_GET[$key]) ? self::sanitize($_GET[$key]) : $default;
    }

    /**
     * Get sanitized REQUEST data
     * 
     * @param string|null $key Specific key to retrieve, or null for all REQUEST data
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Sanitized REQUEST data
     */
    public static function request($key = null, $default = null) {
        if ($key === null) {
            return self::sanitize($_REQUEST);
        }
        
        return isset($_REQUEST[$key]) ? self::sanitize($_REQUEST[$key]) : $default;
    }

    /**
     * Validate and sanitize an integer
     * 
     * @param mixed $value The value to validate
     * @param int $default Default value if validation fails
     * @return int Validated integer
     */
    public static function int($value, $default = 0) {
        if (is_numeric($value)) {
            return (int) $value;
        }
        return $default;
    }

    /**
     * Validate and sanitize a float
     * 
     * @param mixed $value The value to validate
     * @param float $default Default value if validation fails
     * @return float Validated float
     */
    public static function float($value, $default = 0.0) {
        if (is_numeric($value)) {
            return (float) $value;
        }
        return $default;
    }

    /**
     * Validate an email address
     * 
     * @param string $email The email to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate a URL
     * 
     * @param string $url The URL to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Generate a CSRF token
     * 
     * @return string The generated token
     */
    public static function generateCsrfToken() {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        // Generate a random token
        $token = bin2hex(random_bytes(32));
        
        // Store token in session
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }

    /**
     * Get the current CSRF token (generate if doesn't exist)
     * 
     * @return string The CSRF token
     */
    public static function getCsrfToken() {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        // Generate token if it doesn't exist or is expired (1 hour)
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time']) || 
            (time() - $_SESSION['csrf_token_time']) > 3600) {
            return self::generateCsrfToken();
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate a CSRF token
     * 
     * @param string $token The token to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateCsrfToken($token) {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        // Check if token exists in session
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Check if token is expired (1 hour)
        if (isset($_SESSION['csrf_token_time']) && 
            (time() - $_SESSION['csrf_token_time']) > 3600) {
            return false;
        }
        
        // Compare tokens using timing-safe comparison
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Validate CSRF token from POST data
     * 
     * @return bool True if valid, false otherwise
     */
    public static function validateCsrfFromPost() {
        $token = self::post('csrf_token');
        return self::validateCsrfToken($token);
    }

    /**
     * Generate HTML for CSRF token input field
     * 
     * @return string HTML input element
     */
    public static function csrfField() {
        $token = self::getCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Regenerate CSRF token (useful after login or sensitive operations)
     * 
     * @return string The new token
     */
    public static function regenerateCsrfToken() {
        return self::generateCsrfToken();
    }

    /**
     * Clear CSRF token from session
     * 
     * @return void
     */
    public static function clearCsrfToken() {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
    }
}
