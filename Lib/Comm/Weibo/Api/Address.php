<?php
/**
 * ship相关接口
 *
 * @package    api
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_Address{
    const RESOURCE = 'account';

    /**
     * 获取用户地址
     */

    public static function get_user_address() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'deliver_address',"json",null,false);
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', false);
    
        return $request;
    }    

    /**
     * 添加用户地址
     */
  
   
    public static function add_user_address() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'deliver_address/add',"json",null,false);
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('name', 'string', true);
        $request->add_rule('province', 'int', true);
        $request->add_rule('city', 'int', true);
        $request->add_rule('district', 'int', false);
        $request->add_rule('address', 'string', true);
        $request->add_rule('zip', 'string', true);               
        $request->add_rule('mobile', 'string', false);
        $request->add_rule('area_code', 'string', false);
        $request->add_rule('phone_number', 'string', false);
        $request->add_rule('ext_number', 'string', false);  
        $request->add_rule('is_default', 'boolean', false);          
        
        return $request;
    }
        
    
    /**
     * 更新用户地址
     */

   
    public static function update_user_address() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'deliver_address/update',"json",null,false);
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('id', 'int', true);
        $request->add_rule('name', 'string', false);
        $request->add_rule('province', 'int', false);
        $request->add_rule('city', 'int', false);
        $request->add_rule('district', 'int', false);
        $request->add_rule('address', 'string', false);
        $request->add_rule('zip', 'string', false);               
        $request->add_rule('mobile', 'string', false);
        $request->add_rule('area_code', 'string', false);
        $request->add_rule('phone_number', 'string', false);
        $request->add_rule('ext_number', 'string', false);  
        $request->add_rule('is_default', 'boolean', false);          
        
        return $request;
    }
    /**
     * 删除用户地址
     */

    public static function destroy_user_address() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'deliver_address/destroy',"json",null,false);
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('id', 'int', true);
    
        return $request;
    }     
 
}