<?php

// set custom error handler
function my_error_handler($errno, $errstr, $errfile, $errline) {
    echo "Error on line $errline\n";
}
set_error_handler("my_error_handler");


// set custom exception handler
function my_exception_handler($exception) {
    echo "Exception thrown: ".$exception->getMessage()."\n";
}
set_exception_handler("my_exception_handler");


// setup Exceptional with the following three lines
// this code must come **after** you set custom error/exception handlers
require dirname(__FILE__) . "/exceptional.php";

$api_key = "YOUR-API-KEY";

$exceptional = new Exceptional($api_key, true);


// control which errors are caught with error_reporting
error_reporting(E_ALL);


// start testing
echo $hi;
$math = 1 / 0;

function backtrace($i) {
    if ($i < 6) {
        return backtrace($i + 1);
    }
    echo $cool;
}
backtrace(0);

class Foo
{
    public function bar()
    {
        throw new Exception("This is pretty neat!");
    }
}

$f = new Foo;
$f->bar();

// execution halts after exception_handler is called (PHP behavior)
// so code below never gets called
echo "This never gets called!";

?>