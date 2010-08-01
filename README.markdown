# Exceptional-PHP

PHP wrapper for the http://getexceptional.com API (for PHP 5.1.0+)

### Super simple setup

    require "path/to/exceptional.php";
    $exceptional = new Exceptional("YOUR-API-KEY");

### Exceptions and errors!

Exceptional-PHP catches both errors and exceptions. You can control which errors are caught. If you want to ignore notices, use the following line:

    error_reporting(E_ALL ^ E_NOTICE);

It also supports custom error and exception handlers - see example_advanced.php.
It also catches parse errors and fatal errors, as long the setup file parses correctly.

### 404 Support

Add the following code to your 404 handler to track 404 errors:

    throw new Http404Exception();