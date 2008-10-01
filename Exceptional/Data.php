<?php
/**
 * ExceptionalData is an exception handler used by {@link ExceptionalClient}
 *
 * @category Services
 * @package  ExceptionalClient
 * @author   Jan Lehnardt <jan@php.net>
 * @license  
 * @version  GIT:
 * @link     http://getexceptional.com
 * @see      ExceptionalClient
 */
class Exceptional_Data
{
    /**
     * @return Exceptional_Data
     * 
     * @param Exception $exception An Exception! :-)
     */
    public function __construct($exception)
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
    	$user_ip        = $_SERVER["REMOTE_ADDR"];
    	$host_ip        = $_SERVER["SERVER_ADDR"];
    	$request_uri    = $_SERVER["REQUEST_URI"];
    	$document_root  = $_SERVER["DOCUMENT_ROOT"];
    	$request_method = $_SERVER["REQUEST_METHOD"];
    
    	$now = date("D M j H:i:s O Y");
    
    	$env                = $this->envToXML();
    	$session            = $this->sessionToXML();
    	$request_parameters = $this->requestToXML();
    
    	$trace    = $this->exception->getTrace();
    	$class    = $trace[0]["class"];
    	$function = $trace[0]["function"];
    
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
} /* class ExceptionalData */