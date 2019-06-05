<?php
/**
 * 文件接口
 *
 * @package    api
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_File{
    
    const RESOURCE = 'file';
    
    //通过私信转发或者分享私信附件
    const CONNECT_TIMEOUT_ATTACHMENT_REPOST = 10000;
    const TIMEOUT_ATTACHMENT_REPOST = 10000;
    
    //获取上传文件需要的签名
    const CONNECT_TIMEOUT_ATTACHMENT_UPLOAD_SIGN = 5000;
    const TIMEOUT_ATTACHMENT_UPLOAD_SIGN = 5000;
    
    //通知微盘文件上传完成
    const CONNECT_TIMEOUT_ATTACHMENT_UPLOAD_BACK = 180000;
    const TIMEOUT_ATTACHMENT_UPLOAD_BACK = 180000;
    
    //获取一个已上传的附件的信息
    const CONNECT_TIMEOUT_ATTACHMENT_INFO = 5000;
    const TIMEOUT_ATTACHMENT_INFO = 5000;
    
    /**
     * 获取单个文件信息接口
     */
    public static function info() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'info');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('fid', 'int', TRUE);
        
        return $request;
    }
    /**
     * 私信中的附件上传接口
     */
    public static function msgupload() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'msgupload');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('dir_id', 'int', TRUE);
        $request->add_rule('force', 'int', FALSE);
        $request->add_rule('touid', 'int64', TRUE);
        $request->add_rule('file', 'filepath', TRUE);
        
        return $request;
    }
    
    /**
     * 获取文件上传TOKEN
     */
    public static function attachment_get_token() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'attachment/get_token');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('file_name', 'string', TRUE);
        $request->add_rule('sha1', 'string', FALSE);
        
        return $request;
    }
    
    /**
     * 确认文件上传结束
     */
    public static function attachment_status() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'attachment/status');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('upload_key', 'string', TRUE);
        
        return $request;
    }
    
    /**
     * 获取一个已上传的附件的信息
     */
    public static function attachment_info() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'attachment/info');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('fid', 'int64', TRUE);
        $request->set_request_timeout(self::CONNECT_TIMEOUT_ATTACHMENT_INFO, self::TIMEOUT_ATTACHMENT_INFO);
        
        return $request;
    }
    
    /**
     * 删除附件
     */
    public static function attachment_destroy() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'attachment/destroy');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('fid', 'int64', TRUE);
        
        return $request;
    }
    
    /**
     * 通过私信转发或者分享私信附件
     */
    public static function attachment_repost() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'attachment/repost');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('fids', 'string', TRUE);
        $request->add_set_callback('fids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ','));
        $request->add_rule('uid', 'int64', TRUE);
        $request->set_request_timeout(self::CONNECT_TIMEOUT_ATTACHMENT_REPOST, self::TIMEOUT_ATTACHMENT_REPOST);
        
        return $request;
    }
    
    /**
     * 大文件上传单片签名接口
     */
    public static function get_token_part() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'attachment/get_token_part');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('upload_key', 'string', TRUE);
        $request->add_rule('part_number', 'int', TRUE);
        $request->add_rule('md5', 'string', TRUE);
        
        return $request;
    }
    
    /**
     * 大文件上传片段合并签名接口
     */
    public static function get_token_merge() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'attachment/get_token_merge');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('upload_key', 'string', TRUE);
        $request->add_rule('md5s', 'string', TRUE);
        
        return $request;
    }
    
    /**
     * 获取上传文件需要的签名
     */
    public static function attachment_upload_sign (){
    	$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'attachment/upload_sign');
    	$request = new Comm_Weibo_Api_Request_Platform($url,"GET");
    	$request->add_rule("file_name", "string", TRUE);
    	$request->set_request_timeout(self::CONNECT_TIMEOUT_ATTACHMENT_UPLOAD_SIGN, self::TIMEOUT_ATTACHMENT_UPLOAD_SIGN);
    	return $request;
    }
    
    /**
     * 通知微盘文件上传完成
     */
    public static function attachment_upload_back(){
    	$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'attachment/upload_back');
    	$request = new Comm_Weibo_Api_Request_Platform($url,'GET');
    	$request->add_rule("file_name", "string", TRUE);
    	$request->add_rule("key", "string", TRUE);
    	
    	$request->set_request_timeout(self::CONNECT_TIMEOUT_ATTACHMENT_UPLOAD_BACK, self::TIMEOUT_ATTACHMENT_UPLOAD_BACK);
        
    	return $request;
    }
    
}
