<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 17:47
 */

namespace Jjg\Lib\Baidu\NetworkDisk\Responses;


class PreCreateResponse
{
    private $remote_path;
    private $upload_id;
    private $return_type;
    private $block_list;
    private $errno;
    private $request_id;

    private $error_code;

    private $error_msg;

    private $errmsg;


    /**
     * PreCreateResponse constructor.
     * @param $remote_path
     * @param $upload_id
     * @param $return_type
     * @param $block_list
     * @param $errno
     * @param $request_id
     */
    public function __construct($remote_path, $upload_id, $return_type, $block_list, $errno, $request_id)
    {
        $this->remote_path = $remote_path;
        $this->upload_id = $upload_id;
        $this->return_type = $return_type;
        $this->block_list = $block_list;
        $this->errno = $errno;
        $this->request_id = $request_id;
    }

    /**
     * @return mixed
     */
    public function getRemotePath()
    {
        return $this->remote_path;
    }

    /**
     * @param mixed $remote_path
     */
    public function setRemotePath($remote_path)
    {
        $this->remote_path = $remote_path;
    }

    /**
     * @return mixed
     */
    public function getUploadId()
    {
        return $this->upload_id;
    }

    /**
     * @param mixed $upload_id
     */
    public function setUploadId($upload_id)
    {
        $this->upload_id = $upload_id;
    }

    /**
     * @return mixed
     */
    public function getReturnType()
    {
        return $this->return_type;
    }

    /**
     * @param mixed $return_type
     */
    public function setReturnType($return_type)
    {
        $this->return_type = $return_type;
    }

    /**
     * @return mixed
     */
    public function getBlockList()
    {
        return $this->block_list;
    }

    /**
     * @param mixed $block_list
     */
    public function setBlockList($block_list)
    {
        $this->block_list = $block_list;
    }

    /**
     * @return mixed
     */
    public function getErrno()
    {
        return $this->errno;
    }

    /**
     * @param mixed $errno
     */
    public function setErrno($errno)
    {
        $this->errno = $errno;
    }

    /**
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->request_id;
    }

    /**
     * @param mixed $request_id
     */
    public function setRequestId($request_id)
    {
        $this->request_id = $request_id;
    }


}