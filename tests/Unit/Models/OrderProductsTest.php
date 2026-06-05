<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\OrderProducts;

class OrderProductsTest extends TestCase {

    private OrderProducts $orderProductsModel;

    protected function setUp(): void {
        parent::setUp();
        $this->orderProductsModel = new OrderProducts();
    }

    protected function tearDown(): void {
        parent::tearDown();
        unset($this->orderProductsModel);
    }

    public function testOrderProductsModelHasCorrectTableName(): void {
        $this->assertEquals('order_products', $this->orderProductsModel->table);
    }

    public function testOrderProductsModelHasCorrectPrimaryKey(): void {
        $this->assertEquals('id', $this->orderProductsModel->primaryKey);
    }

    public function testOrderProductsModelHasCorrectSchema(): void {
        $this->assertIsArray($this->orderProductsModel->schema);
        $this->assertNotEmpty($this->orderProductsModel->schema);
    }

    public function testOrderProductsSchemaContainsIdField(): void {
        $hasIdField = false;
        foreach ($this->orderProductsModel->schema as $field) {
            if ($field['name'] === 'id' && $field['type'] === 'int') {
                $hasIdField = true;
                break;
            }
        }
        $this->assertTrue($hasIdField);
    }

    public function testOrderProductsSchemaContainsOrderIdField(): void {
        $hasOrderIdField = false;
        foreach ($this->orderProductsModel->schema as $field) {
            if ($field['name'] === 'order_id' && $field['type'] === 'int') {
                $hasOrderIdField = true;
                break;
            }
        }
        $this->assertTrue($hasOrderIdField);
    }

    public function testOrderProductsSchemaContainsProductIdField(): void {
        $hasProductIdField = false;
        foreach ($this->orderProductsModel->schema as $field) {
            if ($field['name'] === 'product_id' && $field['type'] === 'int') {
                $hasProductIdField = true;
                break;
            }
        }
        $this->assertTrue($hasProductIdField);
    }

    public function testOrderProductsSchemaContainsQuantityField(): void {
        $hasQuantityField = false;
        foreach ($this->orderProductsModel->schema as $field) {
            if ($field['name'] === 'quantity' && $field['type'] === 'int') {
                $hasQuantityField = true;
                break;
            }
        }
        $this->assertTrue($hasQuantityField);
    }

    public function testOrderProductsSchemaContainsPriceField(): void {
        $hasPriceField = false;
        foreach ($this->orderProductsModel->schema as $field) {
            if ($field['name'] === 'price' && $field['type'] === 'decimal') {
                $hasPriceField = true;
                break;
            }
        }
        $this->assertTrue($hasPriceField);
    }

    public function testOrderProductsSchemaContainsSubtotalField(): void {
        $hasSubtotalField = false;
        foreach ($this->orderProductsModel->schema as $field) {
            if ($field['name'] === 'subtotal' && $field['type'] === 'decimal') {
                $hasSubtotalField = true;
                break;
            }
        }
        $this->assertTrue($hasSubtotalField);
    }

    public function testOrderProductsSchemaQuantityHasDefaultValue(): void {
        $quantityField = null;
        foreach ($this->orderProductsModel->schema as $field) {
            if ($field['name'] === 'quantity') {
                $quantityField = $field;
                break;
            }
        }
        $this->assertNotNull($quantityField);
        $this->assertEquals(1, $quantityField['default']);
    }
}
