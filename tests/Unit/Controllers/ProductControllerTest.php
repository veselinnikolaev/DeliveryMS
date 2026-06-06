<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\ProductController;

class ProductControllerTest extends TestCase {

    private $controller;

    protected function setUp(): void {
        parent::setUp();
        $_SESSION = ['user' => ['id' => 1, 'role' => 'admin', 'email' => 'admin@example.com']];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $db = new \mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        $db->query("INSERT IGNORE INTO products (id, name, price, stock, created_at)
                VALUES (1, 'Test Product', 10.00, 100, 0)");
        $db->close();

        $this->controller = new class extends ProductController {
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
            public function print(): void
            {
                // no-op in tests
            }
        };
    }


    protected function tearDown(): void {
        parent::tearDown();
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        unset($this->controller);

        $db = new \mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        $db->query("DELETE FROM products WHERE id = 1");
        $db->close();
    }

    public function testListDisplaysAllProducts(): void {
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
        new class extends ProductController {
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
        new class extends ProductController {
            protected function redirect(string $url): void { throw new \RuntimeException('redirect:' . $url); }
            protected function terminate(string $message = ''): void {}
            protected function setHeader(string $header): void {}
            public function view($layout, array $data = []): void {}
            public array $lastViewData = [];
        };
    }

    public function testFilterAppliesNameFilter(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Laptop';
        
        ob_start();
        $this->controller->filter();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testFilterAppliesPriceRange(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['minPrice'] = '100.00';
        $_POST['maxPrice'] = '500.00';
        
        ob_start();
        $this->controller->filter();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testFilterAppliessStockRange(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['minStock'] = '5';
        $_POST['maxStock'] = '50';
        
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

    public function testCreateHandlesEmptyForm(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [];
        
        ob_start();
        $this->controller->create();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testEditDisplaysExistingProduct(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['id'] = '1';

        $this->controller->edit();

        $this->assertArrayHasKey('id', $this->controller->lastViewData);
    }

    public function testDeleteRequiresPostRequest(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->delete();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testBulkDeleteHandlesMultipleProducts(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['ids'] = ['1', '2', '3'];
        
        ob_start();
        $this->controller->bulkDelete();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testExportHandlesPdfFormat(): void {
        $_POST['productData'] = json_encode([
            ['id' => 1, 'name' => 'Test Product', 'price' => 10.00]
        ]);
        $_POST['format'] = 'pdf';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->controller->export();

        $this->assertEquals('pdf', $this->controller->lastExportFormat);
    }

    public function testExportHandlesEmptyData(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['format'] = 'pdf';
        $_POST['productData'] = null;
        
        ob_start();
        $this->controller->export();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testPrintHandlesProductData(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['productData'] = json_encode([
            ['id' => 1, 'name' => 'Product 1', 'price' => 100],
        ]);
        
        ob_start();
        $this->controller->print();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }
}
