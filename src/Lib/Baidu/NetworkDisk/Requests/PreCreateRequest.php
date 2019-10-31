<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 16:32
 */

namespace Jjg\Lib\Baidu\NetworkDisk\Requests;


use Jjg\Lib\Baidu\NetworkDisk\Exceptions\PathException;
use Jjg\Lib\Baidu\NetworkDisk\Result;

class PreCreateRequest extends Request
{
    private $local_path;

    private $remote_path;

    public function __construct($localPath, $remotePath)
    {
        parent::__construct();
        $this->local_path = $localPath;
        $this->remote_path = $remotePath;
        $this->url_fmt = 'https://pan.baidu.com/rest/2.0/xpan/file?method=precreate&access_token=%s';
    }

    public function getHttpUrl()
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
     * 保存临时文件
     * @param $path
     * @param $partSeq
     * @param $content_str
     * @return bool|int
     */
    private function saveTmpFile($path, $partSeq, $content_str)
    {
        # 创建临时文件夹
        if (!is_dir(storage_path('tmp'))) {
            mkdir(storage_path('tmp'), true, 0777);
        }

        return file_put_contents(storage_path('tmp/' . pathinfo($path, PATHINFO_BASENAME) . '_' . $partSeq), $content_str);
    }


    /**
     * @return Result
     * @throws PathException
     */
    public function load(): Result
    {
        if (!is_file($this->local_path) || !is_readable($this->local_path)) {
            throw new PathException('本地文件不存在或者不可读');
        }
        # 文件大小
        $size = filesize($this->local_path);
        # 是否为目录
        $isDir = is_dir($this->local_path) ? '1' : '0';

        $localFileBaseName = pathinfo($this->local_path, PATHINFO_BASENAME);

        $url = $this->getHttpUrl();

        $blockList = $this->blockList($this->local_path);

        // 非必传字段，如果没有，就不要带上这个参数，不能传空或者null
        $form_params = [
            'path' => $this->remote_path . $localFileBaseName // string	是	上传后使用的文件绝对路径
            , 'size' => $size // string	是	文件或目录的大小，单位B
            , 'isdir' => $isDir //string	是	是否目录，0 文件、1 目录
            , 'autoinit' => 1 //int	是	固定值1
            , 'rtype' => 3 //文件命名策略，默认0 。0 为不重命名，返回冲突；1 为只要path冲突即重命名；2 为path冲突且block_list不同才重命名；3 为覆盖
//            , 'uploadid' => $this->uploadId() //string	否	上传id
            , 'block_list' => \GuzzleHttp\json_encode($blockList) //json array	是	文件各分片MD5的json串
//            , 'content-md5' => current($blockList)//string	否	文件MD5
//            , 'slice-md5' => null //string	否	文件校验段的MD5，校验段对应文件前256KB
            , 'local_ctime' => time()//string	否	客户端创建时间， 默认为当前时间戳
            , 'local_mtime' => time()//string	否	客户端修改时间，默认为当前时间戳
        ];

        $response = $this->httpClient->post($url, [
            'form_params' => $form_params,
        ]);

        $body = $response->getBody();
        $data = \GuzzleHttp\json_decode((string)$body);

        return $this->resultAnalysis($data, $data->path ?? $form_params['path']);

    }


    /**
     * @param $data
     * @param $path
     * @return Result
     * @throws PathException
     */
    private function resultAnalysis($data, $path)
    {
        if (isset($data->errno)) {
            $result = $this->result;
            $errno = $data->errno;
            switch ($errno) {
                case 0:
                    if (empty($data->path)) {
                        $data->path = $path;
                    }

                    $result->succeed($data);
                    break;
                case -7:
                    $result->failed('目录(' . $this->remote_path . ')名错误或无权访问', $errno);
                    break;
                case -8:
                    $result->failed('目录(' . $this->remote_path . ')已存在', $errno);
                    break;
                case -10:
                    $result->failed('云端容量已满', $errno);
                    break;
                case  10:
                    $result->failed('创建文件的superfile失败', $errno);
                    break;
                case 2:
                    $result->failed('参数错误', $errno);
                    break;
                default:
                    $result->failed('其他未知错误', $errno);
                    $result->setModel($data);
                    break;
            }
        } else {
            throw new PathException('预上传失败');
        }
        return $result;

    }
}
