<?php

declare(strict_types=1);

namespace Core;

use App\Models\Setting;

class Controller
{
    protected Security $security;
    public array $settings;

    public function __construct()
    {
        $this->security = new Security();
        $this->validateCsrfOnPost();
        $this->settings = $this->loadSettings();
    }

    /**
     * Load application settings from database
     *
     * @return array Application settings
     */
    protected function loadSettings(): array
    {
        try {
            $settingModel = new Setting();
            $settings = $settingModel->getAll();
            $app_settings = [];
            foreach ($settings as $setting) {
                $app_settings[$setting['key']] = $setting['value'];
            }
            return $app_settings;
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function terminate(string $message = ''): void
    {
        if ($message !== '') {
            echo $message;
        }
        exit;
    }

    protected function setHeader(string $header): void
    {
        header($header);
    }

    protected function redirect(string $url): void
    {
        header("Location: " . $url, true, 301);
        $this->terminate();
    }

    /**
     * Automatically validate CSRF token on POST requests
     * Can be overridden in child controllers if needed
     *
     * @return void
     */
    protected function validateCsrfOnPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

            if ($isAjax) {
                $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
                if (!Security::validateCsrfToken($csrfToken)) {
                    $this->setHeader('HTTP/1.1 403 Forbidden');
                    $this->terminate('CSRF token validation failed. Please refresh the page and try again.');
                }
            } else {
                if (!Security::validateCsrfFromPost()) {
                    $this->setHeader('HTTP/1.1 403 Forbidden');
                    $this->terminate('CSRF token validation failed. Please refresh the page and try again.');
                }
            }
        }
    }

    /**
     * Get sanitized POST data
     *
     * @param string|null $key Specific key to retrieve, or null for all POST data
     * @param mixed|null $default Default value if key doesn't exist
     * @return mixed Sanitized POST data
     */
    protected function post(?string $key = null, mixed $default = null): mixed
    {
        return Security::post($key, $default);
    }

    /**
     * Get sanitized GET data
     *
     * @param string|null $key Specific key to retrieve, or null for all GET data
     * @param mixed|null $default Default value if key doesn't exist
     * @return mixed Sanitized GET data
     */
    protected function get(?string $key = null, mixed $default = null): mixed
    {
        return Security::get($key, $default);
    }

    /**
     * Get sanitized REQUEST data
     *
     * @param string|null $key Specific key to retrieve, or null for all REQUEST data
     * @param mixed|null $default Default value if key doesn't exist
     * @return mixed Sanitized REQUEST data
     */
    protected function request(?string $key = null, mixed $default = null): mixed
    {
        return Security::request($key, $default);
    }

    public function view($layout, array $data = []): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$isAjax) {
            $uri = rtrim($_SERVER['REQUEST_URI'], '/');
            $current = rtrim($_SESSION['current_url'] ?? INSTALL_URL, '/');

// Skip requests to files with extensions
            if (!preg_match('/\.(jpg|jpeg|png|gif|css|js|ico|svg|pdf|map|ts|json)$/i', $uri)) {
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
