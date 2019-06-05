<?php
/**
 * 单一的工具函数，统一放在这里
 *
 * @package    Tool
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     hqlong <qinglong@staff.sina.com.cn>
 * @version    2011-5-20
 */
class Tool_Misc {
    const MAX_FLOAT = '0.0001';
    
    /**
     * 比较两个价格大小，精确到小数点后3位，也即分后一位
     * @param float $price1
     * @param float $price2
     * @return boolean
     */
    public static function compare_price($price1, $price2) {
        $float1 = sprintf ( '%.03f', $price1);
        $float2 = sprintf ( '%.03f', $price2);
        
        return $float1 > $float2;
    }
    
    /**
     *   无线 cookie SUW	 竟然没有销毁！！！！坑 .....
     *   还是自己解决吧：
     *   1> 创建订单页面，如果 get 参数 vid 不是 suw里面的uid，则，
     *    认为这个suw错误，删除之
     *   qiling fix the bug  @2013-10-31
     */
    public static function check_cookie() {
        return ;
        
        $http_method = strtolower($_SERVER['REQUEST_METHOD']);
        if($http_method === 'post') {
            $vid = Comm_Context::form('vid', '');
        }elseif($http_method === 'get') {
            $vid = Comm_Context::param('vid', '');
        }
    
        $viewer = Comm_Context::get('viewer', false);
        if(!empty($vid) && !empty($viewer) && $vid != $viewer->id) {
            $cookie_sue_sup = Comm_Context::get('cookie_sue_sup', -1);
            if($cookie_sue_sup !== 1) {
                setcookie('SUW',"deleted",1,'/','.weibo.com');
                setcookie('SUW',"deleted",1,'/','.weibo.cn');
                $order_url = Comm_Config::get('domain.ordersc');
                $request_uri = Comm_Context::get_server('REQUEST_URI');
                 
                $url = $order_url . $request_uri;
                Tool_Redirect::response($url);
                exit;
            }else {
                // 客户端 情况复杂 可以有多种登录状态 所以也要删除
                setcookie('SUE',"deleted",1,'/','.weibo.com');
                setcookie('SUP',"deleted",1,'/','.weibo.com');
                
                $order_url = Comm_Config::get('domain.ordersc');
                $request_uri = Comm_Context::get_server('REQUEST_URI');
                $url = $order_url . $request_uri;
                Tool_Redirect::response($url);
                exit;
            }
        }
    }
    
    
    /**
     * 解析OpenApi远程调用时，返回的错误信息，一般格式为"xxxx"(234324)
     * 主要是去掉双引号(")和带括号的返回码(0000);
     * 
     * @param string $message
     * @return srting
     */
    public static function parse_error_message($message) {
        return preg_replace('#(?:\(\d+\))|(?:")#is', '', $message);
    }
    
    
    /**
     * 模板渲染方法
     * 
     * @param array $data
     * @param string $tpl
     * @return string
     */
    public static function parse_tpl($data, $tpl){
        $smarty = new WBSmarty();
        $data = array_merge($data, array(
            'g_domain' =>  Comm_Util::conf('domain.weibo'),
            'g_js_domain' =>  Comm_Util::conf('env.js_domain'),
            'g_css_domain' => Comm_Util::conf('env.css_domain'),
            'g_img_domain' => Comm_Util::conf('env.css_domain'),
            //'g_skin_domain' => Comm_Util::conf('env.skin_domain'),
        ));
        $smarty->assign($data);
        return $smarty->fetch($tpl);
    }
    
