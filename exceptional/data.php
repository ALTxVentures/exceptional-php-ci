<?php

class ExceptionalData
{
    
    protected $exception;
    protected $backtrace = array();
    
    public function __construct(Exception $exception)
    {
        //echo "[Exceptional] ".$exception->getMessage()."\n";
        $this->exception = $exception;
        
        $trace = $this->exception->getTrace();
        foreach ($trace as $t) {
            if (!isset($t["file"])) continue;
            $this->backtrace[] = "$t[file]:$t[line]:in `$t[function]\'";
        }
    }

    public function uniqueness_hash()
    {
        return md5(implode("", $this->backtrace));
    }

    public function to_json()
    {        
        $data = ExceptionalEnvironment::to_array();
        
        $error_class = get_class($this->exception);
        if ($error_class == "Http404Error") {
            $error_class = "ActionController::UnknownAction";
            $protocol = (!empty($_SERVER["HTTPS"])) ? "https://" : "http://";
            $data["request"] = array(
                "url" => "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
                "controller" => "none",
                "action" => "none",
                "parameters" => $_REQUEST,
                "request_method" => strtolower($_SERVER["REQUEST_METHOD"]),
                "remote_ip" => $_SERVER["REMOTE_ADDR"],
                "headers" => getallheaders(),
                "session" => array("session_id" => "", "data" => array())
            );
        }
        else {
            $data["rescue_block"] = array(
                "name" => ""
            );
        }
        
        $message = $this->exception->getMessage();
        $now = date("D M j H:i:s O Y");

        $data["exception"] = array(
            "exception_class" => $error_class,
            "message" => $message,
            "backtrace" => $this->backtrace,
            "occurred_at" => $now
        );
        
        return json_encode($data);
    }    

}

// http://php.net/manual/en/function.getallheaders.php
if (!function_exists("getallheaders"))
{
    function getallheaders()
    {
       foreach ($_SERVER as $name => $value)
       {
           if (substr($name, 0, 5) == "HTTP_")
           {
               $headers[str_replace(" ", "-", ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
           }
       }
       return $headers;
    }
}
