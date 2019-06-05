<?php
/**
 * s3文件存储
 * Stephen
 */
require_once PATH_THIRD_LIB.'/SinaService/SinaStorageService/SinaStorageService.php';
class Tool_Storage
{
    private $_storage;
    private $_project;
    private $_accesskey;
    private $_secretkey;

    public function __construct($project='')
    {
        if ($project == 'mediaweibo')
        {
            $this->_project = "download.a.weibo.com";
            $this->_accesskey = "SINA00000000WEIBOEPS";
            $this->_secretkey = "E2ZCK0zEEtwrRnVtdCaPYS9IMhogpU3QdlzrmnqR";
        }
        else
        {
            $this->_project = "storage.verify.weibo.com";
            $this->_accesskey = "SINA00000000WBVERIFY";
            $this->_secretkey = "GgPI8wGaNFt3Dp1RtzXl59W6FbcL6VAEsFcFUjDT";
        }

        $this->_storage = SinaStorageService::getInstance($this->_project, $this->_accesskey, $this->_secretkey);
    }

    /*
     * 上传文件
     */
    public function upload($localfile, $filename, $type = "text/plain")
    {
        $result = '文件不存在';
        if (!file_exists($localfile))
        {
            return array('code' => 'M00004', 'data' => $result);
        }
        $file_content = file_get_contents($localfile);
        $file_length = filesize($localfile);

        //可自定义配置的CURLOPT
        $this->_storage->setCURLOPTs(array(CURLOPT_VERBOSE=>1));
        //设置使用验证方式
        $this->_storage->setAuth(true);

        //上传文件
        $res = $this->_storage->uploadFile($filename, $file_content, $file_length, $type, $result);

        if ($res)
        {
            return array('code' => 'A00006', 'data' => $result);
        }

        return array('code' => 'M00004', 'data' => $result);
    }

    /*
     * 获取文件url
     */

    public function getFileUrl($filename)
    {
        $result = '文件不存在';
        //可自定义配置的CURLOPT
        $this->_storage->setCURLOPTs(array(CURLOPT_VERBOSE=>1));
        //设置使用验证方式
        $this->_storage->setAuth(true);
        //设置IP访问权限,获取一个只允许以下IP访问的url
        //$this->_storage->setExtra("?ip=61.135.152.194");
        //设置下载用户的ip地址被限制在61.135.152.0/24网段，并在UNIX时间1175135000后生效
        //$this->_storage->setExtra("?ip=".time().",10.218.24.");

        $res = $this->_storage->getFileUrl($filename, $result);

        if ($res)
        {
            return array('code' => 'A00006', 'data' => $result);
        }
        return array('code' => 'M00004', 'data' => $result);
    }

    /*
     * 获取文件
     */

    public function getFile($filename)
    {
        $result = '文件不存在';
        //可自定义配置的CURLOPT
        $this->_storage->setCURLOPTs(array(CURLOPT_VERBOSE=>1));
        //设置使用验证方式
        $this->_storage->setAuth(True);
        //设置IP访问权限,获取一个只允许以下IP访问的url
        //$this->_storage->setExtra("?ip=61.135.152.194");
        //设置下载用户的ip地址被限制在61.135.152.0/24网段，并在UNIX时间1175135000后生效
        //$this->_storage->setExtra("?ip=".time().",10.218.24.");

        $res = $this->_storage->getFile($filename, $result);
        if ($res)
        {
            return array('code' => 'A00006', 'data' => $result);
        }
        return array('code' => 'M00004', 'data' => $result);
    }

    /*
 * 上传文件
 */
    public function upload_from_variable($filename, &$file_content, $type = "image/gif")
    {
        $result = '文件不存在';

        //$file_content = file_get_contents($filename);
        $file_length = strlen($file_content);

        //可自定义配置的CURLOPT
        $this->_storage->setCURLOPTs(array(CURLOPT_VERBOSE=>1));
        //设置使用验证方式
        $this->_storage->setAuth(true);

        //上传文件
        $res = $this->_storage->uploadFile($filename, $file_content, $file_length, $type, $result);

        if ($res)
        {
            return array('code' => 'A00006', 'data' => $result);
        }

        return array('code' => 'M00004', 'data' => $result);
    }
}