<?php
/**
 * An interface to getexceptional.com's API.
 *
 * PHP version 5.1.0+
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *  - Neither the name of the The PEAR Group nor the names of its contributors
 *    may be used to endorse or promote products derived from this software
 *    without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * PHP implementation of Exceptional
 *
 * Original authors
 * Jan Lehnardt <jan@php.net>
 * Till Klampaeckel <till@php.net>
 *
 * Licensed under The BSD License
 * http://www.opensource.org/licenses/bsd-license.php
 */

class Exceptional
{

    static $exceptions = array();

    static $previous_exception_handler;
    static $previous_error_handler;

    static $api_key;
    static $use_ssl;

    static $host = "plugin.getexceptional.com";
    static $client_name = "exceptional-php";
    static $version = "1.2";
    static $protocol_version = 5;

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

        if (!is_array(self::$exceptions)) {
            return;
        }

        require dirname(__FILE__)."/exceptional/remote.php";
        require dirname(__FILE__)."/exceptional/environment.php";

        // send stack of exceptions to getexceptional
        foreach (self::$exceptions as $exception) {
            ExceptionalRemote::send_exception($exception);
        }
    }

    static function handle_error($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            // this error code is not included in error_reporting
            return;
        }

        if (!class_exists("PhpError")) {
            require dirname(__FILE__)."/exceptional/php_errors.php";
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
        if (!class_exists("ExceptionalData")) {
            require dirname(__FILE__)."/exceptional/data.php";
        }
        self::$exceptions[] = new ExceptionalData($exception);

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
