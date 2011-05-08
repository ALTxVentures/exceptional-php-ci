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
        Exceptional::setup("");
        $_SERVER["HTTP_HOST"] = "localhost";
    }

    function testParameters()
    {
        $_GET["a"] = "GET works";
        $_POST["b"] = "POST works";

        $notice = new PhpNotice("Test", 0, "", 0);
        $data = new ExceptionalData($notice);

        $params = $data->data["request"]["parameters"];

        $this->assertEquals($params["a"], "GET works");
        $this->assertEquals($params["b"], "POST works");
    }

    function testControllerAndAction()
    {
        Exceptional::$controller = "home";
        Exceptional::$action = "index";

        $notice = new PhpNotice("Test", 0, "", 0);
        $data = new ExceptionalData($notice);

        $request = $data->data["request"];

        $this->assertEquals($request["controller"], "home");
        $this->assertEquals($request["action"], "index");
    }

}
