# Exceptional PHP

The power of [Exceptional](http://getexceptional.com) for PHP

## Super simple setup

    require "path/to/exceptional.php";
    Exceptional::setup("YOUR-API-KEY");

You can turn off exception notifications by passing an empty string as the API key.  This is great for development.

    if (PHP_ENV == "production") {
      $api_key = "YOUR-API-KEY";
    }
    else {
      $api_key = "";
    }

    Exceptional::setup($api_key);

You can turn on SSL by setting the second parameter to `true`.

    Exceptional::setup($api_key, true);

## Exceptions and errors

Exceptional PHP catches both errors and exceptions. You can control which errors are caught. If you want to ignore certain errors, use `error_reporting()`. Here are some common settings:

    error_reporting(E_ALL & ~E_STRICT);             // ignore strict errors
    error_reporting(E_ALL & ~E_NOTICE);             // ignore notices
    error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));  // ignore both

Custom error and exception handlers are supported - see examples/advanced.php.

Fatal and parse errors are caught, too - as long the setup file parses correctly.

## 404 support

Add the following code to your 404 handler to track 404 errors:

    throw new Http404Error();

## Send extra data with your exceptions

    $context = array(
        "user_id" => 1
    );
    Exceptional::context($context);

See the [Exceptional documentation](http://docs.getexceptional.com/extras/context/) for more details.

## Controller + action support

You can include the controller and action names in your exceptions for easier debugging.

    Exceptional::$controller = "welcome";
    Exceptional::$action = "index";
