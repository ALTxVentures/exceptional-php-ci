<?php

/*
 * Require files
 *
 * Not DRY, but better than poluting the global namespace with a variable
 */

require_once dirname(__FILE__)."/exceptional/data.php";
require_once dirname(__FILE__)."/exceptional/environment.php";
require_once dirname(__FILE__)."/exceptional/errors.php";
require_once dirname(__FILE__)."/exceptional/remote.php";

class Exceptional
{

    static $exceptions = array();

    static $previous_exception_handler;
    static $previous_error_handler;

    static $api_key;
    static $use_ssl;

    static $host = "plugin.getexceptional.com";
    static $client_name = "exceptional-php";
    static $version = "1.4";
    static $protocol_version = 5;

    static $controller = "";
    static $action = "";

    static $context = array();

    static $debugging = false;

    /*
     * Installs Exceptional as the default exception handler
     */
    static function setup($api_key, $use_ssl = false)
    {
        if ($api_key == "") {
          $api_key = null;
        }

        self::$api_key = $api_key;
        self::$use_ssl = $use_ssl;

        // set exception handler & keep old exception handler around
        self::$previous_exception_handler = set_exception_handler(
            array("Exceptional", "handle_exception")
        );

        self::$previous_error_handler = set_error_handler(
            array("Exceptional", "handle_error")
        );

        register_shutdown_function(
            array("Exceptional", "shutdown")
        );
    }

    static function shutdown()
    {
        if ($e = error_get_last()) {
            self::handle_error($e["type"], $e["message"], $e["file"], $e["line"]);
        }

        if (Exceptional::$api_key == null || empty(self::$exceptions)) {
            return;
        }

        // send stack of exceptions to getexceptional
        foreach (self::$exceptions as $exception) {
          $data = new ExceptionalData($exception);
          ExceptionalRemote::send_exception($data);
        }
    }

    static function handle_error($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            // this error code is not included in error_reporting
            return;
        }

        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $ex = new PhpNotice($errstr, $errno, $errfile, $errline);
                break;

            case E_WARNING:
            case E_USER_WARNING:
                $ex = new PhpWarning($errstr, $errno, $errfile, $errline);
                break;

            case E_STRICT:
                $ex = new PhpStrict($errstr, $errno, $errfile, $errline);
                break;

            case E_PARSE:
                $ex = new PhpParse($errstr, $errno, $errfile, $errline);
                break;

            default:
                $ex = new PhpError($errstr, $errno, $errfile, $errline);
        }

        self::handle_exception($ex, false);
        if (self::$previous_error_handler) {
            call_user_func(self::$previous_error_handler, $errno, $errstr, $errfile, $errline);
        }
    }

    /*
     * Exception handle class. Pushes the current exception onto the exception
     * stack and calls the previous handler, if it exists. Ensures seamless
     * integration.
     */
    static function handle_exception($exception, $call_previous = true)
    {
        self::$exceptions[] = $exception;

        // if there's a previous exception handler, we call that as well
        if ($call_previous && self::$previous_exception_handler) {
            call_user_func(self::$previous_exception_handler, $exception);
        }
    }

    static function context($data = array()) {
        self::$context = array_merge(self::$context, $data);
    }

    static function clear() {
        self::$context = array();
    }

}

class Http404Error extends Exception {

    public function __construct()
    {
        if (!isset($_SERVER["HTTP_HOST"])) {
            echo "Run PHP on a server to use Http404Error.\n";
            exit(0);
        }
        parent::__construct($_SERVER["REQUEST_URI"]." can't be found.");
    }

}
