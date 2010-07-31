<?php

class ExceptionalData
{
	
    protected $exception;
    
    public function __construct(Exception $exception)
	{
		//echo "[Exceptional] ".$exception->getMessage()."\n";
        $this->exception = $exception;
    }

	public function uniqueness_hash()
	{
		return md5($this->exception->getTraceAsString());
	}

    public function to_json()
	{
		$now = date("D M j H:i:s O Y");
		
		$trace    = $this->exception->getTrace();
        $class    = $trace[0]["class"];
        $function = $trace[0]["function"];

        $message     = $this->exception->getMessage();
        $error_class = get_class($this->exception);

		foreach ($trace as $t) {
			$backtrace[] = "$t[file]:$t[line]:in `$t[function]'";
		}

		$data = ExceptionalEnvironment::to_array();
		$data["exception"] = array(
			"exception_class" => $error_class,
			"message" => $message,
			"backtrace" => $backtrace,
			"occurred_at" => $now
		);
		
		$data["rescue_block"] = array(
			"name" => ""
		);

		return json_encode($data);
    }

}
