<?php

class ExceptionalRemote
{
    /*
     * Does the actual sending of an exception
     */
    public static function send_exception($exception)
    {
        $uniqueness_hash = $exception->uniqueness_hash();
        $hash_param = ($uniqueness_hash) ? null : "&hash={$uniqueness_hash}";
        $url = "/api/errors?api_key=".Exceptional::$api_key."&protocol_version=".Exceptional::$protocol_version.$hash_param;
        $compressed = gzdeflate($exception->to_json(), 6);
        self::call_remote($url, $compressed);
    }

    /*
     * Sends a POST request
     */
    public static function call_remote($url, $post_data)
    {
        $s = fsockopen(Exceptional::$host, Exceptional::$port, $errno, $errstr);
        if (!$s || empty($post_data)) { 
            return false;
        }

        $host = Exceptional::$host;

        $request  = "POST $url HTTP/1.1\r\nHost: {$host}\r\n";
        $request .= "Accept: */*\r\n";
        $request .= "User-Agent: Exceptional @package_version@\r\n";
        $request .= "Content-Type: text/json\r\n";
        $request .= "Connection: close\r\n";
        $request .= "Content-Length: ".strlen($post_data)."\r\n\r\n";
        $request .= "$post_data\r\n";

        fwrite($s, $request);

        if (Exceptional::$debugging === false) {
            return;
        }
        
        // for debugging
        $response = "\nDebugging...\n";
        while (!feof($s)) {
            $response .= fgets($s);
        }
        echo $response;
    }

}