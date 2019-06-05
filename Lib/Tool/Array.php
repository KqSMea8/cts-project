<?php
/**
 * 数组工具函数 
 * 
 * @author      	liuyu6
 * @version 	    2013-11-15
 * @copyright  	copyright(2013) weibo.com all rights reserved
 *
 */
class Tool_Array {
    /**
     * 将所有参数是NULL的值都改成空
     * @param array $params
     * @return array
     */
    public static function null2empty(array $params) {        
        foreach ($params as &$p) {
            if (is_null($p)) {
                $p = '';
            }
        }
        
        return $params;
    }
    
    /**
     * 剔除数组的value为null的key
     * 
     * @param array $arr_para
     * @return array
     */
    public static function filter_null(array $arr_para) {
        foreach($arr_para as $k=>&$v) {
            if(is_null($v)) {
                unset($arr_para[$k]);
            }
        }
        return $arr_para;
    }
    
    /**
     * 剔除数组的value为空字符串|0|'0' 等的key
     * 
     * @param array $arr_para
     * @return array
     */
    public static function filter_empty(array $arr_para) {
        foreach($arr_para as $k=>&$v) {
            if('' === $v) {
                unset($arr_para[$k]);
            }
        }
        return $arr_para;
    }
    
    /**
     * 过滤掉value 为null或empty的key，直接unset
     * @param array $params
     * @return array
     */
    public static function array_filter(array $params) {
        // 过滤null 以及 empty 的value 的key
        $params = Tool_Array::filter_null($params);
        $params = Tool_Array::filter_empty($params);
    
        return $params;
    }
    
    /**
     * 返回关联数组的values
     * 
     * @param array $arr
     * @return string
     */
    public static function array_values_recursive(array $arr) {
        $array_values = array();
    
        foreach ($arr as $value) {
            if (is_scalar($value) OR is_resource($value)) {
                $array_values[] = $value;
            }elseif (is_array($value)) {
                $array_values = array_merge($array_values, self::array_values_recursive($value));
            }
        }
        
        return $array_values;
    }
}