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
use Jjg\Lib\Baidu\NetworkDisk\Result;

class ShardUploadRequest extends Request
{
    //https://pan.baidu.com/union/document/upload#%E5%88%86%E7%89%87%E4%B8%8A%E4%BC%A0

    private $local_path;

    private $uploaded_path;

    private $upload_id;

    private $part_seq = 0;

    public function __construct($localPath, $remotePath, $uploadId, $partSeq)
    {
        parent::__construct();
        $this->local_path = $localPath;
        $this->uploaded_path = $remotePath;
        $this->upload_id = $uploadId;
        $this->part_seq = $partSeq;

        $this->url_fmt = 'https://d.pcs.baidu.com/rest/2.0/pcs/superfile2?method=upload&access_token=%s&type=tmpfile&path=%s&uploadid=%s&partseq=%s';

    }

    public function getHttpUrl()
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
     * @return Result
     * @throws ShardUploadException
     */
    public function load(): Result
    {
        $file = $this->extractTmpFile($this->local_path, $this->part_seq);
        $url = $this->getHttpUrl();
        $response = $this->httpClient->post($url, [
            'multipart' => [['name' => 'file', 'contents' => $file]],
        ]);

        $body = $response->getBody();
        $data = \GuzzleHttp\json_decode((string)$body);

        if (empty($data->md5)) {
            throw new ShardUploadException('分片上传失败');
        }

        # 删除临时文件
        $this->deleteTmpFile($this->local_path, $this->part_seq);

        $this->result->succeed($data);

        return $this->result;
    }

    /**
     * 删除临时文件
     * @param $path
     * @param $partSeq
     * @return bool
     */
    private function deleteTmpFile($path, $partSeq)
    {
        return unlink(storage_path('tmp/' . pathinfo($path, PATHINFO_BASENAME) . '_' . $partSeq));
    }

    /**
     * 读取临时文件：文件流
     * @param $path
     * @param $partSeq
     * @return bool|resource
     */
    private function extractTmpFile($path, $partSeq)
    {
        return fopen(storage_path('tmp/' . pathinfo($path, PATHINFO_BASENAME) . '_' . $partSeq), 'rb');
    }

}
