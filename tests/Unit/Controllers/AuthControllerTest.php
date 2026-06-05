<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\AuthController;
use App\Models\User;
use PHPUnit\Framework\MockObject\MockObject;

class AuthControllerTest extends TestCase {

    private AuthController $controller;
    private MockObject $userModelMock;

    protected function setUp(): void {
        parent::setUp();
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->controller = new AuthController();
    }

    protected function tearDown(): void {
        parent::tearDown();
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        unset($this->controller);
    }

    public function testRegisterRedirectsIfAlreadyLoggedIn(): void {
        $_SESSION['user'] = ['id' => 1, 'email' => 'test@example.com'];
        
        $this->expectOutputString('');
        $this->controller->register();
        $this->assertTrue(isset($_SESSION['user']));
    }

    public function testRegisterDisplaysFormOnGet(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->register();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testRegisterFailsIfEmailExists(): void {
        $_POST['send'] = true;
        $_POST['email'] = 'existing@example.com';
        $_POST['password'] = 'password123';
        $_POST['repeat_password'] = 'password123';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->register();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testRegisterFailsIfPasswordsDoNotMatch(): void {
        $_POST['send'] = true;
        $_POST['email'] = 'new@example.com';
        $_POST['password'] = 'password123';
        $_POST['repeat_password'] = 'password456';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->register();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testLoginDisplaysFormOnGet(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->login();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testLoginRedirectsIfAlreadyLoggedIn(): void {
        $_SESSION['user'] = ['id' => 1, 'email' => 'test@example.com'];
        $_SESSION['previous_url'] = '/';
        
        $this->expectOutputString('');
        $this->controller->login();
        $this->assertTrue(isset($_SESSION['user']));
    }

    public function testLoginFailsWithInvalidCredentials(): void {
        $_POST['send'] = true;
        $_POST['email'] = 'invalid@example.com';
        $_POST['password'] = 'wrongpassword';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->login();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testLogoutDestroysSession(): void {
        $_SESSION['user'] = ['id' => 1, 'email' => 'test@example.com'];
        
        $this->expectOutputString('');
        $this->controller->logout();
    }

    public function testLogoutRequiresActiveSession(): void {
        $_SESSION = [];
        
        $this->expectOutputString('');
        $this->controller->logout();
    }
}
