<?php
require_once T3PPATH.'/pin/pincode.php';
class Tool_Analyze_SaasCheck {
    private static $error_code = array(
        '20015' => true,
        '20016' => true,
        '20017' => true,
        '20018' => true,
        '20019' => true,
        '20020' => true,
        '20021' => true,
        '20022' => true,
    	'20023' => true,
        '20031' => true, //验证码错误
    	'20507' => true, //验证码错误
    	'20033' => true, //异地登陆验证码
        '20032' => true,
    	'20308' => true, //你今天给太多人发过私信了，休息一下吧~
    	'20131' => true, //转发恶意链接
    );
    
    /**
     * 根据code返回对应的saas错误信息
     * @param unknown_type $code
     */
    public static function get_err_info($code) {
        $err_info = array();
        if(isset(self::$error_code[$code])) {
            if('20031' == $code||'20507' == $code || '20033' == $code) {
                $err_info['msg'] = '';
                $err_info['riacode'] = Comm_Util::conf('riacode.sass');
            } else {
                $err_info['msg'] = Comm_Util::i18n('ajax.saas.saas_error_' . $code);
                $err_info['riacode'] = Comm_Util::conf('riacode.error');
            }
        }
        return $err_info;
    }

	public static function check_retcode($retcode){
		$ip_long = Comm_Context::get_client_ip(true);
		$key = md5($ip_long . $_COOKIE[pincode::CODE_NAME]);
		if($key == $retcode){
			return true;
		}else{
			return false;
		}
	}
}
