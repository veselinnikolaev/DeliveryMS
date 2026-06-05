<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Product;

class ProductTest extends TestCase {

    private Product $productModel;

    protected function setUp(): void {
        parent::setUp();
        $this->productModel = new Product();
    }

    protected function tearDown(): void {
        parent::tearDown();
        unset($this->productModel);
    }

    public function testProductModelHasCorrectTableName(): void {
        $this->assertEquals('products', $this->productModel->table);
    }

    public function testProductModelHasCorrectPrimaryKey(): void {
        $this->assertEquals('id', $this->productModel->primaryKey);
    }

    public function testProductModelHasCorrectSchema(): void {
        $this->assertIsArray($this->productModel->schema);
        $this->assertNotEmpty($this->productModel->schema);
    }

    public function testProductSchemaContainsIdField(): void {
        $hasIdField = false;
        foreach ($this->productModel->schema as $field) {
            if ($field['name'] === 'id' && $field['type'] === 'int') {
                $hasIdField = true;
                break;
            }
        }
        $this->assertTrue($hasIdField);
    }

    public function testProductSchemaContainsNameField(): void {
        $hasNameField = false;
        foreach ($this->productModel->schema as $field) {
            if ($field['name'] === 'name' && $field['type'] === 'varchar') {
                $hasNameField = true;
                break;
            }
        }
        $this->assertTrue($hasNameField);
    }

    public function testProductSchemaContainsDescriptionField(): void {
        $hasDescriptionField = false;
        foreach ($this->productModel->schema as $field) {
            if ($field['name'] === 'description' && $field['type'] === 'text') {
                $hasDescriptionField = true;
                break;
            }
        }
        $this->assertTrue($hasDescriptionField);
    }

    public function testProductSchemaContainsPriceField(): void {
        $hasPriceField = false;
        foreach ($this->productModel->schema as $field) {
            if ($field['name'] === 'price' && $field['type'] === 'decimal') {
                $hasPriceField = true;
                break;
            }
        }
        $this->assertTrue($hasPriceField);
    }

    public function testProductSchemaContainsStockField(): void {
        $hasStockField = false;
        foreach ($this->productModel->schema as $field) {
            if ($field['name'] === 'stock' && $field['type'] === 'int') {
                $hasStockField = true;
                break;
            }
        }
        $this->assertTrue($hasStockField);
    }

    public function testProductSchemaContainsCreatedAtField(): void {
        $hasCreatedAtField = false;
        foreach ($this->productModel->schema as $field) {
            if ($field['name'] === 'created_at' && $field['type'] === 'timestamp') {
                $hasCreatedAtField = true;
                break;
            }
        }
        $this->assertTrue($hasCreatedAtField);
    }

    public function testProductSchemaStockHasDefaultValue(): void {
        $stockField = null;
        foreach ($this->productModel->schema as $field) {
            if ($field['name'] === 'stock') {
                $stockField = $field;
                break;
            }
        }
        $this->assertNotNull($stockField);
        $this->assertEquals(0, $stockField['default']);
    }
}
