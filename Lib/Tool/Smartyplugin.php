<?php
class Tool_SmartyPlugin {
    protected static $cache = array();
    protected static $rowRule= array('+'=>'go_up','-'=>'go_down','='=>'go_norm');
    /**
     * 根据level显示对应的图标样式
     * @param array $param
     * @param Smarty $smarty
     */
    public static function show_icon_by_level($param, $smarty) {
        $icon_type = '';
        if(isset($param['extra']) && isset($param['extra']['verified']) && isset($param['extra']['verified_type'])) {
            if ((boolean)$param['extra']['verified'] === true) {
                if ($param['extra']['verified_type'] == 0) {
                    $icon_type = 'v_person';
                } elseif ($param['extra']['verified_type'] > 0) {
                    $icon_type = 'v_enterprise';
                }
            } else {
                if ($param['extra']['verified_type'] == 220) {
                    $icon_type = 'daren';
                }
            }
            
            if (isset(self::$cache['show_icon_by_level'][$icon_type])) {
                return self::$cache['show_icon_by_level'][$icon_type];
            }
            
            if (!isset(self::$cache['show_icon_by_level']['icon_css'])) {
                $icon_css = array(
                    'v_person' => array(
                        'css' => 'approve',
                        'title' => Comm_I18n::text('tpls.common.sina_vip_personal'),
                        'alt' =>  Comm_I18n::text('tpls.common.sina_vip_personal'),
                        'href' => Comm_Config::get('domain.verified_platform'),
                    ),
                    'v_enterprise' => array(
                        'css' => 'approve_co',
                        'title' => Comm_I18n::text('tpls.common.sina_vip_enterprise'),
                        'alt' =>  Comm_I18n::text('tpls.common.sina_vip_enterprise'),
                        'href' => Comm_Config::get('domain.verified_platform'),
                    ),
                    'daren' => array(
                        'css' => 'ico_club',
                        'title' => Comm_I18n::text('tpls.common.title_daren'),
                        'alt' => Comm_I18n::text('tpls.common.title_daren'),
                        'href' => Comm_Config::get('domain.club') . '/intro',
                    ),
                );
                self::$cache['show_icon_by_level']['icon_css'] = $icon_css;
            } else {
                $icon_css = self::$cache['show_icon_by_level']['icon_css'];
            }
        }
        
        if($icon_type) {
        	$node_type = ($icon_type == 'daren') ? ' node-type="daren"' : "";
            $html = '<img src="' . Comm_Util::conf('env.css_domain') . '/t4/style/images/common/transparent.gif" title= "%s" alt="%s" class="%s"'.$node_type.'/>';
            if(!isset($param['is_link']) || $param['is_link'] == 1) {
                $html = '<a target="_blank" href="' . $icon_css[$icon_type]['href'] . '">' . $html . '</a>';
            }
            $title = (isset($param['title']) && $param['title']) ? $param['title'] : $icon_css[$icon_type]['title'];
            
            return self::$cache['show_icon_by_level'][$icon_type] = sprintf($html, $title, $icon_css[$icon_type]['alt'], $icon_css[$icon_type]['css']);
        }
        
        return false;
    }
    
    /**
     * 根据level显示对应的图标样式for v5
     * @param array $param
     * @param Smarty $smarty
     */
    public static function show_icon_by_level_v5($param, $smarty) {
    	$icon_type = '';
    	if(isset($param['extra']) && isset($param['extra']['verified']) && isset($param['extra']['verified_type'])) {
    		if ((boolean)$param['extra']['verified'] === true) {
    			if ($param['extra']['verified_type'] == 0) {
    				$icon_type = 'v_person';
    			} elseif ($param['extra']['verified_type'] > 0) {
    				$icon_type = 'v_enterprise';
    			}
    		} else {
    			if ($param['extra']['verified_type'] == 220) {
    				$icon_type = 'daren';
    			}
    		}
    
    		if (isset(self::$cache['show_icon_by_level'][$icon_type])) {
    			return self::$cache['show_icon_by_level'][$icon_type];
    		}
    
    		if (!isset(self::$cache['show_icon_by_level']['icon_css'])) {
    			$icon_css = array(
    					'v_person' => array(
    							'css' => 'approve',
    							'title' => Comm_I18n::text('tpls.common.sina_vip_personal'),
    							'alt' =>  Comm_I18n::text('tpls.common.sina_vip_personal'),
    							'href' => Comm_Config::get('domain.verified_platform'),
    					),
    					'v_enterprise' => array(
    							'css' => 'approve_co',
    							'title' => Comm_I18n::text('tpls.common.sina_vip_enterprise'),
    							'alt' =>  Comm_I18n::text('tpls.common.sina_vip_enterprise'),
    							'href' => Comm_Config::get('domain.verified_platform'),
    					),
    					'daren' => array(
    							'css' => 'ico_club',
    							'title' => Comm_I18n::text('tpls.common.title_daren'),
    							'alt' => Comm_I18n::text('tpls.common.title_daren'),
    							'href' => Comm_Config::get('domain.club') . '/intro',
    					),
    			);
    			self::$cache['show_icon_by_level']['icon_css'] = $icon_css;
    		} else {
    			$icon_css = self::$cache['show_icon_by_level']['icon_css'];
    		}
    	}
    
    	if($icon_type) {
    		$node_type = ($icon_type == 'daren') ? ' node-type="daren"' : "";
    		$html = '<i class="W_ico16 %s"'.$node_type.'></i>';
    		if(!isset($param['is_link']) || $param['is_link'] == 1) {
    			$html = '<a target="_blank" href="' . $icon_css[$icon_type]['href'] . '">' . $html . '</a>';
    		}    		  
    		return self::$cache['show_icon_by_level'][$icon_type] = sprintf($html, $icon_css[$icon_type]['css']);
    	}
    
    	return false;
    }

