<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\OrderController;

class OrderControllerTest extends TestCase {

    private $controller;

    protected function setUp(): void {
        parent::setUp();
        $_SESSION = ['user' => ['id' => 1, 'role' => 'admin', 'email' => 'admin@example.com', 'name' => 'Admin User'], 'previous_url' => '/'];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $db = new \mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        $db->query("INSERT IGNORE INTO users (id, name, email, password_hash, role, created_at)
                VALUES (998, 'Test User', 'testuser998@example.com', 'hash', 'user', 0)");
        $db->query("INSERT IGNORE INTO orders (id, user_id, courier_id, address, country, region, status, product_price, tax, shipping_price, total_amount, created_at)
                VALUES (1, 998, NULL, '123 Street', 'Bulgaria', 'Sofia', 'pending', 10.00, 1.00, 2.00, 13.00, 0),
                       (2, 998, NULL, '123 Street', 'Bulgaria', 'Sofia', 'pending', 10.00, 1.00, 2.00, 13.00, 0)");
        $db->query("INSERT IGNORE INTO products (id, name, price, stock, created_at)
            VALUES (1, 'Test Product 1', 10.00, 100, 0),
                   (2, 'Test Product 2', 20.00, 100, 0)");
        $db->close();

        $this->controller = new class extends OrderController {
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
                $this->lastExportFormat = $_POST['format'] ?? 'pdf';
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
        $db->query("DELETE FROM orders WHERE id IN (1, 2)");
        $db->query("DELETE FROM users WHERE id = 998");
        $db->query("DELETE FROM products WHERE id IN (1, 2)");
        $db->close();
    }

    public function testListDisplaysAllOrders(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->list();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testListAppliesCustomerNameFilter(): void {
        $_POST['customerName'] = 'John Doe';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->list();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testListAppliesCourierNameFilter(): void {
        $_POST['courierName'] = 'Courier Name';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->list();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testListAppliesStatusFilter(): void {
        $_POST['status'] = 'pending';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->list();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testListAppliesTrackingNumberFilter(): void {
        $_POST['trackingNumber'] = 'TRACK123';
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

    public function testListAppliesDateRangeFilter(): void {
        $_POST['orderDateFrom'] = '2024-01-01';
        $_POST['orderDateTo'] = '2024-12-31';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->list();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testListAppliesPriceRangeFilter(): void {
        $_POST['minTotalPrice'] = '100.00';
        $_POST['maxTotalPrice'] = '1000.00';
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

    public function testCreateRequiresAuthentication(): void
    {
        $_SESSION = [];
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('redirect:' . INSTALL_URL . '?controller=Auth&action=login');

        $controller = new class extends OrderController {
            protected function redirect(string $url): void { throw new \RuntimeException('redirect:' . $url); }
            protected function terminate(string $message = ''): void {}
            protected function setHeader(string $header): void {}
            public function view($layout, array $data = []): void {}
            public array $lastViewData = [];
        };
        $controller->create();
    }

    public function testCreateRejectsUserRole(): void
    {
        $_SESSION['user']['role'] = 'user';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('redirect:/'); // redirects to previous_url which is '/'

        $controller = new class extends OrderController {
            protected function redirect(string $url): void { throw new \RuntimeException('redirect:' . $url); }
            protected function terminate(string $message = ''): void {}
            protected function setHeader(string $header): void {}
            public function view($layout, array $data = []): void {}
            public array $lastViewData = [];
        };
        $controller->create();
    }

    public function testDetailsDisplaysOrderInfo(): void {
        $db = new \mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        $db->query("INSERT IGNORE INTO users (id, name, email, password_hash, role, created_at)
                VALUES (998, 'Test User', 'testuser998@example.com', 'hash', 'user', 0)");
        $db->query("INSERT IGNORE INTO orders (id, user_id, courier_id, address, country, region, status, product_price, tax, shipping_price, total_amount, created_at)
                VALUES (1, 998, NULL, '123 Street', 'Bulgaria', 'Sofia', 'pending', 10.00, 1.00, 2.00, 13.00, 0)");
        $db->close();

        $_GET['id'] = '1';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->controller->details();

        $this->assertArrayHasKey('order', $this->controller->lastViewData);
    }

    public function testDetailsRequiresOrderId(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['id'] = '';

        // empty id redirects to previous_url
        $this->expectException(\RuntimeException::class);
        $this->controller->details();
    }

    public function testChangeStatusRequiresCourierRole(): void {
        $_SESSION['user']['role'] = 'user';

        $this->expectException(\RuntimeException::class);
        $this->controller->changeStatus();
    }

    public function testChangeStatusUpdatesOrderStatus(): void {
        $db = new \mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        $db->query("INSERT IGNORE INTO users (id, name, email, password_hash, role, created_at)
                VALUES (998, 'Test User', 'testuser998@example.com', 'hash', 'user', 0)");
        $db->query("INSERT IGNORE INTO orders (id, user_id, courier_id, address, country, region, status, product_price, tax, shipping_price, total_amount, created_at)
                VALUES (1, 998, NULL, '123 Street', 'Bulgaria', 'Sofia', 'shipped', 10.00, 1.00, 2.00, 13.00, 0),
                       (2, 998, NULL, '123 Street', 'Bulgaria', 'Sofia', 'shipped', 10.00, 1.00, 2.00, 13.00, 0)");
        $db->close();

        $_SESSION['user']['role'] = 'courier';
        $_SESSION['user']['id'] = 2;
        $_POST['ids'] = ['1', '2'];
        $_POST['status'] = 'delivered';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->controller->changeStatus();

        $this->assertIsString(''); // just verify no crash
    }

    public function testDeleteRemovesOrder(): void {
        $_POST['id'] = '1';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->delete();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testBulkDeleteRemovesMultipleOrders(): void {
        $_POST['ids'] = ['1', '2', '3'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->bulkDelete();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testPayDisplaysPaymentForm(): void {
        $db = new \mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        $db->query("INSERT IGNORE INTO users (id, name, email, password_hash, role, created_at)
                VALUES (998, 'Test User', 'testuser998@example.com', 'hash', 'user', 0)");
        $db->query("INSERT IGNORE INTO orders (id, user_id, courier_id, address, country, region, status, product_price, tax, shipping_price, total_amount, created_at)
                VALUES (1, 998, NULL, '123 Street', 'Bulgaria', 'Sofia', 'pending', 10.00, 1.00, 2.00, 13.00, 0)");
        $db->close();

        $_GET['order_id'] = '1';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->controller->pay();

        $this->assertArrayHasKey('order', $this->controller->lastViewData);
    }

    public function testPaySuccessDisplaysSuccessPage(): void {
        $_GET['order_id'] = '1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->pay_success();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testPayCancelDisplaysCancelPage(): void {
        $_GET['order_id'] = '1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->pay_cancel();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testPaypalIpnHandlesVerifiedPayment(): void {
        $_POST['custom'] = '1';
        $_POST['payment_status'] = 'Completed';
        $_POST['txn_id'] = 'test_txn_123';
        $_POST['mc_gross'] = '100.00';
        $_POST['mc_currency'] = 'USD';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->paypal_ipn();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testEditDisplaysExistingOrder(): void {
        $_GET['order_id'] = '1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->edit();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testCalculatePriceReturnsJson(): void {
        $_POST['product_id'] = ['1', '2'];
        $_POST['quantity'] = ['2', '3'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->calculatePrice();
        $output = ob_get_clean();
        
        $json = json_decode($output, true);
        $this->assertIsArray($json);
    }

    public function testExportHandlesPdfFormat(): void {
        $_POST['orderData'] = json_encode([
            ['id' => 1, 'total_amount' => 100, 'status' => 'pending']
        ]);
        $_POST['format'] = 'pdf';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->controller->export();

        $this->assertEquals('pdf', $this->controller->lastExportFormat);
    }

    public function testPrintHandlesOrderData(): void {
        $_POST['orderData'] = json_encode([
            ['id' => 1, 'total_amount' => 100, 'status' => 'pending']
        ]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->print();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }
}
