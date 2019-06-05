<?php
/**
 * 配置类
 *
 * @package Swift
 * @copyright copyright(2011) weibo.com all rights reserved
 * @author weibo.com php team
 */

class Comm_Config {
    /**
     * 加载指定的配置文件
     *
     * @param string 映射configuration文件名
     * @return array
     */
    public static function load($config_file) {
        $files = Swift_Core::find_file('config', $config_file, true);
        if (empty($files)) {
            throw new Comm_Exception_Program("config file not exists");
        }
        
        $config = array();
        
        foreach ($files as $file) {
            $config = Comm_Array::merge($config, Swift_Core::load($file));
        }
        
        return $config;
    }
    
    /**
     * 获取指定的配置项，如果$key不存在将报错
     * 进程内缓存，避免重复加载
     * 
     * @param string $key 支持dot path方式获取
     */
    public static function get($key) {
        static $config = array();
        
        if (strpos($key, '.') !== false) {
            list($file, $path) = explode('.', $key, 2);
        }else{
            $file = $key;
        }
        
        /*
        if (!isset($config[$file])) {
            $config[$file] = self::load($file);
        }
        */
        
        if (isset($path)) {
            $val = Comm_Array::path($config[$file], $path, "#not_found#");
            if ($val === "#not_found#"){
                throw new Comm_Exception_Program("config key not exists:" . $key);
            }
            
            return $val;
        }else{
            // 获取整个配置
            return $config[$file];
        }
    }
}