    /**
     * 对微博时间做转换
     * 
     * @param string $time
     * @return string
     */
    public static function format_time($time) {
        $tools_formatter_time = new Tool_Formatter_Time();
        return $tools_formatter_time->timeFormat($time);
    }
    
    public static function show_web_im($param, $smarty){
    	$owner = $param['owner'];
    	$viewer = $param['viewer'];
    	$relation = $param['relation'];
    	$card = 0;
    	if (isset($param['card'])){
    		$card = 1;
    	}
    	$allow_message = $param['allow_message'];
    	if ($owner->id == $viewer->id){
    		return "";
    	}
        $show_web_im = 0;
        if ($relation == 1|| $relation ==2 || $allow_message){
    		$show_web_im = 1;
    	}
    	$html = "";
    	if ($show_web_im == 1){
    		//1 在线 2 忙碌3离线4隐身0不在线
    		try {
                $online_status = Dr_Im::status_query($owner->id);
    		} catch (Comm_Exception_Program $e) {
    		    $online_status = array();
    		}
	    	if (isset($online_status['status']) && in_array($online_status['status'], array(1,2,3))){
	    		if ($card){
	    			$html = '<span class="IM_online"></span>';
	    			$html .= '<a href="javascript:;" action-type="webim.conversation" action-data="uid='.$owner->id.'&nick='.$owner->screen_name.'">聊天</a>';
	    		}else {
	    			$html = '<a class="webim_online" href="javascript:;" action-type="webim.conversation" action-data="uid='.$owner->id.'&nick='.$owner->screen_name.'">聊天</a>';
	    		}
	    	}else{
	    		if ($card){
	    			$html = '<span class="IM_offline"></span>';
	    			$html .= '<a href="javascript:;" action-type="webim.conversation" action-data="uid='.$owner->id.'&nick='.$owner->screen_name.'">私信</a>';
	    		}else {
	    			$html = '<a class="webim_leave" href="javascript:;" action-type="webim.conversation" action-data="uid='.$owner->id.'&nick='.$owner->screen_name.'">私信</a>';
	    		}
	    	}
    	}
    	return $html;
    }
    
