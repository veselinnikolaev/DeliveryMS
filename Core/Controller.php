<?php

declare(strict_types=1);

namespace Core;

class Controller {

    protected Security $security;

    public function __construct() {
        $this->security = new Security();
        $this->validateCsrfOnPost();
    }

    /**
     * Automatically validate CSRF token on POST requests
     * Can be overridden in child controllers if needed
     * 
     * @return void
     */
    protected function validateCsrfOnPost(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Skip CSRF validation for AJAX requests (they should handle it differently)
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            if (!$isAjax && !Security::validateCsrfFromPost()) {
                // CSRF validation failed
                http_response_code(403);
                die('CSRF token validation failed. Please refresh the page and try again.');
            }
        }
    }

    /**
     * Get sanitized POST data
     * 
     * @param string|null $key Specific key to retrieve, or null for all POST data
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Sanitized POST data
     */
    protected function post(?string $key = null, $default = null): mixed {
        return Security::post($key, $default);
    }

    /**
     * Get sanitized GET data
     * 
     * @param string|null $key Specific key to retrieve, or null for all GET data
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Sanitized GET data
     */
    protected function get(?string $key = null, $default = null): mixed {
        return Security::get($key, $default);
    }

    /**
     * Get sanitized REQUEST data
     * 
     * @param string|null $key Specific key to retrieve, or null for all REQUEST data
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Sanitized REQUEST data
     */
    protected function request(?string $key = null, $default = null): mixed {
        return Security::request($key, $default);
    }

    public function view($layout, array $data = []): void {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$isAjax) {
            $uri = rtrim($_SERVER['REQUEST_URI'], '/');
            $current = rtrim($_SESSION['current_url'] ?? INSTALL_URL, '/');

// Skip requests to files with extensions
            if (!preg_match('/\.(jpg|jpeg|png|gif|css|js|ico|svg|pdf)$/i', $uri)) {
                if ($current !== $uri) {
                    $_SESSION['previous_url'] = $_SESSION['current_url'] ?? INSTALL_URL;
                    $_SESSION['current_url'] = $uri;
                }
            }
        }

        // Add CSRF token to all views
        $data['csrf_token'] = Security::getCsrfToken();

        View::render($layout, $data);
    }
}
