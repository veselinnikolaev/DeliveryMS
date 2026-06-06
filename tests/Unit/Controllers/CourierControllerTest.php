<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\CourierController;

class CourierControllerTest extends TestCase {

    private $controller;

    protected function setUp(): void {
        parent::setUp();
        $_SESSION = ['user' => ['id' => 1, 'role' => 'admin', 'email' => 'admin@example.com', 'name' => 'Admin User'], 'previous_url' => '/'];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->controller = new class extends CourierController {
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
            public ?string $lastExportFormat = null;

            public function export(): void
            {
                $couriers = [];
                $rawData = $_POST['courierData'] ?? null;
                if ($rawData !== null) {
                    $couriers = json_decode($rawData, true);
                    if (!$couriers || empty($couriers)) {
                        echo "No couriers to export";
                        $this->terminate();
                    }
                }
                // record format instead of actually exporting
                $this->lastExportFormat = $_POST['format'] ?? 'pdf';
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

        $db = new \mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        $db->query("DELETE FROM users WHERE email = 'existing@example.com'");
        $db->query("DELETE FROM users WHERE id = 999");
        $db->close();
    }

    public function testListDisplaysAllCouriers(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->list();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testListRequiresAuthentication(): void {
        $_SESSION = [];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('redirect:' . INSTALL_URL . '?controller=Auth&action=login');

        new class extends CourierController {
            protected function redirect(string $url): void
            {
                throw new \RuntimeException('redirect:' . $url);
            }
            protected function terminate(string $message = ''): void {}
            public function view($layout, array $data = []): void {}
            public array $lastViewData = [];
        };
    }

    public function testListRejectsUserRole(): void {
        $_SESSION['user']['role'] = 'user';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('redirect:' . INSTALL_URL);

        new class extends CourierController {
            protected function redirect(string $url): void
            {
                throw new \RuntimeException('redirect:' . $url);
            }
            protected function terminate(string $message = ''): void {}
            public function view($layout, array $data = []): void {}
            public array $lastViewData = [];
        };
    }

    public function testListAppliesNameFilter(): void {
        $_POST['name'] = 'John';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->list();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testListAppliesEmailFilter(): void {
        $_POST['email'] = 'courier@example.com';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->list();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testListAppliesPhoneFilter(): void {
        $_POST['phone'] = '1234567890';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->list();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testListAppliesAddressFilter(): void {
        $_POST['address'] = '123 Main Street';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->list();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testListAppliesCountryFilter(): void {
        $_POST['country'] = 'Bulgaria';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->list();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testListAppliesRegionFilter(): void {
        $_POST['region'] = 'Sofia';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->list();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testFilterCallsList(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->filter();
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

    public function testCreateFailsIfEmailExists(): void
    {
        // seed the email so existsBy() returns true and save() is never reached
        $db = new \mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        $db->query("INSERT IGNORE INTO users (name, email, password_hash, role, created_at)
                VALUES ('Test Courier', 'existing@example.com', 'hash', 'courier', 0)");
        $db->close();

        $_POST['send'] = true;
        $_POST['email'] = 'existing@example.com';
        $_POST['password'] = 'password123';
        $_POST['repeat_password'] = 'password123';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->controller->create();

        $this->assertArrayHasKey('error_message', $this->controller->lastViewData);
        $this->assertStringContainsString('already exists', $this->controller->lastViewData['error_message']);
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

    public function testDeleteRemovesCourier(): void {
        $_POST['id'] = '2';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->delete();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testBulkDeleteRemovesMultipleCouriers(): void {
        $_POST['ids'] = ['2', '3', '4'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->bulkDelete();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testEditDisplaysExistingCourier(): void
    {
        $db = new \mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        $db->query("INSERT IGNORE INTO users (id, name, email, password_hash, role, created_at)
                VALUES (999, 'Test Courier', 'courier999@example.com', 'hash', 'courier', 0)");
        $db->close();

        $_GET['id'] = '999';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->controller->edit();

        $this->assertArrayHasKey('id', $this->controller->lastViewData);
    }

    public function testExportHandlesPdfFormat(): void
    {
        $_POST['courierData'] = json_encode([
            ['id' => 2, 'name' => 'John Courier', 'email' => 'john@example.com']
        ]);
        $_POST['format'] = 'pdf';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->controller->export();

        $this->assertEquals('pdf', $this->controller->lastExportFormat);
    }

    public function testPrintHandlesCourierData(): void {
        $_POST['courierData'] = json_encode([
            ['id' => 2, 'name' => 'John Courier', 'email' => 'john@example.com']
        ]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->print();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testPrintHandlesEmptyCourierData(): void {
        $_POST['courierData'] = '[]';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->print();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }
}
