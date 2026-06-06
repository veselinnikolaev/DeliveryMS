<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\NotificationController;

class NotificationControllerTest extends TestCase {

    private $controller;

    protected function setUp(): void {
        parent::setUp();
        $_SESSION = ['user' => ['id' => 1, 'email' => 'user@example.com', 'name' => 'User']];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->controller = new class extends NotificationController {
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
    }

    public function testIndexDisplaysNotifications(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testIndexRequiresAuthentication(): void {
        $_SESSION = [];
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('redirect:' . INSTALL_URL . '?controller=Auth&action=login');
        new class extends NotificationController {
            protected function redirect(string $url): void { throw new \RuntimeException('redirect:' . $url); }
            protected function terminate(string $message = ''): void {}
            protected function setHeader(string $header): void {}
            public function view($layout, array $data = []): void {}
            public array $lastViewData = [];
        };
    }

    public function testMarkAsSeenUpdatesNotification(): void {
        $_POST['id'] = '1';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->markAsSeen();
        $output = ob_get_clean();
        
        $json = json_decode($output, true);
        $this->assertIsArray($json);
        $this->assertArrayHasKey('status', $json);
    }

    public function testMarkAsSeenRejectsGetRequest(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->markAsSeen();
        $output = ob_get_clean();
        
        $json = json_decode($output, true);
        $this->assertIsArray($json);
        $this->assertEquals('error', $json['status']);
    }

    public function testMarkAllSeenUpdatesAllNotifications(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->markAllSeen();
        $output = ob_get_clean();
        
        $json = json_decode($output, true);
        $this->assertIsArray($json);
        $this->assertArrayHasKey('status', $json);
    }

    public function testMarkAllSeenRejectsGetRequest(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->markAllSeen();
        $output = ob_get_clean();
        
        $json = json_decode($output, true);
        $this->assertIsArray($json);
        $this->assertEquals('error', $json['status']);
    }
}
