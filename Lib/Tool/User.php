<?php

class Tool_User {

	public static function show($uid) {
		try {
			$api = Comm_Weibo_Api_Users::show();
			$api->uid = $uid;
			return $api->get_rst();
		} catch (Comm_Weibo_Exception_Api $e) {
		} catch (Exception $e) {
		}
		return false;
	}

    /**
     * getLevelClass 
     * 返回用户icon(V6)
     * @param mixed $viewer 
     * @static
     * @return void
     */
    public static function getLevelClass($viewer){
        if ($viewer->verified_type == 0){//橙V
            return 'icon_pf_approve';
        }else if ($viewer->verified_type >= 1 && $viewer->verified_type <= 7){//蓝V
            return 'icon_pf_approve_co';
        }else if($viewer->verified_type == 400){//已故实名
            return 'icon_pf_approve_dead';
        }

        if ($viewer->level == 7){//达人
            return 'icon_pf_club';
        }else if ($viewer->level == 10){//微女郎
            return 'icon_pf_vlady';
        }
        return '';
    }

    //获取v6的认证样式
    public static function getClassHtml($viewer){
        if ($viewer->verified_type == 0){
            return '<a href="http://verified.weibo.com/verify" target="_blank"><i class="W_icon icon_approve" title="¿¿¿¿¿¿"></i></a>';
        }else if ($viewer->verified_type >= 1 && $viewer->verified_type <= 7){
            return '<a href="http://verified.weibo.com/verify" target="_blank"><i class="W_icon icon_approve_co" title="¿¿¿¿¿¿"></i></a>';
        }else if($viewer->verified_type == 400){
            //return 'icon_approve_dead';
        }

        if ($viewer->level == 7){
            return '<a href="http://club.weibo.com/intro" target="_blank"><i class="W_icon icon_club" node-type="daren" title="¿¿¿¿"></i></a>';
        }else if ($viewer->level == 10){
            //return 'icon_vlady';
        }
        return '';
    }
}
