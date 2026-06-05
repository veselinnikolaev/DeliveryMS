<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\User;

class UserTest extends TestCase {

    private User $userModel;

    protected function setUp(): void {
        parent::setUp();
        $this->userModel = new User();
    }

    protected function tearDown(): void {
        parent::tearDown();
        unset($this->userModel);
    }

    public function testUserModelHasCorrectTableName(): void {
        $this->assertEquals('users', $this->userModel->table);
    }

    public function testUserModelHasCorrectPrimaryKey(): void {
        $this->assertEquals('id', $this->userModel->primaryKey);
    }

    public function testUserModelHasCorrectSchema(): void {
        $this->assertIsArray($this->userModel->schema);
        $this->assertNotEmpty($this->userModel->schema);
    }

    public function testUserSchemaContainsIdField(): void {
        $hasIdField = false;
        foreach ($this->userModel->schema as $field) {
            if ($field['name'] === 'id' && $field['type'] === 'int') {
                $hasIdField = true;
                break;
            }
        }
        $this->assertTrue($hasIdField);
    }

    public function testUserSchemaContainsEmailField(): void {
        $hasEmailField = false;
        foreach ($this->userModel->schema as $field) {
            if ($field['name'] === 'email' && $field['type'] === 'varchar') {
                $hasEmailField = true;
                break;
            }
        }
        $this->assertTrue($hasEmailField);
    }

    public function testUserSchemaContainsPasswordHashField(): void {
        $hasPasswordField = false;
        foreach ($this->userModel->schema as $field) {
            if ($field['name'] === 'password_hash' && $field['type'] === 'varchar') {
                $hasPasswordField = true;
                break;
            }
        }
        $this->assertTrue($hasPasswordField);
    }

    public function testUserSchemaContainsRoleField(): void {
        $hasRoleField = false;
        foreach ($this->userModel->schema as $field) {
            if ($field['name'] === 'role' && $field['type'] === 'varchar') {
                $hasRoleField = true;
                break;
            }
        }
        $this->assertTrue($hasRoleField);
    }

    public function testUserSchemaContainsPhoneNumberField(): void {
        $hasPhoneField = false;
        foreach ($this->userModel->schema as $field) {
            if ($field['name'] === 'phone_number' && $field['type'] === 'varchar') {
                $hasPhoneField = true;
                break;
            }
        }
        $this->assertTrue($hasPhoneField);
    }

    public function testUserSchemaContainsAddressField(): void {
        $hasAddressField = false;
        foreach ($this->userModel->schema as $field) {
            if ($field['name'] === 'address' && $field['type'] === 'varchar') {
                $hasAddressField = true;
                break;
            }
        }
        $this->assertTrue($hasAddressField);
    }

    public function testUserSchemaContainsCountryField(): void {
        $hasCountryField = false;
        foreach ($this->userModel->schema as $field) {
            if ($field['name'] === 'country' && $field['type'] === 'varchar') {
                $hasCountryField = true;
                break;
            }
        }
        $this->assertTrue($hasCountryField);
    }

    public function testUserSchemaContainsRegionField(): void {
        $hasRegionField = false;
        foreach ($this->userModel->schema as $field) {
            if ($field['name'] === 'region' && $field['type'] === 'varchar') {
                $hasRegionField = true;
                break;
            }
        }
        $this->assertTrue($hasRegionField);
    }

    public function testUserSchemaContainsPhotoPathField(): void {
        $hasPhotoField = false;
        foreach ($this->userModel->schema as $field) {
            if ($field['name'] === 'photo_path' && $field['type'] === 'varchar') {
                $hasPhotoField = true;
                break;
            }
        }
        $this->assertTrue($hasPhotoField);
    }

    public function testUserSchemaContainsCreatedAtField(): void {
        $hasCreatedAtField = false;
        foreach ($this->userModel->schema as $field) {
            if ($field['name'] === 'created_at' && $field['type'] === 'timestamp') {
                $hasCreatedAtField = true;
                break;
            }
        }
        $this->assertTrue($hasCreatedAtField);
    }
}
