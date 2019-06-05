<?php
/**
 * Tool_Scheme
 * 获取客户端scheme
 * @author    xiamengyu<mengyu5@staff.sina.com.cn> 
 * @created   2014-11-26
 * @copyright copyright(2013) weibo.com all rights reserved
 */
class Tool_Scheme{
    const H5_URL = 'http://m.weibo.cn/groupChat/userChat/groupListForLuckyBag?';

    /** 
     * gotoMsgbox 
     * 红包乎起消息箱
     * @param mixed $content 
     * @param mixed $encode 
     * @static
     * @return void
     */
    public static function gotoMsgbox($content, $set_id, $blessing = '快来抢，手快有，手慢无'){
        $clientInfo = $_SERVER['HTTP_USER_AGENT'];
        $weiboInfo = substr($clientInfo,strpos($clientInfo, 'Weibo') + 5, -1);
        $weiboInfoArr = explode( '__', $weiboInfo);
        $clientVersion = $weiboInfoArr[2];
        if (strpos($clientVersion, '_') !== false){
            $clientInfo = explode('_', $clientVersion);
            $clientVersion = $clientInfo[0];
        }

        $viewer = Comm_Context::get('viewer', false);
        $obj = Model_Object::get_object_info('2022782002:receive_' . $set_id);
        $data = array(
                    'title'      => "[{$viewer->name}]的红包",
                    'des'        => $blessing,
                    'btn'        => '发送',
                    'img'        => 'http://img.t.sinajs.cn/t4/appstyle/red_envelope/images/mobile/card_icon.png',
                    //'content'    => $content,
                );
        if(version_compare($clientVersion, '5.1.0') != -1){
            $data['pickertype'] = 1;
            $data['type']       = 1;
            $data['content']    = $obj['object']['url'];
            $data['pageid']     = '1001212022782002:receive_' . $set_id;
            $url  = 'sinaweibo://recentpicker?';
            $url .= http_build_query($data);
            return $url;
        }else{
            $data['content']      = '说说分享心得...';
            $data['luckybag_url'] = $obj['object']['url'];
            $data['name']         = '分享群组红包';
            return self::H5_URL . http_build_query($data);
        }

    }

    /**
     * transferUserList 
     * 转账
     * @param mixed $pageid 
     * @static
     * @return void
     */
    public static function transferUserList($id){
        $clientInfo = $_SERVER['HTTP_USER_AGENT'];
        $weiboInfo = substr($clientInfo,strpos($clientInfo, 'Weibo') + 5, -1);
        $weiboInfoArr = explode( '__', $weiboInfo);
        $clientVersion = $weiboInfoArr[2];
        if (strpos($clientVersion, '_') !== false){
            $clientInfo = explode('_', $clientVersion);
            $clientVersion = $clientInfo[0];
        }   

        $data = array(
                    'title'      => '我发起了收款，小伙伴们快来付钱吧',
                    'btn'        => '分享',
                    'img'        => 'http://ww4.sinaimg.cn/large/483449fdtw1egwd3nbf55j203c03c746.jpg',
                );

        $obj = Model_Object::get_object_info(Comm_Config::get('misc.transfer_object_id_prefix') . $id);
        if(version_compare($clientVersion, '5.1.0') != -1){
            $data['pickertype'] = 1;
            $data['type']       = 1;
            $data['content']    = $obj['object']['url'];
            $data['pageid']     = Comm_Config::get('misc.transfer_biz') . Comm_Config::get('misc.transfer_biz') . '_' . $id;
            $data['des']        = '';
            $url  = 'sinaweibo://recentpicker?';
            $url .= http_build_query($data);
            return $url;
        }else{
            $data['content']      = '说说分享心得...';
            $data['luckybag_url'] = $obj['object']['url'];
            $data['name']         = '分享到私信';
            $data['des']          = "&nbsp;";
            return self::H5_URL . http_build_query($data);
        }
    }

}
