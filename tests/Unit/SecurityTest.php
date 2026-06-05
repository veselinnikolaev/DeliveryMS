<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Core\Security;

class SecurityTest extends TestCase {

    private Security $security;

    protected function setUp(): void {
        parent::setUp();
        $this->security = new Security();
    }

    protected function tearDown(): void {
        parent::tearDown();
        unset($this->security);
    }

    public function testSanitize(): void {
        $input = '<script>alert("xss")</script>Hello World';
        $sanitized = Security::sanitize($input);
        
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringContainsString('Hello World', $sanitized);
        $this->assertStringContainsString('&lt;', $sanitized);
    }

    public function testSanitizeArray(): void {
        $input = [
            'name' => '<script>alert("xss")</script>John',
            'email' => 'john@example.com'
        ];
        $sanitized = Security::sanitize($input);
        
        $this->assertArrayHasKey('name', $sanitized);
        $this->assertArrayHasKey('email', $sanitized);
        $this->assertStringNotContainsString('<script>', $sanitized['name']);
        $this->assertEquals('john@example.com', $sanitized['email']);
    }

    public function testGenerateCsrfToken(): void {
        $token = Security::generateCsrfToken();
        
        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    public function testValidateCsrfToken(): void {
        $token = Security::generateCsrfToken();
        
        $this->assertTrue(Security::validateCsrfToken($token));
        $this->assertFalse(Security::validateCsrfToken('invalid_token_12345678901234567890'));
    }

    public function testValidateEmail(): void {
        $this->assertTrue(Security::validateEmail('test@example.com'));
        $this->assertTrue(Security::validateEmail('user.name+tag@domain.co.uk'));
        $this->assertFalse(Security::validateEmail('invalid-email'));
        $this->assertFalse(Security::validateEmail('test@'));
        $this->assertFalse(Security::validateEmail('@example.com'));
    }

    public function testValidateUrl(): void {
        $this->assertTrue(Security::validateUrl('https://example.com'));
        $this->assertTrue(Security::validateUrl('http://example.com/path'));
        $this->assertFalse(Security::validateUrl('not-a-url'));
        $this->assertFalse(Security::validateUrl('javascript:alert(1)'));
    }

    public function testInt(): void {
        $this->assertEquals(42, Security::int('42'));
        $this->assertEquals(42, Security::int(42));
        $this->assertEquals(0, Security::int('not-a-number'));
        $this->assertEquals(99, Security::int('not-a-number', 99));
    }

    public function testFloat(): void {
        $this->assertEquals(3.14, Security::float('3.14'));
        $this->assertEquals(3.14, Security::float(3.14));
        $this->assertEquals(0.0, Security::float('not-a-number'));
        $this->assertEquals(2.5, Security::float('not-a-number', 2.5));
    }
}
