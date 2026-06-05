<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\CourierLocation;

class CourierLocationTest extends TestCase {

    private CourierLocation $courierLocationModel;

    protected function setUp(): void {
        parent::setUp();
        $this->courierLocationModel = new CourierLocation();
    }

    protected function tearDown(): void {
        parent::tearDown();
        unset($this->courierLocationModel);
    }

    public function testCourierLocationModelHasCorrectTableName(): void {
        $this->assertEquals('courier_locations', $this->courierLocationModel->table);
    }

    public function testCourierLocationModelHasCorrectPrimaryKey(): void {
        $this->assertEquals('id', $this->courierLocationModel->primaryKey);
    }

    public function testCourierLocationModelHasCorrectSchema(): void {
        $this->assertIsArray($this->courierLocationModel->schema);
        $this->assertNotEmpty($this->courierLocationModel->schema);
    }

    public function testCourierLocationSchemaContainsIdField(): void {
        $hasIdField = false;
        foreach ($this->courierLocationModel->schema as $field) {
            if ($field['name'] === 'id' && $field['type'] === 'int') {
                $hasIdField = true;
                break;
            }
        }
        $this->assertTrue($hasIdField);
    }

    public function testCourierLocationSchemaContainsUserIdField(): void {
        $hasUserIdField = false;
        foreach ($this->courierLocationModel->schema as $field) {
            if ($field['name'] === 'user_id' && $field['type'] === 'int') {
                $hasUserIdField = true;
                break;
            }
        }
        $this->assertTrue($hasUserIdField);
    }

    public function testCourierLocationSchemaContainsLatitudeField(): void {
        $hasLatitudeField = false;
        foreach ($this->courierLocationModel->schema as $field) {
            if ($field['name'] === 'latitude' && $field['type'] === 'decimal') {
                $hasLatitudeField = true;
                break;
            }
        }
        $this->assertTrue($hasLatitudeField);
    }

    public function testCourierLocationSchemaContainsLongitudeField(): void {
        $hasLongitudeField = false;
        foreach ($this->courierLocationModel->schema as $field) {
            if ($field['name'] === 'longitude' && $field['type'] === 'decimal') {
                $hasLongitudeField = true;
                break;
            }
        }
        $this->assertTrue($hasLongitudeField);
    }

    public function testCourierLocationSchemaContainsTimestampField(): void {
        $hasTimestampField = false;
        foreach ($this->courierLocationModel->schema as $field) {
            if ($field['name'] === 'timestamp' && $field['type'] === 'bigint') {
                $hasTimestampField = true;
                break;
            }
        }
        $this->assertTrue($hasTimestampField);
    }

    public function testGetLatestLocationMethodExists(): void {
        $this->assertTrue(method_exists($this->courierLocationModel, 'getLatestLocation'));
    }
}
