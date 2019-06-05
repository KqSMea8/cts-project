<?php

/*
 * 配置文件读取工具
 *
 * TODO: 增加静态缓存
 *
 */

define('PATH_CONFIG', PATH_ROOT . DS . "Config");

class Lib_Config {


    // $path 是相对PATH_CONFIG 的子目录
    public static function get($key, $path = '') {

        $path = empty($path) ?  PATH_CONFIG : PATH_CONFIG . DS . $path;

        $config = array();
        if (empty($path)) {
            return $config;
        }

        $pathArr = explode('.', $key, 2);
        $configFilePhp =  $path . DS . $pathArr[0] . '.php';
        if (file_exists($configFilePhp)) {
            $configPhp = include($configFilePhp);
            $config = $config +$configPhp;
        }
        $configFileIni =  $path. DS . $pathArr[0] . '.ini';
        if (file_exists($configFileIni)) {
            $configIni = parse_ini_file($configFileIni);
            $config = $config + $configIni;
        }
        if (!empty($pathArr[1])) {
            $nodeArr = explode('.', $pathArr[1]);
            $configTmp = $config;
            foreach ($nodeArr as $node) {
                if(isset($configTmp[$node])){
                    $configTmp = $configTmp[$node];
                }else{
                    return $key;
               }
            }
            $config = $configTmp;
        }
        return $config;
    }


    public static function i18n($key, $params='') {
        $result = self::get($key , 'i18n'. DS . 'zh-cn');
        if(is_array($params)){
            foreach($params as $key=>$val){
                $result['msg'] = str_replace('{' . $key . '}', $val, $result['msg']);
            }
        }
        return $result;
    }
}
