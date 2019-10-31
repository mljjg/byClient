<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 15:02
 */

namespace Jjg\Lib\Baidu\NetworkDisk\Requests;


class CodeRequest extends Request
{
    private $response_type = 'code';
    private $client_id = '';
    private $redirect_uri = 'oob';
    private $scope = 'basic,netdisk';
    private $display = 'page';
    private $qrcode = '1';
    private $force_login = '1';

    public function __construct()
    {
        parent::__construct();
        $this->method = 'GET';
        $this->url_fmt = 'https://openapi.baidu.com/oauth/2.0/authorize?response_type=%s&client_id=%s&redirect_uri=%s&scope=%s&display=%s&qrcode=%s&force_login=%s';
    }

    public function getHttpUrl()
    {
        return sprintf($this->url_fmt, $this->response_type, $this->client_id, $this->redirect_uri, $this->scope, $this->display, $this->qrcode, $this->force_login);
    }


    /**
     * @param string $client_id
     */
    public function setClientId(string $client_id)
    {
        $this->client_id = $client_id;
    }

}