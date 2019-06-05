<?php
/**
 * 公用类方法
*@author  sql <qiling@staff.sina.com.cn>
*@copyright	1.0
*@link		参数验证
*@package	Tool
*@subpackage	Tool
*
 */
class Tool_Common{
    const EXPORT_TRANSFER_LIST = 'http://api.sc.weibo.com/v2/transfer/income/export?' ;
    const API_KEY = 'f4793789d78e66793ca3';
	/*
	 * 获取左导
	 * 
	 * */
    public static function  getLeftbar(){
		$viewer = Comm_Context::get('viewer');
		$uid = $viewer->id;
		$url = "http://i.e.weibo.com/hd/leftcommon?uid=" . $uid . "&loation=event_create";
		return  '';
    }
    //微博转帐：获取导出收款列表url
    public static function get_export_url($params) {
        $params['sign'] = Tool_Sign::generate_sign($params, self::API_KEY) ;
        $query_params = self::filter_empty_param($params) ;
        $url = self::EXPORT_TRANSFER_LIST . http_build_query($query_params) ;
	
	return $url ;
    }

    public static function filter_empty_param($params) {
        foreach($params as $key => $values) {
            if($values === '') {
                unset($params[$key]);
            }
        }
	return $params ;
    }

    public static function getUrlParams($url){
        $urlArr = parse_url($url);
        if (!empty($urlArr['query'])){
            parse_str($urlArr['query'], $arr);
            return $arr;
        }
        return false;
    }

	public static function getAllParams(){
		return array_merge($_GET, $_POST);
	}

    /**
     * hideUserNmae 
     * 隐藏完整用户名
     * @param mixed $name 
     * @static
     * @access public
     * @return void
     */
    public static function hideUserNmae($name){
    
        $length = mb_strlen($name, 'utf-8');
        $start = mb_substr($name, 0, 1, 'utf-8');
        $end = mb_substr($name, $length - 1, 1, 'utf-8');
        /*
        $holder = '';
        for ($i=0; $i < $length - 2; $i++){
            $holder .= '*';
        }
        $holder = $holder ? $holder : '*';
        */
        $holder = '****';
        return $start . $holder . $end;
    }
}
