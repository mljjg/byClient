<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 17:19
 */

namespace Jjg\Lib\Baidu\NetworkDisk\Requests;


use Jjg\Lib\Baidu\NetworkDisk\Exceptions\ShardUploadException;
use Jjg\Lib\Baidu\NetworkDisk\Responses\ShardUploadResponse;

class ShardUploadRequest extends Request
{
    //https://pan.baidu.com/union/document/upload#%E5%88%86%E7%89%87%E4%B8%8A%E4%BC%A0

    private $local_path;

    private $uploaded_path;

    private $upload_id;

    private $part_seq = 0;

    private $url_fmt = 'https://d.pcs.baidu.com/rest/2.0/pcs/superfile2?method=upload&access_token=%s&type=tmpfile&path=%s&uploadid=%s&partseq=%s';

    public function __construct($localPath, $remotePath, $uploadId, $partSeq)
    {
        parent::__construct();
        $this->local_path = $localPath;
        $this->uploaded_path = $remotePath;
        $this->upload_id = $uploadId;
        $this->part_seq = $partSeq;

    }

    public function getUrl()
    {
        return sprintf($this->url_fmt, $this->access_token, $this->uploaded_path, $this->upload_id, $this->part_seq);
    }

    /**
     * @return int
     */
    public function getPartSeq(): int
    {
        return $this->part_seq;
    }

    /**
     * @param int $part_seq
     */
    public function setPartSeq(int $part_seq)
    {
        $this->part_seq = $part_seq;
    }


    /**
     * @return ShardUploadResponse
     * @throws ShardUploadException
     */
    public function load(): ShardUploadResponse
    {
        $file = $this->extractTmpFile($this->local_path, $this->part_seq);
        $url = $this->getUrl();
        $response = $this->httpClient->post($url, [
//            'form_params' => ['file' => $file],
            'multipart' => [['name' => 'file', 'contents' => $file]],
        ]);

        $body = $response->getBody();
        $data = \GuzzleHttp\json_decode((string)$body);

        if (empty($data->md5)) {
            throw new ShardUploadException('分片上传失败');
        }

        # 删除临时文件
        $this->deleteTmpFile($this->local_path, $this->part_seq);

        return new ShardUploadResponse($data->md5, $data->request_id ?? '');
    }

}
