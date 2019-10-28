<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 18:20
 */

namespace Jjg\Lib\Baidu\NetworkDisk\Requests;


use Jjg\Lib\Baidu\NetworkDisk\Responses\CreateFileResponse;
use Mockery\Exception;

class CreateFileRequest extends Request
{
    private $url_fmt = 'https://pan.baidu.com/rest/2.0/xpan/file?method=create&access_token=%s';

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
    }

    private function getUrl()
    {
        return sprintf($this->url_fmt, $this->access_token);
    }

    /**
     * @return CreateFileResponse|\Psr\Http\Message\ResponseInterface
     */
    public function load()
    {
        $url = $this->getUrl();
        $form_params = [
            'path' => $this->remote_path // path	string	是	上传后使用的文件绝对路径
            , 'size' => strval(filesize($this->local_path)) // size	string	是	文件或目录的大小，必须要和文件真实大小保持一致
            , 'isdir' => 0 //string	是	是否目录，0 文件、1 目录
            , 'autoinit' => 1 //int	是	固定值1
            , 'rtype' => 3 //文件命名策略，默认0 。0 为不重命名，返回冲突；1 为只要path冲突即重命名；2 为path冲突且block_list不同才重命名；3 为覆盖
            , 'uploadid' => $this->upload_id //string	否	上传id (创建文件必传，创建目录非必传)
            , 'block_list' => json_encode($this->block_list) //json array	是	文件各分片MD5的json串 (创建文件必传，创建目录非必传)
        ];
        $response = $this->httpClient->post($url, [
            'form_params' => $form_params,
//            'query' => ['access_token' => $this->access_token, 'method' => 'create'],
        ]);

        $body = $response->getBody();

        $data = \GuzzleHttp\json_decode((string)$body);

        $response = new CreateFileResponse($data->fs_id ?? '', $data->md5 ?? '', $data->server_filename ?? '', $data->category ?? '', $data->path ?? '', $data->size ?? '', $data->ctime ?? 0, $data->mtime ?? 0, $data->isdir ?? 0, $data->errno ?? 0, $data->name ?? '');

        return $response;
    }

}
