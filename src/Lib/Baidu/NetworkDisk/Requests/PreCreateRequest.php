<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 16:32
 */

namespace Jjg\Lib\Baidu\NetworkDisk\Requests;


use Jjg\Lib\Baidu\NetworkDisk\Exceptions\PathException;
use Jjg\Lib\Baidu\NetworkDisk\Responses\PreCreateResponse;

class PreCreateRequest extends Request
{

    private $url_fmt = 'https://pan.baidu.com/rest/2.0/xpan/file?method=precreate&access_token=%s';

    private $local_path;
    private $remote_path;

    public function __construct($localPath, $remotePath)
    {
        parent::__construct();
        $this->local_path = $localPath;
        $this->remote_path = $remotePath;

    }

    public function getUrl()
    {
        return sprintf($this->url_fmt, $this->access_token);
    }

    /**
     * @return float|int
     */
    private function defaultMaxLen()
    {
        return 4 * 1024 * 1024; # 4M
    }

    /**
     * 文件分片
     *
     * @param $path
     * @param float|int $maxLen
     * @return array
     */
    public function blockList($path, $maxLen = null): array
    {
        $blackList = [];
        $size = filesize($path);
        $maxLen = $maxLen ?: $this->defaultMaxLen(); # 4M

        for ($offset = 0; $offset <= $size; $offset += $maxLen) {
            $content_str = file_get_contents($path, false, null, $offset, $maxLen);

            $this->saveTmpFile($path, count($blackList), $content_str);

            $blackList[] = md5($content_str);
        }

        return $blackList;
    }


    /**
     * @return PreCreateResponse|\Psr\Http\Message\ResponseInterface
     * @throws PathException
     */
    public function load(): PreCreateResponse
    {
        if (!is_file($this->local_path) || !is_readable($this->local_path)) {
            throw new PathException('本地文件不存在或者不可读');
        }
        # 文件大小
        $size = filesize($this->local_path);
        # 是否为目录
        $isDir = is_dir($this->local_path) ? '1' : '0';

        $localFileBaseName = pathinfo($this->local_path, PATHINFO_BASENAME);

        $url = $this->getUrl();

        $blockList = $this->blockList($this->local_path);

        // 非必传字段，如果没有，就不要带上这个参数，不能传空或者null
        $form_params = [
            'path' => $this->remote_path . $localFileBaseName // string	是	上传后使用的文件绝对路径
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
//            'query' => ['method' => 'precreate', 'access_token' => $token],
//            'headers' => [
//                'User-Agent' => 'pan.baidu.com',
//            ]
        ]);

        $body = $response->getBody();
        $data = \GuzzleHttp\json_decode((string)$body);

        $response = new PreCreateResponse($data->path ?? $form_params['path'], $data->uploadid ?? '', $data->return_type ?? null, $data->block_list ?? [], $data->errno, $data->request_id);

        return $response;

    }
}
