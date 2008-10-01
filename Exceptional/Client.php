<?php
/**
 * Exception handler and client for getexceptional.com
 *
 * @category Services
 * @package  ExceptionalClient
 * @author   Jan Lehnardt <jan@php.net>
 * @license  
 * @version  GIT:
 * @link     http://getexceptional.com
 */
class Exceptional_Client
{
    /**
     * Array holding all exceptions that were thrown in a request
     *
     * @var array exception stack
     * @see self::__destruct()
     **/
    public $exceptions = array();

    /**
     * @var string * getexceptional.com-related
     * @see self::__construct()
     */
    public $url;
    public $host = 'getexceptional.com';
    public $port = 80;
    
    /**
     * @var int $protocol_version getexceptional needs this.
     * @see self::__construct()
     */
    public $protocol_version = 2;

    /**
     * @var boolean $debugging A flag.
     * @see self::__construct()
     */
    public $debugging;
    
	/**
	 * Installs the ExceptinoalClient class as the default exception handler
	 *
	 * @param string  $api_key   from getexceptional.com
	 * @param boolean $debugging flag
	 * 
	 * @return ExceptionalClient
	 * @todo   Do something nifty with all the configuration.
	 */
	public function __construct($api_key, $debugging = false)
	{
        $this->url       = "/errors/?api_key={$api_key}&protocol_version=";
        $this->url       = $this->protocol_version;
		$this->debugging = $debugging;
		
        // set exception handler & keep old exception handler around
        $this->previous_exception_handler = set_exception_handler(array(
            $this, 'handleException'));
	}
	
	/**
	 * Exception handle class. Pushes the current exception onto the exception
	 * stack and calls the previous handler, if it exists. Ensures seamless
	 * integration.
	 *
	 * @param Exception $exception object, gets passed in by PHP
	 * 
	 * @return void
	 * @uses   ExceptionalData::__construct()
	 */
	public static function handleException($exception)
	{
	    if (!class_exists('Exceptional_Data')) {
            include_once dirname(__FILE__) . '/Data.php';
            if (!class_exists('Exceptional_Data')) {
                die('Could not find class "ExceptionalData".');
            }
	    }
		$this->exceptions[] = new ExceptionalData($exception);

		// If there's a previous exception handler, we call that as well
		if($this->previous_exception_handler) {
			$this->previous_exception_handler($exception);
		}
	}
	
	/**
	 * Destructor! Sends all collected exceptions to Exceptional
	 * 
	 * @uses self::$exceptions
	 * @uses self::sendException()
	 */
	public function __destruct()
	{
		if(!is_array($this->exceptions)) {
			return;
		}

		// send stack of exceptions to getexceptional
		foreach($this->exceptions AS $exception) {
			$this->sendException($exception);
		}
	}
	
	/**
	 * Does the actual sending of an exception
	 *
	 * @param Exception exception object, gets passed in by PHP
	 * 
	 * @return void
	 * @uses   self::makeRequest()
	 */
	public function sendException($exception)
	{
		$body = $exception->toXML();
		$this->makeRequest($this->url, $body);
	}
	
	/**
	 * Sends a POST request
	 *
	 * @param String $url       to post to on `$this->server`
	 * @param String $post_data to send as body
	 * 
	 * @return mixed False, if the request fails, void otherwise.
	 * 
	 * @uses self::$host
	 * @uses self::$post
	 * @uses self::$debugging
	 */
	protected function makeRequest($url, $post_data)
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

		if($this->debugging === false) {
			return; // do not wait for response, we don't care
		}
		// for debugging
		$response = "";
		while(!feof($s)) {
			$response .= fgets($s);
		}
		var_dump($response);
	}
}