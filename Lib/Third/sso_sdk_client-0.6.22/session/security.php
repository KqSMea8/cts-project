<?php

/**
 * 
 */

class Sso_Sdk_Session_Security {

	//===== 验证选项常量 ====
	const VERIFY_ITEM_ID          = 1;  // 验证的是身份证
	const VERIFY_ITEM_MOBILE        = 2;  // 验证的是手机

	//===== 验证结果常量 =====
	const VERIFY_RESULT_SUCC          = 1;//通过验证
	const VERIFY_RESULT_FAIL      = 2;//验证失败
	const VERIFY_RESULT_UNDEFINED   = 4;//无法验证
	
	private static $_version = 1;
	private static $_serialize_map = array(
		'vtime'        => array('index'=>1,    'pack'=>'timestamp'),
		'item'         => array('index'=>2,    'pack'=>'c1'),
		'result'       => array('index'=>3,    'pack'=>'c2')
	);
	private $_uid;
	Private $_tid;
	private $_vtime;
	private $_result;
	private $_item;
	

	public function __construct(array $data) {
		if (isset($data['uid']) && isset($data['tid']) && isset($data['vtime']) && isset($data['result']) ) {
			$this->_uid = $data['uid'];
			$this->_tid = $data['tid'];
			$this->_vtime = $data['vtime'];
			$this->_result = $data['result'];
			if ($data['result'] == self::VERIFY_RESULT_SUCC ? isset($data['item']) : true) {
				$this->_item = $data['item'];
			}else{
				throw new Exception('data error,need item value');
			}
		}else{
			throw new Exception('data error,need timestamp and result value');
		}
	}

	public function get($key){
		if (isset($this->$key)) {
			return $this->$key;
		}
	}
	
	/**
	 * 存储反序列化
	 *
	 * @param $str
	 *
	 * @throws Exception
	 * @return array
	 */
	public static function unserialize($str) {
		$version = ord($str{0});
		switch($version) {
			case 1:
				$arr = self::_unserialize_v1($str);
				break;
			default:
				return false;
// 				throw new Exception("unserialize fail");
		}

		if (!$arr['vtime'] || !$arr['result']) {
// 			throw new Exception("data is broken");
			return false;
		}
		return $arr;
	}
	
	/**
	 * 存储返回序列化版本1
	 *
	 * @param $str
	 *
	 * @return array
	 */
	private static function _unserialize_v1($str){
		$offset = 1;
		$len = strlen($str);
		$map = array();
		foreach(self::$_serialize_map as $key=>$item) {
			$map[$item['index']] = $key;
		}
		$arr = array();
		while($offset < $len) {
			$key = ord($str{$offset});
			$value_len = ord($str{$offset+1});
			if ($value_len == 0) {
				$offset += 2;continue;
			}
			$value = substr($str, $offset + 2, $value_len);
			$arr[$map[$key]] = $value;
			$offset = $offset + 2 + $value_len;
		}
		foreach($arr as $key => $val) {
			if (isset(self::$_serialize_map[$key]['pack'])) {
				$val = Sso_Sdk_Tools_String::unpack($val, self::$_serialize_map[$key]['pack']);
			}
			$arr[$key] = $val;
		}
		return $arr;
	}
}