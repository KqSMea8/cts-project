<?php
/** 
 * 
 * 
 * @author tianhong
 * @since 2013-2-1
 * @package 
 *
 */
class Tool_Pagedata{ 
    /**
     * 获取顶导所需的数据
     * @return array
     */
    public static function get_content_top_data(){
        $viewer = Comm_Context::get('viewer',FALSE);
        $verified = $viewer['verified'];
        $productDomain = 'weibo.com';
        $http_host = Comm_Context::get_server('HTTP_HOST');


        if($viewer->verified_type == 0){
            $verified = 1;//名人
        }elseif($viewer->verified_type == '200' || $viewer->verified_type == '220' ){
            $verified = 3;//达人
        }elseif($viewer->verified_type == 3){
            $verified = '';
        }elseif($viewer->verified_type >0 && $viewer->verified_type <=7){
            $verified = 2;
        }else{
            $verified = '';
        }
        
        $data['verified'] = $verified;
        $data['productDomain'] = $productDomain;
        //判断viewer是v4用户还是v5用户，如果是v4用户展示旧顶导，如果是v5用户展示新顶导。
        //之后主站会全部切换为v5，所以此乃权益之计，待主站都是v5之后我们下线。
        //$data['vLevel'] = Dr_User::get_user_vlevel($viewer->id);
        $data['vLevel'] = '5';


        return $data;        
    }    
}
