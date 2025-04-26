<?php
namespace Tests;


use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        session_destroy();
    }

    public function testLoginWithCorrectCredentials()
    {
        $_POST = [
            'email' => 'testuser2@example.com',
            'password' => 'password123'
        ];
        $_GET = ['testing' => 'true'];

        ob_start();
        include __DIR__ . '/../../php/signIn.php';
        $output = ob_get_clean();

        $this->assertEquals('Login successful', trim($output));
    }

    public function testLoginWithWrongPassword()
    {
        $_POST = [
            'email' => 'testuser2@example.com',
            'password' => 'wrongpassword'
        ];
        $_GET = ['testing' => 'true'];

        ob_start();
        include __DIR__ . '/../../php/signIn.php';
        $output = ob_get_clean();

        $this->assertEquals('Invalid email or password', trim($output));
    }

    public function testLoginWithMissingFields()
    {
        $_POST = [
            'email' => '',
            'password' => ''
        ];
        $_GET = ['testing' => 'true'];

        ob_start();
        include __DIR__ . '/../../frontend/php/signIn.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('required', trim($output));
    }

    public function testLoginWithNonexistentAccount()
    {
        $_POST = [
            'email' => 'nonexistent@example.com',
            'password' => 'somepassword'
        ];
        $_GET = ['testing' => 'true'];

        ob_start();
        include __DIR__ . '/../../frontend/php/signIn.php';
        $output = ob_get_clean();

        $this->assertEquals('Invalid email or password', trim($output));
    }
    public function testDatabaseConnection()
{
    $config = require __DIR__ . '/../../frontend/php/config.php';
    $conn = new \mysqli(
        $config['db_host'],
        $config['db_user'],
        $config['db_pass'],
        $config['db_name']
    );
    $this->assertNull($conn->connect_error);
}
}