	/**
	 *  检测是否为仿真或者生产环境
	 *  
	 *  非测试和开发环境则示为仿真或者生产环境
	 *  
	 * @return BOOL
	 */
	public static function is_pro_env() {
	    if (isset($_SERVER['WEIBO_ENV']) && in_array($_SERVER['WEIBO_ENV'], array('test', 'dev'))) {
	        return FALSE;
	    }
	    return TRUE;
	}
	/**
	 * 域名合法性检测
	 * 
	 * @param string $domain
	 * @return bool
	 */
    public static function check_domain($domain) {
        if (!preg_match('/^[a-z0-9]*[a-z]+[a-z0-9]*$/i', $domain)) {
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * 检测owner是不是viewer
     * 
     * @return BOOL
     */
    public static function check_owner_is_viewer() {
        $owner = Comm_Context::get('owner', FALSE);
        $viewer = Comm_Context::get('viewer', FALSE);
        if (FALSE === $owner || FALSE === $viewer)  {
            return FALSE;
        }
        
        return $owner->id === $viewer->id;
    }
    
    /**
     * 判断用户是否登录
     */
    public static function is_login(){
        $is_login = TRUE;
        $viewer = Comm_Context::get('viewer', FALSE);
        if($viewer == FALSE) {
            $is_login = FALSE;
        }
        
        return $is_login;
    }
    
    /**
     * 判断是否使用未登录认证
     */
    public static function is_use_unlogin_auth() {
        $is_unlogin_access = Comm_Context::get('UNLOGIN_ACCESS', FALSE);
        return $is_unlogin_access;
    }
      
    /**
     * 获取请求uri
     */
    public static function get_uri(){
    	if (!empty($_SERVER['PATH_INFO'])) {
    		$uri = $_SERVER['PATH_INFO'];
    	} else {
    		if (isset($_SERVER['REQUEST_URI'])) {
    			// 提取path部分
    			$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    			$uri = rawurldecode($uri);
    		} elseif (isset($_SERVER['PHP_SELF'])) {
    			$uri = $_SERVER['PHP_SELF'];
    		} elseif (isset($_SERVER['REDIRECT_URL'])) {
    			$uri = $_SERVER['REDIRECT_URL'];
    		} else {
    			throw new Swift_Exception_Program('can not detect uri');
    		}
    	}
    	
    	return $uri;
    }
    /**
     * 返回关联数组的values
     * 
     * @param array $arr
     * @return string
     */
    public static function array_values_recursive(array $arr) {
        $array_values = array();
    
        foreach ($arr as $value) {
            if (is_scalar($value) OR is_resource($value)) {
                $array_values[] = $value;
            }elseif (is_array($value)) {
                $array_values = array_merge($array_values, self::array_values_recursive($value));
            }
        }
        
        return $array_values;
    }
    
    /**
     * 是否是 IPv4 地址（格式为 a.b.c.h）
     *
     * @param mixed $value
     *
     * @return boolean
     */
    public static function is_ipv4($value)
    {
        $test = @ip2long($value);
        return $test !== - 1 && $test !== false;
    }

	/**
	 * getPageUrl
	 *
	 * 获得去page化商品售卖页url 
	 * @param int $iid 商品id
	 * @param int $firmId 商家id
	 * @return string url
	 */
	public static function getPageUrl($iid, $firmId = '')
	{
		if (!(int)$firmId)
		{
			$drItemInfo = new Dr_Item_Info();
			$itemInfo = $drItemInfo->get_item_info($iid);
			$firmId = $itemInfo['firm_id'];
		}

		$pageUrl = sprintf(Comm_Config::get('misc.page_url'), Comm_Config::get('misc.service_num'), $firmId, $iid);
		return $pageUrl;
	}
	
	/**
	 * getItemId
	 *
	 * 获得去page化商品的item_id
	 * @param int $iid 商品id
	 * @param int $firmId 商家id
	 * @return string item_id
	 */
	public static function getScheme($iid, $firmId = '')
	{
		if (!(int)$firmId)
		{
			$drItemInfo = new Dr_Item_Info();
			$itemInfo = $drItemInfo->get_item_info($iid);
			$firmId = $itemInfo['firm_id'];
		}
		
		$itemId = sprintf(Comm_Config::get('misc.item_id'), Comm_Config::get('misc.service_num'), $firmId, $iid);
		$scheme = "sinaweibo://infopage?containerid={$itemId}" ;
		return $scheme;
	}
    /**
     * getPageId 
     * 获取page化的pageid
     * @param mixed $iid 
     * @param string $firmId 
     * @return void
     */
	public static function getPageId($iid, $firmId = '')
	{
		if (!(int)$firmId)
		{
			$drItemInfo = new Dr_Item_Info();
			$itemInfo = $drItemInfo->get_item_info($iid);
			$firmId = $itemInfo['firm_id'];
		}

		$pageId = sprintf('%d_%d_%d', Comm_Config::get('misc.service_num'), $firmId, $iid);
		return $pageId;
	}

    /**
     * genTransferSign 
     * 生成转账秘钥
     * @param mixed $data 
     * @return void
     */
    public function genTransferSign($data){
        $str = http_build_query($data);
        $sign = md5($str);
        $sign = sha1($sign);
        return $sign;
    }
    
    
    /*
     * 标准化查询串中的日期
     * @param string $date
     * @return 类似 xxxx-xx-xx的日期串 
     */
    public static function standardQueryTime($date) {
    	$pattern = '/\d{4}(-\d{2}){2}/' ;
    	$res = preg_match($pattern, $date, $matches) ;
    	if ($res) {
    		return $matches[0];
    	}
    	return '' ;
    }
}
