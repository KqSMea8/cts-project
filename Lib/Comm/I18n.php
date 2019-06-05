<?php
/**
 * 多语言支持
 *
 * @package Swift
 * @copyright copyright(2011) weibo.com all rights reserved
 * @author weibo.com php team
 */
class Comm_I18n {
	const MODE_FLATTEN_PACKAGES = 'deep_keys';
	const MODE_FLATTEN_KEYS = 'deep_packages';
	
    /**
     * @var string 当前语言
     */
    public static $current_lang = 'zh-cn';
    
    /**
     * @var array 按package, file结构进程内缓存避免重复IO
     */
    public static $lang;
    
    protected static $mode = self::MODE_FLATTEN_KEYS;
    
    /**
     * 设置当前的语言
     *
     * @param string 格式：zh-cn
     * @return string
     */
    public static function set_current_lang($lang) {
        self::$current_lang = strtolower(str_replace(array(' ', '_'), '-', $lang));
    }
    
    public static function flatten_packages_mode(){
    	self::$mode = self::MODE_FLATTEN_PACKAGES;
    }
    
    public static function flattern_keys_mode(){
    	self::$mode = self::MODE_FLATTEN_KEYS;
    }
    
    /**
     * 获取单个或多项目
     *
     * @param string dot path
     * @return string
     */
    public static function text($key) {
        self::load($key);
        
        list($package, $key) = self::split_key($key);
        
        $found = Comm_Array::path(self::$lang[$package], $key, $key);
        return $found;
    }
    
    public static function dynamic_text($key, $val1, $val2 = null){
    	$args = func_get_args();
    	array_shift($args);
    	$text = self::text($key);
    	return vsprintf(preg_replace('/%(\d)%/', '%$1$s', strval($text)), $args);
    }
    
    public static function _($key){
    	return self::text($key);
    }
    
    /**
     * 返回指定语言和分组的所有信息
     *
     * @param string $key 需要载入的语言
     * @return array
     */
    public static function load($key) {
        if (empty($key)){
            throw new Comm_Exception_Program('language key is required');
        }
        
        list($package) = self::split_key($key);
        
        // 预判
        if (isset(self::$lang[$package])) {
            return;
        }
        
        // 处理路径
        $path = APPPATH . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR;
        $path .= self::$current_lang . DIRECTORY_SEPARATOR . $package . ".php";
        
        // 只在APPPATH中查找
        if (!file_exists($path)) {
            throw new Comm_Exception_Program("language file \"" . self::$current_lang . ":{$package}\" not exists");
        }else{
        	$lang = Swift_Core::load($path);
        }
        
        // 保存
        self::$lang[$package] = $lang;
    }

    protected static function split_key($key){
    	//安全起见，将连续的多个"."认为是一个"."
    	$path = explode('.', preg_replace('#\.{2,}#', '.', $key));
    	if(self::$mode === self::MODE_FLATTEN_KEYS){
	    	$actural_key = array_pop($path);
	        $package = implode(DIRECTORY_SEPARATOR, $path);
    	}else{
    		$package = array_shift($path);
    		$actural_key = implode('.', $path);
    	}
    	return array($package, $actural_key);
    }
}