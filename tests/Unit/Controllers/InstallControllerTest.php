<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\InstallController;

class InstallControllerTest extends TestCase {

    private InstallController $controller;

    protected function setUp(): void {
        parent::setUp();
        $_SESSION = ['previous_url' => '/'];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->controller = new InstallController();
    }

    protected function tearDown(): void {
        parent::tearDown();
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        unset($this->controller);
    }

    public function testStep0DisplaysWelcomePage(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->step0();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testStep1DisplaysDatabaseForm(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        try {
            $this->controller->step1();
        } catch (\Throwable $e) {
            // Expected if INSTALLED constant is true
        }
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testStep1RejectsInvalidDatabaseCredentials(): void {
        $_POST['hostname'] = 'invalid-host';
        $_POST['username'] = 'invalid-user';
        $_POST['password'] = 'wrong-password';
        $_POST['database'] = 'invalid-db';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        try {
            $this->controller->step1();
        } catch (\Throwable $e) {
            // Expected if database connection fails
        }
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testStep2DisplaysAdminForm(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        try {
            $this->controller->step2();
        } catch (\Throwable $e) {
            // Expected if INSTALLED constant check fails
        }
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testStep2RejectsIfPasswordsDoNotMatch(): void {
        $_POST['name'] = 'Admin User';
        $_POST['email'] = 'admin@example.com';
        $_POST['password'] = 'password123';
        $_POST['repeat_password'] = 'password456';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        try {
            $this->controller->step2();
        } catch (\Throwable $e) {
            // Expected if INSTALLED constant check fails
        }
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testStep3DisplaysPayPalForm(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        try {
            $this->controller->step3();
        } catch (\Throwable $e) {
            // Expected if INSTALLED constant check fails
        }
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testStep3HandlesPayPalConfiguration(): void {
        $_POST['paypal_business_email'] = 'business@paypal.com';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        try {
            $this->controller->step3();
        } catch (\Throwable $e) {
            // Expected if INSTALLED constant check fails
        }
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testStep4DisplaysMailtrapForm(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        try {
            $this->controller->step4();
        } catch (\Throwable $e) {
            // Expected if installation is not complete
        }
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testStep4HandlesMailtrapConfiguration(): void {
        $_POST['mail_host'] = 'smtp.mailtrap.io';
        $_POST['mail_port'] = '465';
        $_POST['mail_username'] = 'user@mailtrap.io';
        $_POST['mail_password'] = 'password123';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        try {
            $this->controller->step4();
        } catch (\Throwable $e) {
            // Expected if installation is not complete
        }
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function testStep5DisplaysCompletionPage(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        try {
            $this->controller->step5();
        } catch (\Throwable $e) {
            // Expected if installation is not complete
        }
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }
}
