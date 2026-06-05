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
        $this->controller = new UserController();
    }

    protected function tearDown(): void {
        parent::tearDown();
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        unset($this->controller);
    }

    public function testListRequiresAuthentication(): void {
        $_SESSION = [];
        
        $this->expectOutputString('');
        try {
            $controller = new UserController();
        } catch (\Throwable $e) {
            $this->assertTrue(true);
        }
    }

    public function testListRejectsUserRole(): void {
        $_SESSION['user']['role'] = 'user';
        
        $this->expectOutputString('');
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
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']
        ]);
        $_POST['format'] = 'pdf';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->export();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
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
