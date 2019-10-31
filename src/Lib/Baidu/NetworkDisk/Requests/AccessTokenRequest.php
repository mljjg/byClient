<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 15:02
 */

namespace Jjg\Lib\Baidu\NetworkDisk\Requests;


use Illuminate\Support\Facades\Cache;
use Jjg\Lib\Baidu\NetworkDisk\Result;

class AccessTokenRequest extends Request
{
    private $code;
    private $api_key;
    private $secret_key;

    public function __construct()
    {
        parent::__construct();
//        $this->code = $code;
//        $this->api_key = $api_key;
//        $this->secret_key = $secret_key;
        $this->method = 'GET';
        $this->url_fmt = 'https://openapi.baidu.com/oauth/2.0/token?grant_type=authorization_code&code=%s&client_id=%s&client_secret=%s&redirect_uri=oob';
    }

    /**
     * @return string
     */
    public function getHttpUrl()
    {
        return sprintf($this->url_fmt, $this->code, $this->api_key, $this->secret_key);
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @param mixed $api_key
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
    }

    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        return $this->secret_key;
    }

    /**
     * @param mixed $secret_key
     */
    public function setSecretKey($secret_key)
    {
        $this->secret_key = $secret_key;
    }

    /**
     * @return string
     */
    public function getUrlFmt(): string
    {
        return $this->url_fmt;
    }

    /**
     * @param string $url_fmt
     */
    public function setUrlFmt(string $url_fmt)
    {
        $this->url_fmt = $url_fmt;
    }

    /**
     * @return Result
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function load(): Result
    {
        $response = $this->httpClient->request($this->method, $this->getHttpUrl());
        $body = $response->getBody();
        $data = \GuzzleHttp\json_decode((string)$body);
        if (!empty($data->access_token)) {

            $this->result->setModel($data);
            ## 缓存 token信息 8450496baab78343b96612bcced64174
            Cache::put($this->cache_key, $this->result, floor($data->expires_in / 60));
        }

        return $this->result;
    }


}