	public static function show_wvr(){
		if (isset($_COOKIE['wvr']) && ($_COOKIE['wvr'] == '4' || $_COOKIE['wvr'] == '3.6')) {
			return "&wvr=".$_COOKIE['wvr'];
		}
	}
	public static function show_icon_by_domain($param, $smarty) {
    	$weibo_domain = Comm_Util::conf("domain.weibo");
    	if(!isset($param['extra']) || !isset($param['extra']['id'])) {
    		$html = '<a href="{$weibo_domain}" class="online">'.$weibo_domain.'</a>';
        	return $html;
        }
        $weihao_html = $html = "";
        $user_info = $param['extra'];
        $domain_bak = $show_domain =  $show_all_domain = false;
        if (isset($param['show_domain']) && $param['show_domain']){
        	$show_domain = true;
        }
        //如果模板中传参数show_all_domain的值，则微号及domain全显示
       if (isset($param['show_all_domain']) && $param['show_all_domain'] ){
        	$show_all_domain = true;
        }
        if(isset($user_info['weihao']) && !empty($user_info['weihao'])){
			$domain_bak = true;
			$weihao = $user_info['weihao'];
        }elseif(isset($user_info['domain_bak']) && !empty($user_info['domain_bak'])) {
        	$domain_bak = true;
        	$weihao = $user_info['domain_bak'];
        }
        //限制内网访问
		if ($domain_bak && Comm_Config::get('control.use_weihao_icon')){
			$weihao_list = Comm_Config::load("weihao");
			$weihao_info = Dr_Account::weihao_batch(array($user_info['id'] => $weihao));
			$weihao_html = '';
			if (isset($weihao_info[$user_info['id']]) && is_array($weihao_info[$user_info['id']])){
				$weihao_info = $weihao_info[$user_info['id']];
				$weihao_class = $weihao_title = "";
				if (isset($weihao_info['type']) && isset($weihao_list[$weihao_info['type']])){
					if (isset($weihao_list[$weihao_info['type']]['class']) && $weihao_list[$weihao_info['type']]['class'] && isset($weihao_list[$weihao_info['type']]['title']) && $weihao_list[$weihao_info['type']]['title']){
						$weihao_class = $weihao_list[$weihao_info['type']]['class'];
						$weihao_title = $weihao_list[$weihao_info['type']]['title'];
						$weihao_title .= "(".$weihao.")";
						$weihao_html = '<a target="_blank" href="'.Comm_Util::conf("domain.weihao").'"><img class="'.$weihao_class.'" src="'.Comm_Util::conf('env.css_domain').'/t4/style/images/common/transparent.gif" title="'.$weihao_title.'"/></a>';
					}
				}
			}
			
			//微号用户同时显示域名domain信息
			if( isset($user_info['domain']) && !empty($user_info['domain']) && (($user_info['domain'] != $weihao ) && ($user_info['domain'] != $user_info['id']) ) && $show_all_domain ){
			
				if(isset($_COOKIE['wvr']) && ($_COOKIE['wvr'] == '3.6')){
					$weihao_html .= '<br/><a class="online" href="/'.$user_info['domain'].'">'.$weibo_domain.'/'.$user_info['domain'].'</a>';
				}else{
					$weihao_html .= '<i class="W_vline">|</i><a class="online" href="/'.$user_info['domain'].'">'.$weibo_domain.'/'.$user_info['domain'].'</a>';
				}
			}
			
		}
		if($domain_bak){
			$user_domain = $user_info['weihao'];
		}else {
			$user_domain = self::show_domain($user_info);
       	}
        if ($show_domain){
			$html = '<p><a href="/'.$user_domain.'" class="online">'.$weibo_domain.'/'.$user_domain.'</a>'.$weihao_html.'</p>';
		}elseif ($domain_bak){
			$html = '<p class="info1"><a href="/'.$user_domain.'">'.$weibo_domain.'/'.$user_domain.'</a>'.$weihao_html.'</p>';
		}
		return $html;	
    }
	/*
     * 根据传入的domain判断用户是否为微号用户，如果是返回微号入口链接
     * @param array $param
     * $param Smarty $smarty
     */
    public static function show_domain($user){
    	//如果是微号用户优先取微号
       if(isset($user['weihao']) && !empty($user['weihao'])){
			return $user['weihao'];
        }else{
    		return ($user['domain'] != $user['id'])? $user['domain'] : "u/".$user['id'];
        }
    }
    
    /**
     * 根据tip返回对应的样式(上涨，下跌，持平)
     * @param array $param
     */
    public static function show_arrow($param){
    	$type = 1;//预留，以防将来加载别的东西；
    	if($type==1){	
    		$str = self::$rowRule[$param['tip']];
    		return $str;
    	}
    	
    }
    /**
     * 显示淘宝标示
     * @param unknown $param
     * @param unknown $smarty
     */
    public static function show_taobao_icon($user){
    	if (!($user['user']->badge['taobao'] || $user['user']['badge']['taobao'])){
    		if ($user['user']['contact_uid']){
    			$user['user'] = Dr_User::get_user_info($user['user']['contact_uid']);
    		}elseif ($user['user']->id){
    			$user['user'] = Dr_User::get_user_info($user['user']->id);
    		}
    	}
    	if($user['user']->badge['taobao'] ==1 || $user['user']['badge']['taobao'] ==1) {
            $html = '<img src="' . Comm_Util::conf('env.css_domain') . '/t4/style/images/common/transparent.gif" title= "%s" alt="%s" class="%s"/>';
           	$html = '<a target="_blank" href="http://e.weibo.com/taobao/introduce">' . $html . '</a>';
            $title = '淘宝';
            $alt = '淘宝';
            $css = "approve_tao";
            return sprintf($html, $title, $alt, $css);
        }
    }
	public static function get_order_status($params, $smarty) {
	    $do_order_info = new ReflectionClass('Do_Order_Info');
	    $order_status_info = $do_order_info->getConstants();
	    $order_status = array_values($order_status_info);
	    if(isset($params['status']) && in_array($params['status'], $order_status)) {
	        $status_info = Comm_I18n::text('orderstatus.' . $params['status']);
	        // 后续会针对子状态判断
	        if(isset($params['substatus']) && in_array($params['status'], $order_status)) {
	            $substatus_info = $status_info['substatus'];
	            return $substatus_info[0];
	        }
	        $type = $params['type'];
	        if(isset($status_info[$type]))
	            return $status_info[$type];
	    }
	    return '';
	}
	
	public static function getGiftStatus($params, $smarty)
	{
	    $do_gift_info = new ReflectionClass('Do_Gift_Info');
	    $gift_status_info = $do_gift_info->getConstants();
	    $gift_status = array_values($gift_status_info);
	    if(isset($params['status']) && in_array($params['status'], $gift_status))
	    {
	        $status_info = Comm_I18n::text('giftstatus.' . $params['status']);
	        $type = $params['type'];
	        if(isset($status_info[$type]))
	        {
	            return $status_info[$type];
	        }
	    }
	    return '';
	}
}
