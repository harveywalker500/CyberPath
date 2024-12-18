<?php


use PHPUnit\Framework\TestCase;

class registerTest extends TestCase
{
    public function testEmptyFormSubmission()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'username' => '',
            'password' => '',
            'confirm_password' => '',
            'forename' => '',
            'surname' => '',
            'email' => ''
        ];

        ob_start();
        include 'register.php';
        $output = ob_get_clean();

        $this->assertStringNotContainsString('Please fill in all fields.', $output);
    }

    public function testInvalidEmailFormat()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'username' => 'testuser',
            'password' => '8idzGtGD-!6sRqqg',
            'confirm_password' => '8idzGtGD-!6sRqqg',
            'forename' => 'John',
            'surname' => 'Doe',
            'email' => 'invalid-email'
        ];

        ob_start();
        include 'register.php';
        $output = ob_get_clean();

        $this->assertStringNotContainsString('Invalid email format.', $output);
    }

    public function testPasswordMismatch()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'username' => 'testuser',
            'password' => '8idzGtGD-!6sRqqg',
            'confirm_password' => '6!gco-EmEKZX*yu4',
            'forename' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com'
        ];

        ob_start();
        include 'register.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Passwords do not match.', $output);
    }

    public function testSuccessfulRegistration()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'username' => 'testuser',
            'password' => '8idzGtGD-!6sRqqg',
            'confirm_password' => '8idzGtGD-!6sRqqg',
            'forename' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com'
        ];

        // Mock the database connection
        $mockDb = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockDb->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);
        $mockDb->method('lastInsertId')->willReturn('1'); // Return a string instead of an integer

        function getConnection() {
            global $mockDb;
            return $mockDb;
        }

        ob_start();
        include 'register.php';
        $output = ob_get_clean();

        $this->assertStringNotContainsString('Error registering user:', $output);
    }
}
