<?php
/*
 * To run all tests, use:
 *
 * phpunit test/*
 */

require "PHPUnit/Autoload.php";

require dirname(__FILE__)."/../exceptional.php";
Exceptional::setup("");

// report all errors
error_reporting(-1);

class ExceptionalTest extends PHPUnit_Framework_TestCase
{
    function testPhpWarning()
    {
        $math = 1 / 0;

        $e = end(Exceptional::$exceptions);

        $this->assertEquals(get_class($e), "PhpWarning");
        $this->assertEquals($e->getMessage(), "Division by zero");
    }

    function testPhpNotice()
    {
      $little = $big;

      $e = end(Exceptional::$exceptions);

      $this->assertEquals(get_class($e), "PhpNotice");
      $this->assertEquals($e->getMessage(), "\$big is undefined");
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

}
