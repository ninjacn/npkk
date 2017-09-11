<?php

namespace Ninjacn\Npkk;

class Http 
{

    public static function test() {
        return 'test';
    }

    /*
     * 非200都返回false
     * @return false|mixed
     */
    public static function sendRequest($url, $method = 'get', $postData = array(), $header = array()) {
        $debug = false;
        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);//debug
            if($method == 'post') {
                curl_setopt($ch, CURLOPT_POST, 1); 
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); 
            }

            if($header) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            }
            $output = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            //debug
            if($debug) {
                $headerSend = curl_getinfo($ch, CURLINFO_HEADER_OUT);
                print_r($headerSend);
                if($method == 'post') {
                    print_r(print_r($postData,true));
                }
                print_r('statusCode: ' . $statusCode );
                print_r('response body: ' . $output );
            }

            if($statusCode == 200) {
                return $output;
            }else{
                return null;
            }
            curl_close($ch);
        } catch(\Exception $e) {
        }
        return false;
    }

}
