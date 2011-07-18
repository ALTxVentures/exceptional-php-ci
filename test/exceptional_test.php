<?php
/*
 * To run all tests, use:
 *
 * phpunit test/*
 */

require "PHPUnit/Autoload.php";

require dirname(__FILE__)."/../exceptional.php";

// report all errors
error_reporting(-1);

class ExceptionalTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $_SERVER["HTTP_HOST"] = "localhost";
    }

    function testGetParameters()
    {
        $_GET["a"] = "GET works";

        $this->createExceptionData();

        $this->assertEquals($this->request["parameters"]["a"], "GET works");
    }

    function testPostParameters() {
        $_POST["b"] = "POST works";

        $this->createExceptionData();

        $this->assertEquals($this->request["parameters"]["b"], "POST works");
    }

    function testControllerAndAction()
    {
        Exceptional::$controller = "home";
        Exceptional::$action = "index";

        $this->createExceptionData();

        $this->assertEquals($this->request["controller"], "home");
        $this->assertEquals($this->request["action"], "index");
    }

    function testSessionFilter() {
        $session_name = ini_get("session.name");
        $session_id = rand();
        $_SERVER["HTTP_Cookie"] = "$session_name=$session_id";

        $this->createExceptionData();

        $this->assertEquals($this->request["headers"]["Cookie"], "$session_name=[FILTERED]");
    }

    function createExceptionData() {
        $notice = new PhpNotice("Test", 0, "", 0);
        $this->data = new ExceptionalData($notice);
        $this->request = $this->data->data["request"];
    }

}
