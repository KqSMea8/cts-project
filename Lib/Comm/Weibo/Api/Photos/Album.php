<?php
/**
 * 相册接口SDK
 *
 * @package    photos
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     dengzheng<dengzheng@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Photos_album {
    const RESOURCE = 'photos/album';
    /**
     * 用户的照片数、相册数。
     */
    public static function counts(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url('photos', 'counts');
        $request = new Comm_Weibo_Api_Request_Platform($url,'GET');
        $request->add_rule('uid', 'int64');
        return $request;
    }
}