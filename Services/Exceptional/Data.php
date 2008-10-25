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
 * @package  Services_Exceptional_Data
 * @author   Jan Lehnardt <jan@php.net>
 * @author   Till Klampaeckel <till@php.net>
 * @license  http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version  GIT:
 * @link     http://getexceptional.com
 */

/**
 * Services_Exceptional_Data is an exception handler used by
 * {@link Services_Exceptional_Client}
 *
 * @category Services
 * @package  Services_Exceptional_Data
 * @author   Jan Lehnardt <jan@php.net>
 * @author   Till Klampaeckel <till@php.net>
 * @license  http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version  Release: @package_version@
 * @link     http://getexceptional.com
 * @see      Services_Exceptional_Client
 */
class Services_Exceptional_Data
{
    /**
     * @var mixed $exception Exception
     */
    protected $exception;
    
    /**
     * @return Services_Exceptional_Data
     * 
     * @param Exception $exception An Exception! :-)
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
     */
    public function toXML()
    {
    	$user_ip        = $_SERVER['REMOTE_ADDR'];
    	$host_ip        = $_SERVER['SERVER_ADDR'];
    	$request_uri    = $_SERVER['REQUEST_URI'];
    	$document_root  = $_SERVER['DOCUMENT_ROOT'];
    	$request_method = $_SERVER['REQUEST_METHOD'];
    
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
    
    	return 
"<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<error>
  <agent_id>cc25f30e09d5d2e14cbdc7b0e1da30ba0896a58c</agent_id>
  <controller_name>$class</controller_name>
  <error_class>$error_class</error_class>
  <action_name>$function</action_name>
  <environment>
$env
  </environment>
  <session>
$session
  </session>
  <rails_root>$document_root</rails_root>
  <url>$request_uri</url>
  <parameters>
$request_parameters
  </parameters>
  <occurred_at>$now</occurred_at>
  <message>$message</message>
  <backtrace>$backtrace</backtrace>
</error>";
    }
    
    /**
     * @return string
     * @uses   $_ENV
     * @uses   self::arrayToXml()
     */
    protected function envToXML()
    {
        return $this->arrayToXML($_ENV);
    }
    
    /**
     * @return string
     * @uses   $_SESSION
     * @uses   self::arrayToXml()
     */
    protected function sessionToXML()
    {
        return $this->arrayToXML($_SESSION);
    }
    
    /**
     * @return string
     * @uses   $_REQUEST
     * @uses   self::arrayToXml()
     */
    protected function requestToXML()
    {
        return $this->arrayToXML($_REQUEST);
    }

    /**
     * @return mixed
     * 
     * @param array $array An array! :-)
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