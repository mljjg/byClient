<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/17
 * Time: 13:40
 */

namespace Jjg\Lib\Clients;


use Illuminate\Support\Facades\Cache;

class BaiduYun
{
    private $app_id = '17332190';
    private $api_key = 'ukp6O2V0Pzvxfkaj9qTYWGmp';
    private $secret_key = 'hG7ZbE8PS7PGMpAvCZGqaPbplRCfV4oP';
    protected $superFileUrl = 'https://d.pcs.baidu.com/rest/2.0/pcs/superfile2';
    protected $preCreateUrl = 'https://pan.baidu.com/rest/2.0/xpan/file?method=precreate';
    protected $createUrl = 'https://pan.baidu.com/rest/2.0/xpan/file?method=create';

    /**
     * @var \GuzzleHttp\Client
     */
    private $httpClient;
    private $cackeKeyToken = 'baidu.access_token.info';
    private $accessToken;
    private $size;
    private $isDir = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->httpClient = new \GuzzleHttp\Client();
        try {
            $this->accessToken = $this->getToken();
        } catch (\Exception $exception) {
            \Log::info("服务端未权限");
        }

    }

    /**
     *  server端:获取code
     * https://developer.baidu.com/newwiki/dev-wiki/kai-fa-wen-dang.html?t=1557733846879
     */
    public function getCodeUrl()
    {
        $url = 'https://openapi.baidu.com/oauth/2.0/authorize?response_type=code&client_id=%s&redirect_uri=oob&scope=basic,netdisk&display=page&qrcode=1&force_login=1';

        $url = sprintf($url, $this->api_key);
        return $url;
    }


    /**
     * https://pan.baidu.com/union/document/entrance#%E6%8E%88%E6%9D%83%E6%B5%81%E7%A8%8B
     * server端:使用code换取access_token
     * @param $code
     * @return array|mixed
     */
    public function getAccessToken($code)
    {
        $result = [];
        $url = 'https://openapi.baidu.com/oauth/2.0/token?grant_type=authorization_code&code=%s&client_id=%s&client_secret=%s&redirect_uri=oob';
        $url = sprintf($url, $code, $this->api_key, $this->secret_key);
        try {
            $response = $this->httpClient->get($url);
            $body = $response->getBody();
            $data = json_decode((string)$body);
            if (!empty($data->access_token)) {
                $result = ['access_token' => $data->access_token, 'expires_in' => $data->expires_in ?? 0, 'scope' => $data->scope ?? '', 'refresh_token' => $data->refresh_token ?? ''];
                ## 缓存 token信息
                Cache::put($this->cackeKeyToken, $result, floor($result['expires_in'] / 60));
            }
            \Log::info('百度网盘 api_key：' . $this->api_key . ',access token:' . json_encode($data));
        } catch (\Exception $exception) {
            $result = ['code' => $code, 'error_code' => $exception->getCode(), 'error_message' => $exception->getMessage()];
        }

        return $result;
    }

    /**
     * @return null
     * @throws \Exception
     */
    public function getToken()
    {
        $result = Cache::get($this->cackeKeyToken);
        $token = $result['access_token'] ?? null;

        if ($token) {
            return $token;
        } else {
            throw new \Exception('Please execute script [by:info] authorization');
        }

    }

    /**
     * 获取用户信息
     */
    public function uinfo()
    {
        $url = 'https://pan.baidu.com/rest/2.0/xpan/nas?method=uinfo';

        $response = $this->httpClient->get($url);
        $body = $response->getBody();
        $data = json_decode((string)$body);
        return $data;
    }

    //https://pan.baidu.com/union/document/upload#%E9%80%9A%E7%94%A8%E5%8F%82%E6%95%B0
    //文件上传分为三个阶段：预上传、分片上传、创建文件。只有完成这三步，才能将文件上传到网盘

    /**
     * 预上传
     * @param $path
     * @param string $by_path
     * @return mixed
     * @throws \Exception
     */
    public function preCreate($path, $by_path = '/暮来/')
    {
        $size = filesize($path);
        $this->size = $size;
        $base_name = pathinfo($path, PATHINFO_BASENAME);

        $isDir = is_dir($path) ? '1' : '0';
        $this->isDir = $isDir;
        $token = $this->getToken();
        $url = 'https://pan.baidu.com/rest/2.0/xpan/file?method=precreate&access_token=' . $token;

        $blockList = $this->blockList($path);
        // 非必传字段，如果没有，就不要带上这个参数，不能传空或者null
        $form_params = [
            'path' => $by_path . $base_name // string	是	上传后使用的文件绝对路径
            , 'size' => $size // string	是	文件或目录的大小，单位B
            , 'isdir' => $isDir //string	是	是否目录，0 文件、1 目录
            , 'autoinit' => 1 //int	是	固定值1
            , 'rtype' => 3 //文件命名策略，默认0 。0 为不重命名，返回冲突；1 为只要path冲突即重命名；2 为path冲突且block_list不同才重命名；3 为覆盖
//            , 'uploadid' => $this->uploadId() //string	否	上传id
            , 'block_list' => json_encode($blockList) //json array	是	文件各分片MD5的json串
//            , 'content-md5' => current($blockList)//string	否	文件MD5
//            , 'slice-md5' => null //string	否	文件校验段的MD5，校验段对应文件前256KB
            , 'local_ctime' => time()//string	否	客户端创建时间， 默认为当前时间戳
            , 'local_mtime' => time()//string	否	客户端修改时间，默认为当前时间戳
        ];

        $response = $this->httpClient->post($url, [
            'form_params' => $form_params,
            'query' => ['method' => 'precreate', 'access_token' => $token],
            'headers' => [
                'User-Agent' => 'pan.baidu.com',
            ]
        ]);

        $body = $response->getBody();
        $data = json_decode((string)$body, true);
        if (empty($data['path'])) {
            $data['path'] = $form_params['path'];
        }

        return $data;
    }

    /**
     * https://pan.baidu.com/union/document/upload#%E5%88%86%E7%89%87%E4%B8%8A%E4%BC%A0
     * @param $path
     * @param $uploadedPath
     * @param $uploadid
     * @param int $partseq
     *
     * @return mixed
     * {
     * "md5": "55142edf74727f4a80cfd86c4d80be4f",
     * "request_id": 8383464737849269312
     * }
     */
    public function shardUpload($path, $uploadedPath, $uploadid, $partseq = 0)
    {

        $file = $this->filePart($path, $partseq);

        $url = "https://d.pcs.baidu.com/rest/2.0/pcs/superfile2";
        $response = $this->httpClient->post($url, [
            'multipart' => [['name' => 'file', 'contents' => $file]],
            'query' => ['access_token' => $this->accessToken, 'method' => 'upload', 'type' => 'tmpfile', 'path' => $uploadedPath, 'uploadid' => $uploadid, 'partseq' => $partseq],
        ]);

        $body = $response->getBody();
        $data = json_decode((string)$body, true);

        return $data;

    }

    /**
     * 文件上传ID
     * @return string
     */
    private function uploadId()
    {
        return base_convert(uniqid(), 16, 10);
    }

    /**
     * @return float|int
     */
    private function maxLen()
    {
        return 4 * 1024 * 1024; # 4M
    }

    /**
     * @param $path
     * @return array
     */
    private function blockList($path): array
    {
        $blackList = [];
        $size = filesize($path);
        $maxLen = $this->maxLen(); # 4M
        $offsets = [];
        for ($offset = 0; $offset <= $size; $offset += $maxLen) {
            $content_str = file_get_contents($path, false, null, $offset, $maxLen);
            $file_md5 = md5($content_str);
            file_put_contents($path . '_' . count($blackList), $content_str);
            file_put_contents(storage_path($file_md5), $content_str);
            $offsets[] = ['offset' => $offset, 'size' => $size];
            $blackList[] = md5($content_str);
        }

        \Log::info('分片文件的offset 集合：' . var_export($offsets, true));

        return $blackList;
    }

    /**
     * @param $path
     * @param $partseq
     * @return string
     */
    private function filePart($path, $partseq)
    {
        $maxLen = $this->maxLen(); # 4M
        $offset = $maxLen * $partseq;
        $fp = fopen($path . '_' . $partseq, 'rb');

        return $fp;
    }

    /**
     * @param $uploadedPath
     * @param $uploadId
     * @param $blockList
     * @return mixed
     */
    public function createFile($uploadedPath, $uploadId, $blockList)
    {
        $url = $this->createUrl . '&access_token=' . $this->accessToken;
        $form_params = [
            'path' => $uploadedPath // path	string	是	上传后使用的文件绝对路径
            , 'size' => strval($this->size) // size	string	是	文件或目录的大小，必须要和文件真实大小保持一致
            , 'isdir' => $this->isDir //string	是	是否目录，0 文件、1 目录
            , 'autoinit' => 1 //int	是	固定值1
            , 'rtype' => 3 //文件命名策略，默认0 。0 为不重命名，返回冲突；1 为只要path冲突即重命名；2 为path冲突且block_list不同才重命名；3 为覆盖
            , 'uploadid' => $uploadId //string	否	上传id (创建文件必传，创建目录非必传)
            , 'block_list' => json_encode($blockList) //json array	是	文件各分片MD5的json串 (创建文件必传，创建目录非必传)
        ];
        $response = $this->httpClient->post($url, [
            'form_params' => $form_params,
            'query' => ['access_token' => $this->accessToken, 'method' => 'create'],
        ]);

        $body = $response->getBody();
        $data = json_decode((string)$body, true);

        return $data;
    }

    private function AddCommonArg($params)
    {
        ## 缓存里获取

        $this->accessToken = $this->getToken();// '21.5def4efe2f7f88d89363bc3f461a64dc.2592000.1573884567.643027510-17332190';
        $params['access_token'] = $this->accessToken;
        return $params;
    }


    public function fileList()
    {
        $url = 'https://pan.baidu.com/rest/2.0/xpan/file?method=list';
        $form_params = $this->AddCommonArg(['method' => 'list']);
        $response = $this->httpClient->get($url, [
            'query' => $form_params
        ]);

        $body = $response->getBody();
        $data = json_decode((string)$body);
        return $data;
    }

    public function userInfo()
    {
        $url = 'https://pan.baidu.com/rest/2.0/xpan/nas';
        $form_params = $this->AddCommonArg(['method' => 'uinfo']);

        $response = $this->httpClient->get($url, [
            'query' => $form_params
        ]);

        $body = $response->getBody();
        $data = json_decode((string)$body);
        return $data;
    }


}