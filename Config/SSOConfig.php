<?PHP
/**
 * SSOConfig class file.
 *
 * @package SSOClinent
 * @author lijunjie <junjie2@staff.sina.com.cn>
 * @author liuzhiyu <zhiyu@staff.sina.com.cn>
 * @copyright Copyright (c) 2011 SINA R&D Centre
 * @version $Id: SSOConfig.php 34552 2011-03-17 03:38:11Z zhiyu $
 */

class SSOConfig {
	/**
	 * 服务名称，产品名称，应该和entry保持一致
	 */
	const SERVICE			= '';
	/**
	 * 应用产品entry, 获取用户详细信息使用，由统一注册颁发的
	 */
	const ENTRY				= '';
	/**
	 * 应用产品pin, 获取用户详细信息使用，由统一注册颁发的
	 */
	const PIN				= '';
	/**
	 * domain of cookie, 您域名所在的根域，如“.sina.com.cn”，“.51uc.com”
	 */
	const COOKIE_DOMAIN		= '.weibo.com';
	/**
	 * 如果只需要根据sina.com.cn域的cookie就可以信任用户身份的话，可以设置为false，这样不需要验证service ticket，省一次http的调用
	 */
	const USE_SERVICE_TICKET= true;
	/**
	 * 是否使用微博自动登录
	 * 暂时只有微博主站可设置为true
	 */
	const USE_WEIBO_ALC		= false;
	/**
	 * 使用RSA加密cookie验证
	 * 如果需要使用请设置为true
	 */
	const USE_RSA_SIGN		= false;
}