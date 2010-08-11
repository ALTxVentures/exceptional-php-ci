# Exceptional-PHP

PHP wrapper for the http://getexceptional.com API (for PHP 5.1.0+)

### Super simple setup

    require "path/to/exceptional.php";
    Exceptional::setup("YOUR-API-KEY");

### Exceptions and errors!

Exceptional-PHP catches both errors and exceptions. You can control which errors are caught. If you want to ignore certain errors, use `error_reporting()`. Here are some common settings:

    error_reporting(E_ALL & ~E_STRICT);             // ignore strict errors
    error_reporting(E_ALL & ~E_NOTICE);             // ignore notices
    error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));  // ignore both

Custom error and exception handlers are supported - see example_advanced.php.

Fatal and parse errors are caught, too - as long the setup file parses correctly.

### 404 Support

Add the following code to your 404 handler to track 404 errors:

    throw new Http404Exception();
    
### Send extra data

    $context = array(
        "user_id" => 1
    );
    Exceptional::context($context);