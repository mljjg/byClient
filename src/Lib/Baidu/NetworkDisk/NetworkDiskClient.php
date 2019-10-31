<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 15:01
 */

namespace Jjg\Lib\Baidu\NetworkDisk;


use Jjg\Lib\Baidu\NetworkDisk\Exceptions\PathException;
use Jjg\Lib\Baidu\NetworkDisk\Requests\AccessTokenRequest;
use Jjg\Lib\Baidu\NetworkDisk\Requests\CodeRequest;
use Jjg\Lib\Baidu\NetworkDisk\Requests\CreateFileRequest;
use Jjg\Lib\Baidu\NetworkDisk\Requests\MakeDirRequest;
use Jjg\Lib\Baidu\NetworkDisk\Requests\PreCreateRequest;
use Jjg\Lib\Baidu\NetworkDisk\Requests\ShardUploadRequest;


class NetworkDiskClient
{
    private $app_id;
    private $api_key;
    private $secret_key;

    /**
     * @var \GuzzleHttp\Client
     */
    private $httpClient;

    public function __construct()
    {
        $this->app_id = config('netdisk.app_id');
        $this->api_key = config('netdisk.api_key');
        $this->secret_key = config('netdisk.secret_key');
        $this->httpClient = new \GuzzleHttp\Client();
    }

    /**
     *  server端:获取code
     * https://developer.baidu.com/newwiki/dev-wiki/kai-fa-wen-dang.html?t=1557733846879
     */
    public function getServerCode()
    {
        $codeRequest = new CodeRequest();
        $codeRequest->setClientId($this->api_key);

        return $codeRequest->getHttpUrl();

    }


    /**
     * @param $code
     * @return Result
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAccessToken($code)
    {
        $accessTokenRequest = new AccessTokenRequest();
        $accessTokenRequest->setCode($code);
        $accessTokenRequest->setApiKey($this->api_key);
        $accessTokenRequest->setSecretKey($this->secret_key);
        return  $accessTokenRequest->load();

    }


    /**
     * @param $localPath
     * @param $remotePath
     * @return Result
     * @throws PathException
     */
    public function preCreate($localPath, $remotePath)
    {
        $preCreateRequest = new PreCreateRequest($localPath, $remotePath);
        return $preCreateRequest->load();

    }

    /**
     * https://pan.baidu.com/union/document/upload#%E5%88%86%E7%89%87%E4%B8%8A%E4%BC%A0
     * @param $localPath
     * @param $remotePath
     * @param $uploadId
     * @param int $partSeq
     *
     * {
     * "md5": "55142edf74727f4a80cfd86c4d80be4f",
     * "request_id": 8383464737849269312
     * }
     * @return Result
     * @throws Exceptions\ShardUploadException
     */
    public function shardUpload($localPath, $remotePath, $uploadId, $partSeq = 0)
    {
        $shardUploadRequest = new ShardUploadRequest($localPath, $remotePath, $uploadId, $partSeq);

        return $shardUploadRequest->load();

    }

    /**
     * 创建文件
     * @param $localPath
     * @param $remotePath
     * @param $uploadId
     * @param $blockList
     *
     * @return Result
     * @throws PathException
     */
    public function createFile($localPath, $remotePath, $uploadId, $blockList)
    {
        $createFileRequest = new CreateFileRequest($localPath, $remotePath, $blockList, $uploadId);
        return $createFileRequest->load();
    }

    /**
     * 创建目录
     * @param $relativePath
     * @param null $remoteRootDir
     * @return Result
     * @throws PathException
     */
    public function makeDir($relativePath, $remoteRootDir = null): Result
    {
        $remoteRootDir = $remoteRootDir == null ? config('netdisk.remote.root') : $remoteRootDir;
        if ($lastRootChar = substr($remoteRootDir, -1)) {
            if ($lastRootChar != '/')
                $remoteRootDir .= '/';
        }

        if ($lastChar = substr($relativePath, -1)) {
            if ($lastChar != '/')
                $relativePath .= '/';
        }

        $remotePath = $relativePath == '/' ? $remoteRootDir : $remoteRootDir . $relativePath;

        $makeDirRequest = new MakeDirRequest($remotePath);

        return $makeDirRequest->load();

    }

}
