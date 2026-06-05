<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Notification;

class NotificationTest extends TestCase {

    private Notification $notificationModel;

    protected function setUp(): void {
        parent::setUp();
        $this->notificationModel = new Notification();
    }

    protected function tearDown(): void {
        parent::tearDown();
        unset($this->notificationModel);
    }

    public function testNotificationModelHasCorrectTableName(): void {
        $this->assertEquals('notifications', $this->notificationModel->table);
    }

    public function testNotificationModelHasCorrectPrimaryKey(): void {
        $this->assertEquals('id', $this->notificationModel->primaryKey);
    }

    public function testNotificationModelHasCorrectSchema(): void {
        $this->assertIsArray($this->notificationModel->schema);
        $this->assertNotEmpty($this->notificationModel->schema);
    }

    public function testNotificationSchemaContainsIdField(): void {
        $hasIdField = false;
        foreach ($this->notificationModel->schema as $field) {
            if ($field['name'] === 'id' && $field['type'] === 'int') {
                $hasIdField = true;
                break;
            }
        }
        $this->assertTrue($hasIdField);
    }

    public function testNotificationSchemaContainsUserIdField(): void {
        $hasUserIdField = false;
        foreach ($this->notificationModel->schema as $field) {
            if ($field['name'] === 'user_id' && $field['type'] === 'int') {
                $hasUserIdField = true;
                break;
            }
        }
        $this->assertTrue($hasUserIdField);
    }

    public function testNotificationSchemaContainsMessageField(): void {
        $hasMessageField = false;
        foreach ($this->notificationModel->schema as $field) {
            if ($field['name'] === 'message' && $field['type'] === 'text') {
                $hasMessageField = true;
                break;
            }
        }
        $this->assertTrue($hasMessageField);
    }

    public function testNotificationSchemaContainsLinkField(): void {
        $hasLinkField = false;
        foreach ($this->notificationModel->schema as $field) {
            if ($field['name'] === 'link' && $field['type'] === 'varchar') {
                $hasLinkField = true;
                break;
            }
        }
        $this->assertTrue($hasLinkField);
    }

    public function testNotificationSchemaContainsIsSeenField(): void {
        $hasIsSeenField = false;
        foreach ($this->notificationModel->schema as $field) {
            if ($field['name'] === 'is_seen' && $field['type'] === 'int') {
                $hasIsSeenField = true;
                break;
            }
        }
        $this->assertTrue($hasIsSeenField);
    }

    public function testNotificationSchemaContainsCreatedAtField(): void {
        $hasCreatedAtField = false;
        foreach ($this->notificationModel->schema as $field) {
            if ($field['name'] === 'created_at' && $field['type'] === 'varchar') {
                $hasCreatedAtField = true;
                break;
            }
        }
        $this->assertTrue($hasCreatedAtField);
    }

    public function testNotificationSchemaIsSeenHasDefaultValue(): void {
        $isSeenField = null;
        foreach ($this->notificationModel->schema as $field) {
            if ($field['name'] === 'is_seen') {
                $isSeenField = $field;
                break;
            }
        }
        $this->assertNotNull($isSeenField);
        $this->assertEquals(0, $isSeenField['default']);
    }
}
