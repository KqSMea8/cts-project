<?php
/**
 * 参数验证
*@author  sql <qiling@staff.sina.com.cn>
*@copyright	1.0
*@link		参数验证
*@package	Tool
*@subpackage	Tool
*
 */
class Tool_Check{
    public static function  check_uid($uid, $is_empty = false, $type = false){
    	if($is_empty || empty($uid))
    		return true;
	 	
	 	//参数验证
    	try {
    		Comm_Argchecker::string($uid, 'width_min,5;width_max,12;re,/^[0-9]*$/u',
    		Comm_Argchecker::NEED, Comm_Argchecker::RIGHT);
    	} catch (Comm_Exception_Program $e) {
    		
    		$res = Comm_Util::i18n('controller.aj.common.incorrect_params');
    		self::render_ajax($res, $type);
    		return ;
    	}
    	return true;
    }
    /*
     * 检测oid
     * */
    public static function  check_oid($oid,$is_empty=false,$type = 'aj'){
		if($is_empty || empty($oid))
			return true;
	
    	//参数验证
    	try {
    		Comm_Argchecker::string($oid, 'width_min,13;width_max,13;re,/^[0-9]*$/u',
    		Comm_Argchecker::NEED, Comm_Argchecker::RIGHT);
    	} catch (Comm_Exception_Program $e) {
    		$res = Comm_Util::i18n('controller.aj.common.invalid_order');
    		self::render_ajax($res,$type);
    		return ;
    	}
    	return true;
    }
    /*
     * 检测金额
    * */
    public static function  check_price($mount, $type='aj'){

    	//参数验证
    	try {
    		Comm_Argchecker::string($mount, 're,/^[0-9]*\.?[0-9][0-9]?$/u',
    		Comm_Argchecker::NEED, Comm_Argchecker::RIGHT);
    	} catch (Comm_Exception_Program $e) {
       		$res = Comm_Util::i18n('controller.aj.common.invalid_price');
    		self::render_ajax($res, $type);
    		return ;
    	}
    	return true;
    }
/*
 * 
 * 检测id
 */
    public static function  check_id($id, $is_empty=false, $type = 'aj'){
    	if($is_empty || empty($id))
    		return true;
    	//参数验证
    	try {
    		Comm_Argchecker::int($id, 'min,1',
    		Comm_Argchecker::NEED, Comm_Argchecker::RIGHT);
    	} catch (Comm_Exception_Program $e) {
    		$res = Comm_Util::i18n('controller.aj.common.invalid_id');
    		self::render_ajax($res, $type);
    		return ;
    	}
    	return true;
    }    

    /*
     *
    * 检测时间
    */
    public static function  check_time($time, $is_empty = false, $type ='aj'){
    	if($is_empty || empty($time))
    		return true;
    	
    	//参数验证
    	if(strtotime($time))
    	return true;
    	    	 
    	$res = Comm_Util::i18n('controller.aj.common.invalid_time');
    	self::render_ajax($res ,$type);
    	return ;
    }
        
    public function render_ajax(array $data, $type ='aj') {
    	if($type == 'location'){
    		Tool_Log::fatal('In the ' . __FILE__ . ' at the line ' . __LINE__ );
    		Tool_Redirect::page_not_found();
    		exit;
     	}
    	$jsonp = Comm_Context::param('callback');
    	if (!is_null($jsonp)) {
    		header('Content-type: text/javascript');
    		$json_data = $jsonp . '(' . json_encode($data) . ')';
    	} else {
    		$json_data = json_encode($data);
    	}
		echo $json_data;
    	exit;
    }  

    public static function htmlCheck($html) {
        try{
        //过滤注释
        $html = preg_replace("/<!--.*?-->/is", " ", $html);
        //过滤脚本
        $html = preg_replace("/<script.*?\/script>/is", " ", $html);
        //提取所有html标签, 捕获内容分别为:  标签前的斜杠, 标签名字, 标签后的斜杠
        if (!preg_match_all("/<(\/?)(\w+)[^>]*?(\/?)>/", $html, $tag_list)) {
            return true;
        }
        $stack = array();
        foreach ($tag_list[0] as $key => $value) {
            $tag_name = strtolower($tag_list[2][$key]); //标签名字(不包含前面的斜杠) 
            if (!empty($tag_list[3][$key]) || in_array($tag_name, array('br', 'img', 'input'))) {
                //单标签直接跳过, 判断依据是标签后是否含有斜杠, 或者白名单中的tag(为兼容不规范的tag)
                continue;
            }
            if (empty($tag_list[1][$key])) { //是否是结束标签, 判断依据是标签前是否含有斜杠
                array_push($stack, $tag_name);
            } else {
                if (empty($stack)) {
                    return "no matched tag before $value"; //找不到与此结束标签匹配的开始标签
                }
                $last_tag_name = array_pop($stack);
                if ($last_tag_name != $tag_name) {
                    return "expect </" . $last_tag_name . "> before " . $value . " "; //找不到与某开始标签匹配的结束标签
                }
            }
        }
        if (!empty($stack)) {
            $tag_name = array_pop($stack);
            return "expect </" . $tag_name . "> in the end";
        }
        }catch(Exception $e){
        }
        return true;
    }
}
