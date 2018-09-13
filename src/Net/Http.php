<?php

namespace Npkk\Net;

use \Exception;

/**
 * Http相关
 * 
 * 说明:
 *
 * GET请求
 * $http = new Http($url, ['timeout' => 20], ['user-agent' => 'bbb'] );
 * $http->debug = true; //debug
 * $body = $http->get()->body;
 *
 * POST请求
 * $http = new Http($url, ['timeout' => 20], ['user-agent' => 'bbb'] );
 * $http->sendJson = true; // json submit
 * $body = $http->post($postData)->body;
 *
 * $debugInfo = $http->post($postData)->debugInfo;
 */
class Http 
{

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    const CONNECT_TIMEOUT = 10;

    private $url;

    private $method = self::METHOD_GET;

    private $opts = []; //curl参数

    private $headerParam = []; // 请求头

    //post
    private $postData = [];
    private $sendJson = false;

    private $status = 0;
    private $header; //请求header
    private $responseHeader; //响应header
    private $body;

    private $debug = false;
    private $debugInfo = [];

    public function __construct($url, $opts = [], $headerParam = [])  {
        if(empty($url)) {
            throw new Exception('Url不能为空');
        }
        $this->url = $url;
        $this->opts = $opts;
        $this->headerParam = $headerParam;
    }

    public function __get($name) {
        if(isset($this->$name)) {
            return $this->$name;
        } else {
            throw new Exception($name . ' 属性不存在');
        }
    }
    public function __set($name, $value) {
        if(isset($this->$name)) {
            $this->$name = $value;
        } else {
            throw new Exception($name . ' 属性不存在');
        }
    }

    public function get() {
        $this->method = self::METHOD_GET;
        $this->sendRequest();
        return $this;
    }

    public function post($postData = [] ) {
        $this->method = self::METHOD_POST;
        $this->postData = $postData;
        $this->sendRequest();
        return $this;
    }

    public function getStatusCode() {
        return $this->status;
    }

    /*
     * 非200都返回false
     * @param array $opts 选项，支持timeout等
     * @return false|mixed
     */
    private function sendRequest() {
        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (isset($this->opts['timeout']) && $this->opts['timeout']) ? $this->opts['timeout'] : self::CONNECT_TIMEOUT);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            if($this->method == self::METHOD_POST) {
                curl_setopt($ch, CURLOPT_POST, 1); 
                if($this->sendJson) {
                    $this->postData = json_encode($this->postData);
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postData); 
            }

            if($this->headerParam) {
                if($this->sendJson) {
                    $this->headerParam = array_merge($this->headerParam, [
                        'Content-Type: application/json',
                        'Content-Length:' . strlen($this->postData)
                    ]);
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headerParam);
            }

            $response = curl_exec($ch);
            if($response) {
                list($responseHeader, $body) = explode("\r\n\r\n", $response, 2);
                $this->body = $body;
                $this->responseHeader = $responseHeader;
            }
            $this->header = curl_getinfo($ch, CURLINFO_HEADER_OUT);
            $this->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if($this->debug) {
                $this->debugInfo['requestHeader'] = $this->header;
                $this->debugInfo['responseHeader'] = $this->responseHeader;
                $this->debugInfo['totalTime'] = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
                $this->debugInfo['connectTime'] = curl_getinfo($ch, CURLINFO_CONNECT_TIME);
                $this->debugInfo['nameLookupTime'] = curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME);

                if($this->method == self::METHOD_POST) {
                    $this->debugInfo['postData'] = $this->postData;
                }
            }
            curl_close($ch);
        } catch(\Exception $e) {
            $this->debugInfo['exception'] = $e->getMessage();
        }
    }

}
