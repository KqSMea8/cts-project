<?php
class Tool_Rsa
{
	/**
	 * 返回Rsa加密后的字符串
	 *
	 * @param array $arr_para   是否需要urlencode处理
	 * @param string $key   key需要支付宝提供
	 * @param boolean $is_urlencode
	 * @return string
	 */
	public static function generate_sign_str(array $arr_para, $is_urlencode = false) {
		$sort_sign_data = self::arr_sort($arr_para);
		$arr_sign_data  = self::filter_sign_data($sort_sign_data);
		$str_sign_data  = self::get_link_string($arr_sign_data, $is_urlencode);
		
		return $str_sign_data;
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
	public static function rsa_sign($sign_data) {
		$key_path = T3PPATH . '/alipay/key/rsa_private_key.pem' ;
		unset($sign_data['type']) ;
		$sign_str = self::generate_sign_str($sign_data) ;
		$priKey = file_get_contents($key_path);
	    $res = openssl_get_privatekey($priKey);
	    openssl_sign($sign_str, $sign, $res);
	    openssl_free_key($res);
		//base64编码
	    $sign = base64_encode($sign);
	    return $sign;
	}
	
	/**
	 * RSA验签
	 * @param $data 待签名数据
	 * @param $ali_public_key_path 支付宝的公钥文件路径
	 * @param $sign 要校对的的签名结果
	 * return 验证结果
	 */
	public static function rsa_verify($data, $sign)  {
		$public_key_path = T3PPATH . '/alipay/key/rsa_public_key.pem' ;
		unset($data['type']) ; 
		$str_data = self::generate_sign_str($data);
		
		$pubKey = file_get_contents($public_key_path);
		$res = openssl_get_publickey($pubKey);
		$result = (bool)openssl_verify($str_data, base64_decode($sign), $res);
		openssl_free_key($res);
		return $result;
	}
}