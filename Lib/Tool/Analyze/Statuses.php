<?php
/**
 * 获取微博列表中的
 *
 * @package    analyze
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Tool_Analyze_Statuses {
    /**
     * 获取微博列表中的微博的链接及作者备注信息
     * @param int $viewer_uid 登录用户uid
     * @param array $list feed列表
     * @param boolean $status_remark 是否显示微博作者的备注
     * @param boolean $is_fav 是否为收藏feed
     */
    public static function get_statuses_userremarks ($viewer_uid, $list, $status_remark = false, $is_fav = false) {
        $userremarks = array();
        if (count($list) > 0) {
            $ids = $followings = array();
            foreach ($list as $k=>$one_list) {
                if($is_fav === true) {
                    $temp_list = $one_list['status'];
                } else {
                    $temp_list = $one_list;
                }
                if($status_remark) {
                    Tool_Analyze_Statuses::append_statuses_user_ids($temp_list, $followings, $viewer_uid);
                }
                if(isset($temp_list['retweeted_status'])) {
                    Tool_Analyze_Statuses::append_statuses_user_ids($temp_list['retweeted_status'], $followings, $viewer_uid);
                }
                unset($temp_list);
            }
            $cnt_followings = count($followings);
            if($cnt_followings > 0){
                $userremarks = Dr_Relation::friends_remark_batch($followings);
            }
        }
        return $userremarks;
    }
    
    public static function append_statuses_user_ids($statuses, &$uids, $exclude_uid) {
        //微博被删除
        if (!isset($statuses['user'])) {
            return;
        }
        if(!empty($statuses['user']['id']) && $statuses['user']['id'] != $exclude_uid) {
            !isset($uids[$statuses['user']['id']]) && $uids[$statuses['user']['id']] = $statuses['user']['id'];
        }
    }
    
    /**
     * 获取微博作者的是否可评论标识
     * @param int $viewer_uid 登录者uid
     * @param array $feed_list
     * @param boolean $is_owner 是否为自己的微博
     */
    public static function fetch_user_privs($viewer_uid, $feed_list){
        $user_privs = array();
        $user_comment_settings = array();
        $need_check_relations = array();
        foreach ($feed_list as $feed){
            if($feed['user']['id']){
                if($feed['user']['id'] == $viewer_uid) {
                    $user_comment_settings[$feed['user']['id']] = true;
                } else {
                    $priv = isset($feed['user']['allow_all_comment']) ? $feed['user']['allow_all_comment'] : false;
                    $user_comment_settings[$feed['user']['id']] = $priv;
                    !$priv && $need_check_relations[] = $feed['user']['id'];
                }
            }
            
            if(!empty($feed['retweeted_status']['user']['id'])){
                if($feed['retweeted_status']['user']['id'] == $viewer_uid) {
                    $user_comment_settings[$feed['retweeted_status']['user']['id']] = true;
                } else {
                    $priv = isset($feed['retweeted_status']['user']['allow_all_comment']) ? $feed['retweeted_status']['user']['allow_all_comment'] : false;
                    $user_comment_settings[$feed['retweeted_status']['user']['id']] = $priv;
                    !$priv && $need_check_relations[] = $feed['retweeted_status']['user']['id'];
                }
            }
        }
        
        //此处检查的是微博的拥有者是否关注了“我”，因关于评论的隐私设置即为如此
        //由于某些用户的粉丝数非常多，所以此处暂采用获取“我”的粉丝列表的方式去做。
        //后期优化可以针对粉丝较少的用户直接获取粉丝列表，而粉丝较多的用户采用单独检测的方式去判断关系
        foreach($need_check_relations as $user){
            $relation = Dr_Relation::check_relation($viewer_uid, $user);
            if($relation === '1' || $relation === '2'){ //双向关注或者对方是viewer“我”的粉丝
                $user_comment_settings[$user] = true;
            }
        }
        return $user_comment_settings;
    }
    /**
     * 获取转发按钮的action-data
     * 
     * 此函数目前只生成相关静态参数，不返回微博的评论状态，评论状态在转发弹层后单独请求获取
     */ 
    public static function get_retwit_action_data($mblog, $viewerid) {
    	$domain = Comm_Config::get("domain.weibo");
        $action_data = array();
        $id_arr = array();
        $mid_arr = array();
        foreach ($mblog as $v) {
            $id_arr[] = $v['mid'];
            if (!empty($v['retweeted_status'])) {
                $id_arr[] = $v['retweeted_status']['mid'];
            }
        }
        $id_arr = array_unique($id_arr);
        $mid_arr = Comm_Weibo_MIDConverter::multi_from10to62($id_arr);
    	foreach ($mblog as $v){
    	    if (!isset($v['user'])) {
    	        continue;
    	    }
    		$action_data_url = "allowForward=1";
    		$root_domain = $v['user']['domain'];
    		if(!empty($v['retweeted_status']) && !isset($v['retweeted_status']['user'])){
    			$action_data_url = "allowForward=0";
    		}

    		if(!empty($v['retweeted_status']) && isset($v['retweeted_status']['user'])){ 				
    				$action_data_url .= "&rootmid=" . $v['retweeted_status']['id'] . "&rootname=" . $v['retweeted_status']['user']['name'] . "&rootuid=" . $v['retweeted_status']['user']['id'];
    				$action_data_url .= "&rooturl=" . $domain ."/". $v['retweeted_status']['user']['id'] . "/" . $mid_arr[$v['retweeted_status']['mid']];
    				$root_domain = $v['retweeted_status']['user']['domain'];
    		}
        	$action_data_url .= "&url=" . $domain ."/". $v['user']['id'] . "/" . $mid_arr[$v['mid']];
    		$action_data_url .= "&mid=" . $v['id'] . "&name=" . $v['user']['name'] . "&uid=" . $v['user']['id']. "&domain=". $root_domain;
			if(isset($v['thumbnail_pic']) && $v['thumbnail_pic']){
				$action_data_url .= "&pid=" . array_shift(explode('.', basename($v['thumbnail_pic'])));
			}
    		if(isset($v['retweeted_status']['thumbnail_pic']) && $v['retweeted_status']['thumbnail_pic']){
				$action_data_url .= "&pid=" . array_shift(explode('.', basename($v['retweeted_status']['thumbnail_pic'])));
			}
        	$action_data[$v['id']] = $action_data_url;
        }       
        return $action_data;    
    }
}