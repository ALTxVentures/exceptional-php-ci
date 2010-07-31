# Exceptional-PHP

PHP wrapper for the http://getexceptional.com API

### Super simple setup

    require "path/to/exceptional.php";
    $exceptional = new Exceptional("YOUR-API-KEY");

### Exceptions and errors!

Exceptional-PHP catches both errors and exceptions. You can control which errors are caught. If you want to ignore notices, use the following line:

    error_reporting(E_ALL ^ E_NOTICE);

It also supports custom error and exception handlers (see example_advanced.php)