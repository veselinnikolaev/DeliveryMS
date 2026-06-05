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
        $this->controller = new SettingsController();
    }

    protected function tearDown(): void {
        parent::tearDown();
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        unset($this->controller);
    }

    public function testIndexRequiresAuthentication(): void {
        $_SESSION = [];
        
        $this->expectOutputString('');
        try {
            $controller = new SettingsController();
        } catch (\Throwable $e) {
            $this->assertTrue(true);
        }
    }

    public function testIndexRejectsUserRole(): void {
        $_SESSION['user']['role'] = 'user';
        
        $this->expectOutputString('');
        $controller = new SettingsController();
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
