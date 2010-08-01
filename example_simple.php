<?php

// setup Exceptional with the following three lines
require dirname(__FILE__) . "/exceptional.php";

$api_key = "YOUR-API-KEY";

$exceptional = new Exceptional($api_key, true);


// control which errors are caught with error_reporting
error_reporting(E_ALL);

// start testing
$math = 1 / 0;

?>