<?php
/**
 * 签名相关函数，目前只支持md5方式
 * @todo 签名规则
 *  1. 对数组里的每一个值从 a 到 z 的顺序排序,若遇到相同首字母,则看第二个字母, 以此类推。排序完成之后,再把所有数组值以“&”字符连接起来;
 *  2. 没有值的参数无需传递,也无需包含到待签名数据中;
 *  3. 待签名数据应该是原生值而不是 urlencode 之后的值.
 *  
 * @author  	liuyu6
 * @version 	2013-11-15
 * @copyright  	copyright(2013) weibo.com all rights reserved
 *
 */
class Tool_Sign {
    public static $sign_type = 'MD5';
    
    /**
     * 设置加密类型
     * @param string $sign_type
     */
    public static function _set_sign_type($sign_type) {
        self::$sign_type = strtoupper($sign_type);
    }
    
    /**
     * 返回MD5加密后的字符串
     *
     * @param array $arr_para   是否需要urlencode处理
     * @param string $key   key需要支付宝提供
     * @param boolean $is_urlencode
     * @return string
     */
    public static function generate_sign(array $arr_para, $key, $is_urlencode = false) {
    	    $sort_sign_data = self::arr_sort($arr_para);
        $arr_sign_data  = self::filter_sign_data($sort_sign_data);
        $str_sign_data  = self::get_link_string($arr_sign_data, $is_urlencode);
    
        switch(self::$sign_type) {
            case "MD5" :
                return self::md5_sign($str_sign_data, $key);
            default :
                return false;
        }
    }

    public static function get_alipay_sign_form(array $arr_para, $is_urlencode = false){
    	$sort_sign_data = self::arr_sort($arr_para);
        $arr_sign_data  = self::filter_alipay_sign_data($sort_sign_data);
        $str_sign_data  = self::get_link_string($arr_sign_data, $is_urlencode);
        return $str_sign_data;
    }

    public static function filter_alipay_sign_data(array $arr_para){
        $para_filter = array ();
        $arr_para = Tool_Array::filter_null($arr_para);
        $arr_para = Tool_Array::filter_empty($arr_para);
    
        foreach($arr_para as $k=>$v) {
            if($k == "sign" || $v === "")
                continue;
    
            $para_filter[$k] = $arr_para[$k];
        }
        return $para_filter;
    }

    //rsa加密
    public static function get_rsa_sign($data, $wb_private_key_path) {
        // -- Get a PEM formatted private key 
        $wb_private_key = file_get_contents($wb_private_key_path);
    
        // -- Returns a positive key resource identifier on success, or FALSE on error
        // -- the key maybe the format file://path/to/file.pem and MUST NAMED PEM. or a PEM formatted private key.
        $priv_key_id = openssl_pkey_get_private($wb_private_key);
    
        // -- computes a signature for the specified data by using SHA1 for hashing followed by encryption using the private key associated with priv_key_id. 
        // -- Note that the data itself is not encrypted.
        openssl_sign($data, $signature, $priv_key_id);
    
        // -- frees the key associated with the specified key_identifier from memory
        openssl_free_key($priv_key_id); 
    
        // -- base64 encode the signature
        return base64_encode($signature);
    }

    public static function verify_rsa_sign($data, $sign, $path){
        //读取支付宝公钥文件

        $pubKey = file_get_contents($path);


        //转换为openssl格式密钥

        $res = openssl_get_publickey($pubKey);


        //调用openssl内置方法验签，返回bool值

        $result = (bool)openssl_verify($data, base64_decode($sign), $res);

        //释放资源

        openssl_free_key($res);


        //返回资源是否成功

        return $result;
    }
    
    /**
     * 由签名算法要求，对数组排序
     *
     * @param array $arr_para
     * @return array
     */
    public static function arr_sort(array $arr_para) {
        ksort($arr_para);
        reset($arr_para);
    
        return $arr_para;
    }
    
    /**
     * 去除数组中的空值和签名参数
     *
     * @param array $arr_para
     * @return array
     */
    public static function filter_sign_data(array $arr_para) {
        $para_filter = array ();
        $arr_para = Tool_Array::filter_null($arr_para);
        $arr_para = Tool_Array::filter_empty($arr_para);
    
        foreach($arr_para as $k=>$v) {
            if($k == "sign" || $k == "sign_type" || $v === "")
                continue;
    
            $para_filter[$k] = $arr_para[$k];
        }
        return $para_filter;
    }
    
    /**
     * 传入一个数组，组成形如 < k1=val1&k2=val2 > 的link形式
     *
     * @param array $arr_para
     * @param boolean $is_urlencode 是否需要urlencode处理
     * @return string
     */
    public static function get_link_string(array $arr_para, $is_urlencode = false) {
        $pairs = array();
        foreach ($arr_para as $k=>$v) {
            if(true === $is_urlencode) {
                $pairs[] = $k . '=' . rawurlencode($v);
            }else {
                $pairs[] = "$k=$v";
            }
        }
        $sign_data = implode('&', $pairs);
    
        return $sign_data;
    }
    
    /**
     * 才用 MD5 方式生成签名，需要实现提供key
     *
     * @param string $str_para
     * @param string $key
     * @return string
     */
    public static function md5_sign($sign_data, $key) {
        return md5($sign_data . $key);
    }
    
    /**
     * 检验签名是否有效
     * type 默认为空，为admin的时候表示是后台调用
     * 赞 qiling 代码很好
     * @param array $param
     * @param string $type
     * 
     */
	public static function check_sign($param, $type = '') {
		if(!isset($param['platform']))
		    $param['platform'] = Comm_Context::form_param('platform', 1);		//平台


		//后台调用
		if($type == 'admin' && $param['platform'] != '999') {
			$data = Comm_Util::i18n('controller.aj.common.invalid_platform');
			Tool_Check::render_ajax($data);
			return;				
		}

		//签名判断
		$model_platform_info = new Model_Platform_info();
		$platform_info = $model_platform_info->get_platform_info($param['platform']);
		if (!$platform_info) {
			$data = Comm_Util::i18n('controller.aj.common.invalid_platform');
			Tool_Check::render_ajax($data);
			return;
		}


		$sign = Tool_Sign::generate_sign($param, $platform_info['key']);

		if(!isset($param['sign']))
			$param['sign'] = Comm_Context::form_param('sign', '');

		if ($sign != $param['sign']) {
			$data = Comm_Util::i18n('controller.aj.common.sign_error');
			Tool_Check::render_ajax($data);
			return;
		}
	}

    public static function check_sign_simple($param,$key,$sign_filter = null)
    {

        if (!empty($sign_filter)) {
            foreach ($sign_filter as $value) {
                unset($param[$value]);
            }
        }
        $sign = Tool_Sign::generate_sign($param, $key);

        if (!isset($param['sign']))
            $param['sign'] = Comm_Context::form_param('sign', '');

        if ($sign != $param['sign']) {
            $data = Lib_Config::i18n("common.sign_error");
            Tool_Check::render_ajax($data);
            exit;
        }
    }
}
