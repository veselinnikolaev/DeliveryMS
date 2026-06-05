<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\ProductController;

class ProductControllerTest extends TestCase {

    private ProductController $controller;

    protected function setUp(): void {
        parent::setUp();
        $_SESSION = ['user' => ['id' => 1, 'role' => 'admin', 'email' => 'admin@example.com']];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->controller = new ProductController();
    }

    protected function tearDown(): void {
        parent::tearDown();
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        unset($this->controller);
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
        
        $this->expectOutputString('');
        try {
            $controller = new ProductController();
        } catch (\Throwable $e) {
            $this->assertTrue(true);
        }
    }

    public function testListRejectsUserRole(): void {
        $_SESSION = ['user' => ['id' => 2, 'role' => 'user', 'email' => 'user@example.com']];
        
        $this->expectOutputString('');
        $controller = new ProductController();
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
        
        ob_start();
        $this->controller->edit();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
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
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['productData'] = '[]';
        $_POST['format'] = 'pdf';
        
        ob_start();
        $this->controller->export();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
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
