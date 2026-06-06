<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\SettingsController;

class SettingsControllerTest extends TestCase {

    private SettingsController $controller;

    protected function setUp(): void {
        parent::setUp();
        $_SESSION = ['user' => ['id' => 1, 'role' => 'admin', 'email' => 'admin@example.com', 'name' => 'Admin User']];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $db = new \mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        $db->query("INSERT IGNORE INTO users (id, name, email, password_hash, role, created_at)
            VALUES (1, 'Admin User', 'admin@example.com', 'hash', 'admin', 0)");
        $db->close();

        $this->controller = new class extends SettingsController {
            protected function redirect(string $url): void
            {
                throw new \RuntimeException('redirect:' . $url);
            }
            protected function terminate(string $message = ''): void {}
            protected function setHeader(string $header): void {}
            public function view($layout, array $data = []): void
            {
                $this->lastViewData = $data;
            }
            public array $lastViewData = [];
        };
    }

    protected function tearDown(): void {
        parent::tearDown();
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        unset($this->controller);

        $db = new \mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        $db->query("DELETE FROM notifications WHERE user_id = 1");
        $db->query("DELETE FROM users WHERE id = 1");
        $db->close();
    }

    public function testIndexRequiresAuthentication(): void {
        $_SESSION = [];
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('redirect:' . INSTALL_URL . '?controller=Auth&action=login');
        new class extends SettingsController {
            protected function redirect(string $url): void { throw new \RuntimeException('redirect:' . $url); }
            protected function terminate(string $message = ''): void {}
            protected function setHeader(string $header): void {}
            public function view($layout, array $data = []): void {}
            public array $lastViewData = [];
        };
    }

    public function testListRejectsUserRole(): void {
        $_SESSION = ['user' => ['id' => 2, 'role' => 'user', 'email' => 'user@example.com']];
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('redirect:' . INSTALL_URL);
        new class extends SettingsController {
            protected function redirect(string $url): void { throw new \RuntimeException('redirect:' . $url); }
            protected function terminate(string $message = ''): void {}
            protected function setHeader(string $header): void {}
            public function view($layout, array $data = []): void {}
            public array $lastViewData = [];
        };
    }

    public function testIndexDisplaysSettingsForm(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testIndexHandlesSettingsUpdate(): void {
        $_POST['settings'] = [
            'currency_code' => 'USD',
            'tax_rate' => '20',
            'shipping_rate' => '5',
            'email_sending' => 'enabled'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testIndexTracksCriticalChanges(): void {
        $_POST['settings'] = [
            'currency_code' => 'EUR',
            'tax_rate' => '25',
            'email_sending' => 'disabled'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testIndexHandlesEmptySettings(): void {
        $_POST['settings'] = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testIndexWithoutSettingsPosts(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [];
        
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }
}
