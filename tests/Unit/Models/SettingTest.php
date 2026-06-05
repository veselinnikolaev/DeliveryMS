<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Setting;

class SettingTest extends TestCase {

    private Setting $settingModel;

    protected function setUp(): void {
        parent::setUp();
        $this->settingModel = new Setting();
    }

    protected function tearDown(): void {
        parent::tearDown();
        unset($this->settingModel);
    }

    public function testSettingModelHasCorrectTableName(): void {
        $this->assertEquals('settings', $this->settingModel->table);
    }

    public function testSettingModelHasCorrectPrimaryKey(): void {
        $this->assertEquals('id', $this->settingModel->primaryKey);
    }

    public function testSettingModelHasCorrectSchema(): void {
        $this->assertIsArray($this->settingModel->schema);
        $this->assertNotEmpty($this->settingModel->schema);
    }

    public function testSettingSchemaContainsIdField(): void {
        $hasIdField = false;
        foreach ($this->settingModel->schema as $field) {
            if ($field['name'] === 'id' && $field['type'] === 'int') {
                $hasIdField = true;
                break;
            }
        }
        $this->assertTrue($hasIdField);
    }

    public function testSettingSchemaContainsKeyField(): void {
        $hasKeyField = false;
        foreach ($this->settingModel->schema as $field) {
            if ($field['name'] === 'key' && $field['type'] === 'varchar') {
                $hasKeyField = true;
                break;
            }
        }
        $this->assertTrue($hasKeyField);
    }

    public function testSettingSchemaContainsValueField(): void {
        $hasValueField = false;
        foreach ($this->settingModel->schema as $field) {
            if ($field['name'] === 'value' && $field['type'] === 'varchar') {
                $hasValueField = true;
                break;
            }
        }
        $this->assertTrue($hasValueField);
    }
}
