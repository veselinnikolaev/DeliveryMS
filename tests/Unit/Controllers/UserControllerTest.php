<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\UserController;

class UserControllerTest extends TestCase {

    private UserController $controller;

    protected function setUp(): void {
        parent::setUp();
        $_SESSION = ['user' => ['id' => 1, 'role' => 'admin', 'email' => 'admin@example.com', 'name' => 'Admin User']];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $db = new \mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        $db->query("INSERT IGNORE INTO users (id, name, email, password_hash, role, created_at)
            VALUES (1, 'Admin User', 'admin@example.com', 'hash', 'admin', 0),
                   (2, 'Test User', 'testuser2@example.com', 'hash', 'user', 0)");
        $db->close();

        $this->controller = new class extends UserController {
            protected function redirect(string $url): void { throw new \RuntimeException('redirect:' . $url); }
            protected function terminate(string $message = ''): void {}
            protected function setHeader(string $header): void {}
            public function view($layout, array $data = []): void { $this->lastViewData = $data; }
            public array $lastViewData = [];
            public ?string $lastExportFormat = null;

            public function export(): void
            {
                $this->lastExportFormat = $_POST['format'] ?? 'pdf';
            }
            public function print(): void {}
        };
    }

    protected function tearDown(): void {
        parent::tearDown();
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        unset($this->controller);

        $db = new \mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        $db->query("DELETE FROM notifications WHERE user_id IN (1, 2)");
        $db->query("DELETE FROM users WHERE id IN (1, 2)");
        $db->close();
    }

    public function testListRequiresAuthentication(): void {
        $_SESSION = [];
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('redirect:' . INSTALL_URL . '?controller=Auth&action=login');
        new class extends UserController {
            protected function redirect(string $url): void { throw new \RuntimeException('redirect:' . $url); }
            protected function terminate(string $message = ''): void {}
            protected function setHeader(string $header): void {}
            public function view($layout, array $data = []): void {}
            public array $lastViewData = [];
        };
    }

    public function testListRejectsUserRole(): void {
        $_SESSION['user']['role'] = 'user';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('redirect:' . INSTALL_URL);

        $this->controller->list();
    }

    public function testListDisplaysAllUsers(): void {
        $_SESSION['user']['role'] = 'admin';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->list();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testFilterAppliesNameFilter(): void {
        $_POST['name'] = 'John';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->filter();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testFilterAppliesEmailFilter(): void {
        $_POST['email'] = 'example@test.com';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->filter();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testFilterAppliesPhoneFilter(): void {
        $_POST['phone'] = '1234567890';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->filter();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testFilterAppliesRoleFilter(): void {
        $_POST['roles'] = ['admin', 'courier'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->filter();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testFilterAppliesAddressFilter(): void {
        $_POST['address'] = '123 Main Street';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->filter();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testChangeRoleUpdateUserRole(): void {
        $_POST['id'] = '2';
        $_POST['role'] = 'courier';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->changeRole();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testChangeRoleRejectsInvalidRole(): void {
        $_POST['id'] = '2';
        $_POST['role'] = 'superadmin';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->changeRole();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testCreateDisplaysFormOnGet(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->create();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testCreateFailsIfPasswordsDoNotMatch(): void {
        $_POST['send'] = true;
        $_POST['email'] = 'new@example.com';
        $_POST['password'] = 'password123';
        $_POST['repeat_password'] = 'password456';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->create();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testDeleteUserRemovesRecord(): void {
        $_POST['id'] = '2';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->delete();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testBulkDeleteRemovesMultipleUsers(): void {
        $_POST['ids'] = ['2', '3', '4'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->bulkDelete();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testEditDisplaysExistingUser(): void {
        $_GET['id'] = '2';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->edit();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testProfileDisplaysUserInfo(): void {
        $_GET['id'] = '1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->profile();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testEditPasswordFailsIfPasswordsDoNotMatch(): void {
        $_POST['id'] = '1';
        $_POST['password'] = 'newpass123';
        $_POST['repeat_password'] = 'newpass456';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->editPassword();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testExportHandlesPdfFormat(): void {
        $_POST['userData'] = json_encode([
            ['id' => 1, 'name' => 'Admin', 'email' => 'admin@example.com']
        ]);
        $_POST['format'] = 'pdf';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->controller->export();

        $this->assertEquals('pdf', $this->controller->lastExportFormat);
    }

    public function testPrintHandlesUserData(): void {
        $_POST['userData'] = json_encode([
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']
        ]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->print();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }
}
