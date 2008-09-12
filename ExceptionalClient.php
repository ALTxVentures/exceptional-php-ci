<?php
/**
 * Exception handler and client for getexceptional.com
 *
 * @author Jan Lehnardt <jan@php.net>
 **/
class ExceptionalClient
{
    /**
     * Installs the ExceptinoalClient class as the default exception handler
	 *
     * @param string api_key from getexceptional.com
     **/
    function __construct($api_key, $debugging = false)
    {
        $this->url = "/errors/?api_key={$api_key}&protocol_version=2";
        $this->host = "getexceptional.com";
        $this->port = 80;
		$this->debugging = $debugging;
		
        // set exception handler & keep old exception handler around
        $this->previous_exception_handler = 
			set_exception_handler(array($this, "handle_exception"));
    }
    
	/**
	 * Exception handle class. Pushes the current exception onto the exception
	 * stack and calls the previous handler, if it exists. Ensures seamless
	 * integration.
	 *
	 * @param Exception exception object, gets passed in by PHP
	 **/
    function handle_exception($exception)
    {
        $this->exceptions[] = new ExceptionData($exception);

		// If there's a previous exception handler, we call that as well
        if($this->previous_exception_handler) {
            $this->previous_exception_handler($exception);
        }
    }
    
	/**
	 * Destructor! Sends all collected exceptions to Exceptional
	 *
	 **/
    function __destruct()
    {
        // send stack of exceptions to getexceptional
        foreach($this->exceptions AS $exception) {
            $this->send_exception($exception);
        }
    }
    
	/**
	 * Does the actual sending of an exception
	 *
	 * @param Exception exception object, gets passed in by PHP
	 **/
    function send_exception($exception)
    {
        $body = $exception->toXML();
        $this->post($this->url, $body);
    }
    
	/**
	 * Sends a POST request
	 *
	 * @param String url to post to on `$this->server`
	 * @param String post data to send as body
	 **/
    function post($url, $post_data)
    {
        $s = fsockopen($this->host, $this->port, $errno, $errstr); 

        if(!$s || empty($post_data)) { 
            return false;
        }

        $request = "POST $url HTTP/1.1\r\nHost: $this->host\r\n";
        $request .= "Accept: */*\r\n";
        $request .= "User-Agent: exception-php-client 0.1\r\n";
        $request .= "Content-Type: text/xml\r\n";
        $request .= "Connection: close\r\n";
        $request .= "Content-Length: ".strlen($post_data)."\r\n\r\n";

        $request .= "$post_data\r\n";

        fwrite($s, $request);

		if(!$this->debugging) {
			return; // do not wait for response, we don't care
		} else {
			// for debugging
	        $response = "";
	        while(!feof($s)) {
	            $response .= fgets($s);
	        }
			var_dump($response);
		}
    }
}

/**
 * Wrapper class for data sent to exceptional API
 *
 * @package ExceptionalClient
 **/
class ExceptionData
{
    function __construct($exception)
    {
        $this->exception = $exception;
    }
    
    function toXML()
    {
        $user_ip = $_SERVER["REMOTE_ADDR"];
        $host_ip = $_SERVER["SERVER_ADDR"];
        $request_uri = $_SERVER["REQUEST_URI"];
		$document_root = $_SERVER["DOCUMENT_ROOT"];
        $request_method = $_SERVER["REQUEST_METHOD"];

        $now = date("D M j H:i:s O Y");
        $env = $this->envToXML();
        $session = $this->sessionToXML();
        $request_parameters = $this->requestToXML();
        
        $trace = $this->exception->getTrace();
        $class = $trace[0]["class"];
        $function = $trace[0]["function"];
        $message = $this->exception->getMessage();
        $backtrace = $this->exception->getTraceAsString();
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
    
    
    function envToXML()
    {
        return $this->_arrayToXML($_ENV);
    }
    
    function sessionToXML()
    {
        return $this->_arrayToXML($_SESSION);
    }
    
    function requestToXML()
    {
        return $this->_arrayToXML($_REQUEST);
    }

    function _arrayToXML($array)
    {
        if(!is_array($array) || empty($array)) {
            return "";
        }

        $return_value = array();
        foreach($array AS $key => $value) {
            $return_value[] = "    <$key>$value</$key>";
        }
        
        return implode("\n", $return_value);
    }
} /* class ExceptionData */