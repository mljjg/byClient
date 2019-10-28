<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 15:02
 */

namespace Jjg\Lib\Baidu\NetworkDisk\Requests;


class AccessTokenRequest
{
    private $code;
    private $api_key;
    private $secret_key;
    private $url_fmt = 'https://openapi.baidu.com/oauth/2.0/token?grant_type=authorization_code&code=%s&client_id=%s&client_secret=%s&redirect_uri=oob';

    private $method = 'GET';

    public function __construct($code, $api_key, $secret_key)
    {
        $this->code = $code;
        $this->api_key = $api_key;
        $this->secret_key = $secret_key;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return sprintf($this->url_fmt, $this->code, $this->api_key, $this->secret_key);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

}