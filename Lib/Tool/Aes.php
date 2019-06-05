<?php
/**
 * AES加密解密工具类
 * @package    tool
 * @copyright  copyright(2012) weibo.com all rights reserved
 * @author     Stephen <zhangdi3@staff.sina.com.cn>
 */
class Tool_Aes{
	CONST KEY  = '377483B16EAF1CF4AA37E33BA74EDC82';
		
	/** AES加密
	 * $text : 要加密的字符串
	 */
	public static function ecryptdString($text){
		$key   = pack("H*",self::KEY);
		$pad   = 16 - (strlen($text) % 16);
		$text .= str_repeat(chr($pad), $pad);
		return bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $text, MCRYPT_MODE_ECB));
	}
	
	
	/** AES解密
	 * $crypttext : 要解密的字符串
	 */
	public static function decryptString($crypttext){
		$key   = pack("H*",self::KEY);
		$crypttext = pack("H*",$crypttext);
		$text =  mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $crypttext, MCRYPT_MODE_ECB);
		$pad   = 16 - (strlen($text) % 16);
		$text .= str_repeat(chr($pad), $pad);
		return $text;
	}
}