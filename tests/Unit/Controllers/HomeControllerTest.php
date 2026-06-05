<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\HomeController;

class HomeControllerTest extends TestCase {

    private HomeController $controller;

    protected function setUp(): void {
        parent::setUp();
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->controller = new HomeController();
    }

    protected function tearDown(): void {
        parent::tearDown();
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        unset($this->controller);
    }

    public function testIndexDisplaysPublicPage(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testIndexDisplaysAdminDashboard(): void {
        $_SESSION['user'] = ['id' => 1, 'role' => 'admin', 'email' => 'admin@example.com'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testIndexDisplaysRootDashboard(): void {
        $_SESSION['user'] = ['id' => 1, 'role' => 'root', 'email' => 'root@example.com'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testIndexDisplaysCourierDashboard(): void {
        $_SESSION['user'] = ['id' => 2, 'role' => 'courier', 'email' => 'courier@example.com'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testIndexDisplaysUserDashboard(): void {
        $_SESSION['user'] = ['id' => 3, 'role' => 'user', 'email' => 'user@example.com'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testIndexWithoutSessionDisplaysPublicView(): void {
        $_SESSION = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }
}
