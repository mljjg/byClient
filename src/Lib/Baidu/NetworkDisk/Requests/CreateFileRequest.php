<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 18:20
 */

namespace Jjg\Lib\Baidu\NetworkDisk\Requests;

use Jjg\Lib\Baidu\NetworkDisk\Exceptions\PathException;
use Jjg\Lib\Baidu\NetworkDisk\Result;

class CreateFileRequest extends Request
{
    private $local_path;

    private $remote_path;

    /**
     * @var array
     */
    private $block_list = [];

    private $upload_id;

    /**
     * CreateFileRequest constructor.
     * @param $local_path
     * @param $remote_path
     * @param array $block_list
     * @param $upload_id
     */
    public function __construct($local_path, $remote_path, array $block_list, $upload_id)
    {
        parent::__construct();
        $this->local_path = $local_path;
        $this->remote_path = $remote_path;
        $this->block_list = $block_list;
        $this->upload_id = $upload_id;
        $this->url_fmt = 'https://pan.baidu.com/rest/2.0/xpan/file?method=create&access_token=%s';
        $this->method = 'POST';
    }

    public function getHttpUrl()
    {
        return sprintf($this->url_fmt, $this->access_token);
    }

    /**
     * @return Result
     * @throws PathException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function load(): Result
    {
        $url = $this->getHttpUrl();
        $form_params = [
            'path' => $this->remote_path // path	string	是	上传后使用的文件绝对路径
            , 'size' => strval(filesize($this->local_path)) // size	string	是	文件或目录的大小，必须要和文件真实大小保持一致
            , 'isdir' => 0 //string	是	是否目录，0 文件、1 目录
            , 'rtype' => 3 //文件命名策略，默认0 。0 为不重命名，返回冲突；1 为只要path冲突即重命名；2 为path冲突且block_list不同才重命名；3 为覆盖
            , 'uploadid' => $this->upload_id //string	否	上传id (创建文件必传，创建目录非必传)
            , 'block_list' => \GuzzleHttp\json_encode($this->block_list) //json array	是	文件各分片MD5的json串 (创建文件必传，创建目录非必传)
        ];

        $response = $this->httpClient->request($this->method, $url, [
            'form_params' => $form_params,
        ]);

        $body = $response->getBody();
        $data = \GuzzleHttp\json_decode((string)$body);

        return $this->resultAnalysis($data);
    }

    /**
     * @param $data
     * @return Result
     * @throws PathException
     */
    private function resultAnalysis($data)
    {
        if (isset($data->errno)) {
            $result = new Result();
            $errno = $data->errno;
            switch ($errno) {
                case 0:
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
                    break;
            }
        } else {
            throw new PathException('创建目录失败');
        }
        return $result;

    }

}
