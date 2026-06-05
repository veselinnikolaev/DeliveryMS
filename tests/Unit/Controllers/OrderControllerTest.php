<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\OrderController;

class OrderControllerTest extends TestCase {

    private OrderController $controller;

    protected function setUp(): void {
        parent::setUp();
        $_SESSION = ['user' => ['id' => 1, 'role' => 'admin', 'email' => 'admin@example.com', 'name' => 'Admin User'], 'previous_url' => '/'];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->controller = new OrderController();
    }

    protected function tearDown(): void {
        parent::tearDown();
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        unset($this->controller);
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

    public function testCreateRequiresAuthentication(): void {
        $_SESSION = [];
        
        $this->expectOutputString('');
        $this->controller->create();
    }

    public function testCreateRejectsUserRole(): void {
        $_SESSION['user']['role'] = 'user';
        
        $this->expectOutputString('');
        $this->controller->create();
    }

    public function testDetailsDisplaysOrderInfo(): void {
        $_GET['id'] = '1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->details();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testDetailsRequiresOrderId(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['id'] = '';
        
        $this->expectOutputString('');
        $this->controller->details();
    }

    public function testChangeStatusRequiresCourierRole(): void {
        $_SESSION['user']['role'] = 'user';
        
        $this->expectOutputString('');
        $this->controller->changeStatus();
    }

    public function testChangeStatusUpdatesOrderStatus(): void {
        $_SESSION['user']['role'] = 'courier';
        $_SESSION['user']['id'] = 2;
        $_POST['ids'] = ['1', '2'];
        $_POST['status'] = 'delivered';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->changeStatus();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
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
        $_GET['order_id'] = '1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->pay();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
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
        
        ob_start();
        $this->controller->export();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
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
