<?php
/**
 * 性别格式化工具
 *
 * @package    Tool
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     Rodin <luodan@staff.sina.com.cn>
 */

/**
 * 性别相关的转换工具
 * @author Rodin <luodan@staff.sina.com.cn>
 */
class Tool_Formatter_Gender {

	/**
	 * 
	 * 将性别转换为用户友好的字符串
	 * @param string $gender 性别
	 * @return string
	 */
	public static function format_friendly($gender){
		return $gender ? (strtolower($gender) == 'f' ? Comm_I18n::text('tpls.common.user_female') : Comm_I18n::text('tpls.common.user_male')) : '';
	}
	
	public static function format_to_enname($gender) {
	    return strtolower($gender) == 'f' ? 'female' : 'male';
	}
	
	/**
	 * 
	 * 将性别转换为第三人称称谓
	 * @param string $gender 性别
	 * @return string "他"/"她"
	 */
	public static function format_to_third($gender){
		return strtolower($gender) == 'f' ? Comm_I18n::text('tpls.common.user_her') : Comm_I18n::text('tpls.common.user_he'); 
	}
}