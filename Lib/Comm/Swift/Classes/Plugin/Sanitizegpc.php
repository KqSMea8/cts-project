<?php

class Swift_Plugin_SanitizeGpc extends Swift_Plugin{
	
    /**
     * 递归过滤GPC
     * 还原magic quote
     * \r\n或\r替换为\n
     *
     * @param mixed any variable
     * @return mixed sanitized variable
     */
    public static function sanitize($value) {
        static $magic_quotes = null;
        if ($magic_quotes === null) {
            $magic_quotes = (bool)get_magic_quotes_gpc();
        }
        
        if (is_array($value) or is_object($value)) {
            foreach ($value as $key => $val) {
                // 递归的标准化每个输入
                $value[$key] = Swift_Plugin_SanitizeGpc::sanitize($val);
            }
        } elseif (is_string($value)) {
            if ($magic_quotes === true) {
                $value = stripslashes($value);
            }
            
            if (strpos($value, "\r") !== false) {
                // 标准化换行
                $value = str_replace(array("\r\n", "\r"), "\n", $value);
            }
        }
        
        return $value;
    }
	
	/* (non-PHPdoc)
	 * @see Swift_Plugin::run()
	 */
	public function run() {
        $_GET = Swift_Core::sanitize($_GET);
        $_POST = Swift_Core::sanitize($_POST);
        $_COOKIE = Swift_Core::sanitize($_COOKIE);
	}
	
}