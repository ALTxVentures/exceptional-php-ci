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
            $this->backtrace[] = "$t[file]:$t[line]:in `$t[function]'";
        }
    }

    public function uniqueness_hash()
    {
        return md5(implode("", $this->backtrace));
    }

    public function to_json()
    {
        $now = date("D M j H:i:s O Y");
        
        $trace    = $this->exception->getTrace();
        $class    = $trace[0]["class"];
        $function = $trace[0]["function"];

        $message     = $this->exception->getMessage();
        $error_class = get_class($this->exception);

        $data = ExceptionalEnvironment::to_array();
        $data["exception"] = array(
            "exception_class" => $error_class,
            "message" => $message,
            "backtrace" => $this->backtrace,
            "occurred_at" => $now
        );
        
        $data["rescue_block"] = array(
            "name" => ""
        );
        
        return json_encode($data);
    }    

}
