<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 17:38
 */

namespace Jjg\Lib\Baidu\NetworkDisk\Responses;


class ShardUploadResponse
{

    private $md5;

    private $request_id;

    public function __construct($md5, $request_id)
    {
        $this->md5 = $md5;
        $this->request_id = $request_id;
    }


    /**
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->request_id;
    }


    /**
     * @return mixed
     */
    public function getMd5()
    {
        return $this->md5;
    }




}