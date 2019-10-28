<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 18:51
 */

namespace Jjg\Lib\Baidu\NetworkDisk\Responses;


class CreateFileResponse
{
    //https://pan.baidu.com/union/document/upload#%E5%88%9B%E5%BB%BA%E6%96%87%E4%BB%B6
    private $fs_id;//fs_id	uint64	文件在云端的唯一标识ID
    private $md5;//md5	string	文件的MD5，只有提交文件时才返回，提交目录时没有该值
    private $server_filename; //	string	文件名
    private $category;    //int	分类类型, 1 视频 2 音频 3 图片 4 文档 5 应用 6 其他 7 种子
    private $path;    //string	上传后使用的文件绝对路径
    private $size;    //uint64	文件大小，单位B
    private $ctime; //uint64	文件创建时间
    private $mtime;//uint64	文件修改时间
    /**
     * @var int
     *  //    int    是否目录，0 文件、1 目录
     */
    private $isdir = 0;
    /**
     * @var int
     * //-7    文件或目录名错误或无权访问;-8    文件或目录已存在;-10    云端容量已满;10    创建文件的superfile失败
     */
    private $errno = 0;

    private $name;

    /**
     * CreateFileResponse constructor.
     * @param $fs_id
     * @param $md5
     * @param $server_filename
     * @param $category
     * @param $path
     * @param $size
     * @param $ctime
     * @param $mtime
     * @param $isdir
     * @param int $errno
     */
    public function __construct($fs_id, $md5, $server_filename, $category, $path, $size, $ctime, $mtime, $isdir, int $errno, $name)
    {
        $this->fs_id = $fs_id;
        $this->md5 = $md5;
        $this->server_filename = $server_filename;
        $this->category = $category;
        $this->path = $path;
        $this->size = $size;
        $this->ctime = $ctime;
        $this->mtime = $mtime;
        $this->isdir = $isdir;
        $this->errno = $errno;
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getFsId()
    {
        return $this->fs_id;
    }

    /**
     * @return mixed
     */
    public function getMd5()
    {
        return $this->md5;
    }

    /**
     * @return mixed
     */
    public function getServerFilename()
    {
        return $this->server_filename;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return mixed
     */
    public function getCtime()
    {
        return $this->ctime;
    }

    /**
     * @return mixed
     */
    public function getMtime()
    {
        return $this->mtime;
    }

    /**
     * @return int
     */
    public function getIsdir(): int
    {
        return $this->isdir;
    }

    /**
     * @return int
     */
    public function getErrno(): int
    {
        return $this->errno;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }



}