<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Order;

class OrderTest extends TestCase {

    private Order $orderModel;

    protected function setUp(): void {
        parent::setUp();
        $this->orderModel = new Order();
    }

    protected function tearDown(): void {
        parent::tearDown();
        unset($this->orderModel);
    }

    public function testOrderModelHasCorrectTableName(): void {
        $this->assertEquals('orders', $this->orderModel->table);
    }

    public function testOrderModelHasCorrectPrimaryKey(): void {
        $this->assertEquals('id', $this->orderModel->primaryKey);
    }

    public function testOrderModelHasCorrectSchema(): void {
        $this->assertIsArray($this->orderModel->schema);
        $this->assertNotEmpty($this->orderModel->schema);
    }

    public function testOrderSchemaContainsIdField(): void {
        $hasIdField = false;
        foreach ($this->orderModel->schema as $field) {
            if ($field['name'] === 'id' && $field['type'] === 'int') {
                $hasIdField = true;
                break;
            }
        }
        $this->assertTrue($hasIdField);
    }

    public function testOrderSchemaContainsUserIdField(): void {
        $hasUserIdField = false;
        foreach ($this->orderModel->schema as $field) {
            if ($field['name'] === 'user_id' && $field['type'] === 'int') {
                $hasUserIdField = true;
                break;
            }
        }
        $this->assertTrue($hasUserIdField);
    }

    public function testOrderSchemaContainsCourierIdField(): void {
        $hasCourierIdField = false;
        foreach ($this->orderModel->schema as $field) {
            if ($field['name'] === 'courier_id' && $field['type'] === 'int') {
                $hasCourierIdField = true;
                break;
            }
        }
        $this->assertTrue($hasCourierIdField);
    }

    public function testOrderSchemaContainsAddressField(): void {
        $hasAddressField = false;
        foreach ($this->orderModel->schema as $field) {
            if ($field['name'] === 'address' && $field['type'] === 'varchar') {
                $hasAddressField = true;
                break;
            }
        }
        $this->assertTrue($hasAddressField);
    }

    public function testOrderSchemaContainsCountryField(): void {
        $hasCountryField = false;
        foreach ($this->orderModel->schema as $field) {
            if ($field['name'] === 'country' && $field['type'] === 'varchar') {
                $hasCountryField = true;
                break;
            }
        }
        $this->assertTrue($hasCountryField);
    }

    public function testOrderSchemaContainsRegionField(): void {
        $hasRegionField = false;
        foreach ($this->orderModel->schema as $field) {
            if ($field['name'] === 'region' && $field['type'] === 'varchar') {
                $hasRegionField = true;
                break;
            }
        }
        $this->assertTrue($hasRegionField);
    }

    public function testOrderSchemaContainsStatusField(): void {
        $hasStatusField = false;
        foreach ($this->orderModel->schema as $field) {
            if ($field['name'] === 'status' && $field['type'] === 'varchar') {
                $hasStatusField = true;
                break;
            }
        }
        $this->assertTrue($hasStatusField);
    }

    public function testOrderSchemaContainsTotalAmountField(): void {
        $hasTotalField = false;
        foreach ($this->orderModel->schema as $field) {
            if ($field['name'] === 'total_amount' && $field['type'] === 'decimal') {
                $hasTotalField = true;
                break;
            }
        }
        $this->assertTrue($hasTotalField);
    }

    public function testOrderSchemaContainsTrackingNumberField(): void {
        $hasTrackingField = false;
        foreach ($this->orderModel->schema as $field) {
            if ($field['name'] === 'tracking_number' && $field['type'] === 'varchar') {
                $hasTrackingField = true;
                break;
            }
        }
        $this->assertTrue($hasTrackingField);
    }

    public function testOrderSchemaContainsTaxField(): void {
        $hasTaxField = false;
        foreach ($this->orderModel->schema as $field) {
            if ($field['name'] === 'tax' && $field['type'] === 'decimal') {
                $hasTaxField = true;
                break;
            }
        }
        $this->assertTrue($hasTaxField);
    }

    public function testOrderSchemaContainsShippingPriceField(): void {
        $hasShippingField = false;
        foreach ($this->orderModel->schema as $field) {
            if ($field['name'] === 'shipping_price' && $field['type'] === 'decimal') {
                $hasShippingField = true;
                break;
            }
        }
        $this->assertTrue($hasShippingField);
    }

    public function testOrderSchemaContainsDeliveryDateField(): void {
        $hasDeliveryField = false;
        foreach ($this->orderModel->schema as $field) {
            if ($field['name'] === 'delivery_date' && $field['type'] === 'varchar') {
                $hasDeliveryField = true;
                break;
            }
        }
        $this->assertTrue($hasDeliveryField);
    }
}
