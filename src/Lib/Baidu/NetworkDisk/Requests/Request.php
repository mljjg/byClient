<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 17:22
 */

namespace Jjg\Lib\Baidu\NetworkDisk\Requests;


use Illuminate\Support\Facades\Cache;

class Request
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;
    protected $access_token;
    protected $cache_key = 'baiduyun.access_token.info';

    public function __construct()
    {
        $this->httpClient = new \GuzzleHttp\Client();

        try {
            $this->access_token = $this->getToken();
        } catch (\Exception $exception) {
            \Log::info("服务端未权限");
        }
    }

    /**
     * @return null
     * @throws \Exception
     */
    public function getToken()
    {
        $result = Cache::get($this->cache_key);
        $token = $result->getData()['access_token'] ?? null;

        if ($token) {
            return $token;
        } else {
            throw new \Exception('Please execute script [by:info] authorization');
        }

    }

    /**
     * 保存临时文件
     * @param $path
     * @param $partSeq
     * @param $content_str
     * @return bool|int
     */
    protected function saveTmpFile($path, $partSeq, $content_str)
    {
        # 创建临时文件夹
        if (!is_dir(storage_path('tmp'))) {
            mkdir(storage_path('tmp'), true, 0777);
        }

        return file_put_contents(storage_path('tmp/' . pathinfo($path, PATHINFO_BASENAME) . '_' . $partSeq), $content_str);
    }

    /**
     * 读取临时文件：文件流
     * @param $path
     * @param $partSeq
     * @return bool|resource
     */
    protected function extractTmpFile($path, $partSeq)
    {
        return fopen(storage_path('tmp/' . pathinfo($path, PATHINFO_BASENAME) . '_' . $partSeq), 'rb');
    }

    /**
     * 删除临时文件
     * @param $path
     * @param $partSeq
     * @return bool
     */
    protected function deleteTmpFile($path, $partSeq)
    {
        return unlink(storage_path('tmp/' . pathinfo($path, PATHINFO_BASENAME) . '_' . $partSeq));
    }

}