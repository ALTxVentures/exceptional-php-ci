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
    
    public static $exceptions = array();

    public static $previous_exception_handler;
    public static $previous_error_handler;

    public static $api_key;
    public static $host = "plugin.getexceptional.com";
    public static $port = 80;


    public static $client_name = "getexceptional-gem";
    public static $version = "2.0.19";
    public static $protocol_version = 5;

    public static $debugging;
    
    /*
     * Installs Exceptional as the default exception handler
     */
    public function __construct($api_key, $debugging = false)
    {
        self::$api_key = $api_key;
        self::$debugging = $debugging;

        // set exception handler & keep old exception handler around
        self::$previous_exception_handler = set_exception_handler(array(
            $this, "handle_exception"));

        self::$previous_error_handler = set_error_handler(array(
            $this, "handle_error"));
    }

    function handle_error($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting
            return;
        }
        self::handle_exception(new ErrorException($errstr, 0, $errno, $errfile, $errline), false);
        if (self::$previous_error_handler) {
            call_user_func(self::$previous_error_handler, $errno, $errstr, $errfile, $errline);
        }
    }

    /*
     * Exception handle class. Pushes the current exception onto the exception
     * stack and calls the previous handler, if it exists. Ensures seamless
     * integration.
     */
    public static function handle_exception($exception, $call_previous = true)
    {
        if (!class_exists("ExceptionalData")) {
            require dirname(__FILE__)."/exceptional/data.php";
        }
        self::$exceptions[] = new ExceptionalData($exception);

        // If there's a previous exception handler, we call that as well
        if ($call_previous && self::$previous_exception_handler) {
            call_user_func(self::$previous_exception_handler, $exception);
        }
    }

    /*
     * Destructor! Sends all collected exceptions to Exceptional
     */
    public function __destruct()
    {   
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

}
