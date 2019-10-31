<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 15:54
 */

namespace Jjg\Lib\Baidu\NetworkDisk;


use Jjg\Lib\Baidu\NetworkDisk\Responses\Response;

class Result
{

    /**
     * 是否成功
     * @var bool
     */
    private $success = true;

    /**
     * @var int
     */
    private $code = 100000;
    /**
     * 业务信息
     * @var string
     */
    private $msg = '操作成功';

    /**
     * @var mixed
     */
    private $model;

    /**
     * the Response content must be a string or object implementing __toString()
     * @return string
     */
    public function __toString()
    {
        return \GuzzleHttp\json_encode($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'code' => $this->code,
            'msg' => $this->msg,
            'success' => $this->success,
            'model' => $this->model,
        ];
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     * @return Result
     */
    public function setSuccess(bool $success)
    {
        $this->success = $success;
        return $this;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @return Result
     */
    public function setCode(int $code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getMsg(): string
    {
        return $this->msg;
    }

    /**
     * @param string $msg
     * @return Result
     */
    public function setMsg(string $msg)
    {
        $this->msg = $msg;
        return $this;
    }


    /**
     * @param $code
     * @param $msg
     * @return Result
     */
    public function failed($msg = null, $code = null)
    {
        $this->code = $code ? $code : 100001;
        $this->msg = $msg ?: $this->getMsg();
        $this->setSuccess(false);
        return $this;
    }

    /**
     * 成功
     * @param null $data
     * @param $msg
     * @return Result
     */
    public function succeed($data = null, $msg = null)
    {
        $this->setModel($data);
        $this->setSuccess(true);
        $this->msg = $msg ?: $this->getMsg();
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param  $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

}
