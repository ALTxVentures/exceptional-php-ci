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
 * @category Services
 * @package  Services_Exceptional
 * @author   Jan Lehnardt <jan@php.net>
 * @author   Till Klampaeckel <till@php.net>
 * @license  http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version  GIT:
 * @link     http://getexceptional.com
 */

/**
 * Services_Exceptional_Data is an exception handler used by
 * {@link Services_Exceptional}
 *
 * @category Services
 * @package  Services_Exceptional
 * @author   Jan Lehnardt <jan@php.net>
 * @author   Till Klampaeckel <till@php.net>
 * @license  http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version  Release: @package_version@
 * @link     http://getexceptional.com
 * @see      Services_Exceptional
 */
class Services_Exceptional_Data
{
    /**
     * @var mixed $exception Exception
     */
    protected $exception;
    
    /**
     * __construct
     *
     * @param Exception $exception An Exception! :-)
     * 
     * @return Services_Exceptional_Data
     */
    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * toXML function converts the exception along with meta-data from the
     * environment to the format getexceptional.com expects when reporting.
     * 
     * @return string
     *
     * @todo Check Agent ID.
     */
    public function toXML()
    {
        $user_ip        = @$_SERVER['REMOTE_ADDR'];
        $host_ip        = @$_SERVER['SERVER_ADDR'];
        $request_uri    = @$_SERVER['REQUEST_URI'];
        $document_root  = @$_SERVER['DOCUMENT_ROOT'];
        $request_method = @$_SERVER['REQUEST_METHOD'];

        $now = date("D M j H:i:s O Y");
    
        $env                = $this->envToXML();
        $session            = $this->sessionToXML();
        $request_parameters = $this->requestToXML();

        $trace    = $this->exception->getTrace();
        $class    = $trace[0]['class'];
        $function = $trace[0]['function'];

        $message     = $this->exception->getMessage();
        $backtrace   = $this->exception->getTraceAsString();
        $error_class = get_class($this->exception);

        $response  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $response .= "<error>\n";
        $response .= "\t<agent_id>";
        $response .= "cc25f30e09d5d2e14cbdc7b0e1da30ba0896a58c";
        $response .= "</agent_id>\n";
        $response .= "\t<controller_name>$class</controller_name>\n";
        $response .= "\t<error_class>$error_class</error_class>\n";
        $response .= "\t<action_name>$function</action_name>\n";
        $response .= "\t<environment>\n";
        $response .= "\t$env\n";
        $response .= "\t</environment>\n";
        $response .= "\t<session>\n";
        $response .= "\t$session\n";
        $response .= "\t</session>\n";
        $response .= "\t<rails_root>$document_root</rails_root>\n";
        $response .= "\t<url>$request_uri</url>\n";
        $response .= "\t<parameters>\n";
        $response .= "\t$request_parameters\n";
        $response .= "\t</parameters>\n";
        $response .= "\t<occurred_at>$now</occurred_at>\n";
        $response .= "\t<message>$message</message>\n";
        $response .= "\t<backtrace>$backtrace</backtrace>\n";
        $response .= "\t</error>";

        return $response;
    }
    
    /**
     * Convert to the $_ENV variable to XML.
     *
     * @return string
     * @uses   $_ENV
     * @uses   self::arrayToXml()
     */
    protected function envToXML()
    {
        return $this->arrayToXML($_ENV);
    }
    
    /**
     * Convert $_SESSION to XML.
     *
     * @return string
     * @uses   $_SESSION
     * @uses   self::arrayToXml()
     */
    protected function sessionToXML()
    {
        return $this->arrayToXML($_SESSION);
    }
    
    /**
     * Convert $_REQUEST to XML.
     *
     * @return string
     * @uses   $_REQUEST
     * @uses   self::arrayToXml()
     */
    protected function requestToXML()
    {
        return $this->arrayToXML($_REQUEST);
    }

    /**
     * Convert an array to XML.
     * 
     * @param array $array An array! :-)
     *
     * @return mixed
     */
    protected function arrayToXML($array)
    {
        if (!is_array($array) || empty($array)) {
            return '';
        }
    
        $return_value = '';
        foreach ($array as $key => $value) {
            $return_value .= "	   <{$key}>{$value}</{$key}>\n";
        }
        return $return_value;
    }
}
