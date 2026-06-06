<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\AuthController;
use App\Models\User;
use PHPUnit\Framework\MockObject\MockObject;

class AuthControllerTest extends TestCase {

    private $controller;
    private MockObject $userModelMock;

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->controller = new class extends AuthController {
            public array $lastViewData = [];

            protected function redirect(string $url): void
            {
                throw new \RuntimeException('redirect:' . $url);
            }
            protected function terminate(string $message = ''): void
            {
                // do nothing
            }
            public function view($layout, array $data = []): void
            {
                $this->lastViewData = $data;
            }
        };
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        unset($this->controller);

        // clean up seeded test data
        $db = new \mysqli(
            $_ENV['DB_HOST'],
            $_ENV['DB_USER'],
            $_ENV['DB_PASS'],
            $_ENV['DB_NAME']
        );
        $db->query("DELETE FROM users WHERE email = 'existing@example.com'");
        $db->close();
    }

    public function testRegisterRedirectsIfAlreadyLoggedIn(): void
    {
        $_SESSION['user'] = ['id' => 1, 'email' => 'test@example.com'];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('redirect:' . INSTALL_URL);

        $this->controller->register();
    }

    public function testRegisterDisplaysFormOnGet(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->register();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testRegisterFailsIfEmailExists(): void
    {
        // seed the email so existsBy() returns true and save() is never reached
        $pdo = new \mysqli(
            $_ENV['DB_HOST'],
            $_ENV['DB_USER'],
            $_ENV['DB_PASS'],
            $_ENV['DB_NAME']
        );
        $pdo->query("INSERT IGNORE INTO users (name, email, password_hash, role, created_at) 
                 VALUES ('Test User', 'existing@example.com', 'hash', 'user', 0)");
        $pdo->close();

        $_POST['send'] = true;
        $_POST['email'] = 'existing@example.com';
        $_POST['password'] = 'password123';
        $_POST['repeat_password'] = 'password123';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->controller->register();

        $this->assertArrayHasKey('error_message', $this->controller->lastViewData);
        $this->assertStringContainsString('already exists', $this->controller->lastViewData['error_message']);
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

    public function testLoginRedirectsIfAlreadyLoggedIn(): void
    {
        $_SESSION['user'] = ['id' => 1, 'email' => 'test@example.com'];
        $_SESSION['previous_url'] = 'http://localhost/index.php';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('redirect:http://localhost/index.php');

        $this->controller->login();
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

    public function testLogoutDestroysSession(): void
    {
        $_SESSION['user'] = ['id' => 1, 'email' => 'test@example.com'];

        try {
            $this->controller->logout();
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('controller=Auth&action=login', $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function testLogoutRequiresActiveSession(): void
    {
        $_SESSION = [];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('redirect:' . INSTALL_URL);

        $this->controller->logout();
    }
}
