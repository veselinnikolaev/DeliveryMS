<?php

declare(strict_types=1);

namespace Tests\Core;

use Core\Security;
use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
{
    /**
     * Test sanitize method with string input
     */
    public function testSanitizeString(): void
    {
        $input = '<script>alert("xss")</script>';
        $sanitized = Security::sanitize($input);
        
        $this->assertNotEquals($input, $sanitized);
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringContainsString('&lt;script&gt;', $sanitized);
    }

    /**
     * Test sanitize method with array input
     */
    public function testSanitizeArray(): void
    {
        $input = [
            'name' => '<script>alert("xss")</script>',
            'email' => 'test@example.com'
        ];
        $sanitized = Security::sanitize($input);
        
        $this->assertIsArray($sanitized);
        $this->assertStringNotContainsString('<script>', $sanitized['name']);
        $this->assertEquals('test@example.com', $sanitized['email']);
    }

    /**
     * Test sanitize method with NULL bytes
     */
    public function testSanitizeNullBytes(): void
    {
        $input = "test" . chr(0) . "string";
        $sanitized = Security::sanitize($input);
        
        $this->assertStringNotContainsString(chr(0), $sanitized);
        $this->assertEquals('teststring', $sanitized);
    }

    /**
     * Test int validation with valid integer
     */
    public function testIntValid(): void
    {
        $result = Security::int('123');
        $this->assertEquals(123, $result);
        $this->assertIsInt($result);
    }

    /**
     * Test int validation with invalid input
     */
    public function testIntInvalid(): void
    {
        $result = Security::int('abc', 0);
        $this->assertEquals(0, $result);
    }

    /**
     * Test int validation with float
     */
    public function testIntFloat(): void
    {
        $result = Security::int('123.45');
        $this->assertEquals(123, $result);
    }

    /**
     * Test float validation with valid float
     */
    public function testFloatValid(): void
    {
        $result = Security::float('123.45');
        $this->assertEquals(123.45, $result);
        $this->assertIsFloat($result);
    }

    /**
     * Test float validation with invalid input
     */
    public function testFloatInvalid(): void
    {
        $result = Security::float('abc', 0.0);
        $this->assertEquals(0.0, $result);
    }

    /**
     * Test email validation with valid email
     */
    public function testValidateEmailValid(): void
    {
        $result = Security::validateEmail('test@example.com');
        $this->assertTrue($result);
    }

    /**
     * Test email validation with invalid email
     */
    public function testValidateEmailInvalid(): void
    {
        $result = Security::validateEmail('invalid-email');
        $this->assertFalse($result);
    }

    /**
     * Test URL validation with valid URL
     */
    public function testValidateUrlValid(): void
    {
        $result = Security::validateUrl('https://example.com');
        $this->assertTrue($result);
    }

    /**
     * Test URL validation with invalid URL
     */
    public function testValidateUrlInvalid(): void
    {
        $result = Security::validateUrl('not-a-url');
        $this->assertFalse($result);
    }

    /**
     * Test CSRF token generation
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGenerateCsrfToken(): void
    {
        $token = Security::generateCsrfToken();

        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes = 64 hex chars
    }

    /**
     * Test CSRF token retrieval (should return same token if not expired)
     */
    public function testGetCsrfToken(): void
    {
        $token1 = Security::getCsrfToken();
        $token2 = Security::getCsrfToken();
        
        $this->assertEquals($token1, $token2);
    }

    /**
     * Test CSRF token validation with valid token
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testValidateCsrfTokenValid(): void
    {
        $token = Security::generateCsrfToken();
        $result = Security::validateCsrfToken($token);
        
        $this->assertTrue($result);
    }

    /**
     * Test CSRF token validation with invalid token
     */
    public function testValidateCsrfTokenInvalid(): void
    {
        $result = Security::validateCsrfToken('invalid_token');
        $this->assertFalse($result);
    }

    /**
     * Test CSRF field generation
     */
    public function testCsrfField(): void
    {
        $field = Security::csrfField();
        
        $this->assertStringContainsString('<input', $field);
        $this->assertStringContainsString('type="hidden"', $field);
        $this->assertStringContainsString('name="csrf_token"', $field);
    }

    /**
     * Test CSRF token regeneration
     */
    public function testRegenerateCsrfToken(): void
    {
        $token1 = Security::generateCsrfToken();
        $token2 = Security::regenerateCsrfToken();
        
        $this->assertNotEquals($token1, $token2);
    }

    /**
     * Test CSRF token clearing
     */
    public function testClearCsrfToken(): void
    {
        Security::generateCsrfToken();
        Security::clearCsrfToken();
        
        $result = Security::validateCsrfToken('any_token');
        $this->assertFalse($result);
    }
}
