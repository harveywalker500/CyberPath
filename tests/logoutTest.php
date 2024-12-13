<?php


use PHPUnit\Framework\TestCase;

class logoutTest extends TestCase
{
    public function testLogOut(){
        session_start();
        $_SESSION['user'] = 'test';

        require_once("src/php/functions.php");

        logOut();

        $this->assertArrayNotHasKey('user', $_SESSION);
    }
}
