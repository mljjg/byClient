<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 17:22
 */

namespace Jjg\Lib\Baidu\NetworkDisk\Requests;


use Illuminate\Support\Facades\Cache;
use Jjg\Lib\Baidu\NetworkDisk\Result;

class Request
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    protected $access_token;

    protected $cache_key = 'baiduyun.access_token.info';

    /**
     * @var string
     */
    protected $url_fmt = '';
    /**
     * @var array
     */
    protected $params = [];

    protected $result;

    protected $method = 'GET';

    public function __construct()
    {
        $this->httpClient = new \GuzzleHttp\Client();
        $this->result = new Result();
        try {
            $this->access_token = $this->getToken();
        } catch (\Exception $exception) {
            \Log::info("服务端未权限");
        }
    }

    public function getHttpUrl()
    {
        return sprintf($this->url_fmt, $this->access_token);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }


    /**
     * @return null
     * @throws \Exception
     */
    public function getToken()
    {
        $result = Cache::get($this->cache_key);
        $token = $result->getModel()->access_token ?? null;

        if ($token) {
            return $token;
        } else {
            throw new \Exception('Please execute script [by:info] authorization');
        }

    }


    /**
     * @return Result
     */
    public function load(): Result
    {

        return $this->result;
    }

}