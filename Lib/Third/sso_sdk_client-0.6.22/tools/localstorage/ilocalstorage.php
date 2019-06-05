<?php
/**
 * 本地存储实现接口
 */

interface Sso_Sdk_Tools_Localstorage_ILocalStorage {
	/**
	 * @return null | string
	 */
	public function get();

	/**
	 * @param $val
	 * @return bool
	 */
	public function set($val);

	/**
	 * @return bool
	 */
	public function delete();

	/**
	 * 返回存储位置的字符串描述
	 * @return string
	 */
	public function get_storage_uri();
}
