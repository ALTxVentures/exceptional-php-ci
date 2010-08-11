<?php

class ExceptionalRemote
{
    /*
     * Does the actual sending of an exception
     */
    static function send_exception($exception)
    {
        $uniqueness_hash = $exception->uniqueness_hash();
        $hash_param = ($uniqueness_hash) ? null : "&hash={$uniqueness_hash}";
        $url = "/api/errors?api_key=".Exceptional::$api_key."&protocol_version=".Exceptional::$protocol_version.$hash_param;
        $compressed = gzcompress($exception->to_json(), 1);
        self::call_remote($url, $compressed);
    }

    /*
     * Sends a POST request
     */
    static function call_remote($url, $post_data)
    {        
        $s = fsockopen(Exceptional::$host, 80, $errno, $errstr, 4);
        if (!$s) {
            return false;
        }

        $request  = "POST $url HTTP/1.1\r\n";
        $request .= "Host: ".Exceptional::$host."\r\n";
        $request .= "Accept: */*\r\n";
        $request .= "User-Agent: ".Exceptional::$client_name." ".Exceptional::$version."\r\n";
        $request .= "Content-Type: text/json\r\n";
        $request .= "Connection: close\r\n";
        $request .= "Content-Length: ".strlen($post_data)."\r\n\r\n";
        $request .= "$post_data\r\n";

        fwrite($s, $request);
        
        // must wait for a response
        $response = "";
        while (!feof($s)) {
            $response .= fgets($s);
        }
        
        if (Exceptional::$debugging !== false) {
            echo $response;
        }
    }

}