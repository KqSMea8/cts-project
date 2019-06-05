<?php

/**
 * 计数器
 * Class Sso_Sdk_Tools_Counter_ICounter
 */
interface Sso_Sdk_Tools_Counter_ICounter{
	/**
	 * 当计数超过最大值时，设置过期时间
	 * @param $type string 计数器ID
	 * @param $val int 一般为最大值
	 * @param $expire int 过期时间
	 * @return mixed
	 */
	public function set($type, $val, $expire);

	/**
	 * 增加一个指定类型的计数
	 * @param $type string 计数器ID
	 * @param $expire int 过期时间
	 * @return int
	 */
	public function incr($type, $expire);

	/**
	 * 获取指定类型的计数
	 * @param $type string 计数器ID
	 * @return int
	 */
	public function get($type);

	/**
	 * 重置指定的计数器
	 * @param $type string 计数器ID
	 * @return int
	 */
	public function reset($type);

	/**
	 * 返回计数器存储位置的序列化表示
	 * @return string
	 */
	public function get_storage_uri();
}