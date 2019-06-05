<?php

/**
 * 数组工具函数
 *
 */


class Lib_Array {
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
    public static function filterNull(array $arr_para) {
        foreach ($arr_para as $k => &$v) {
            if (is_null($v)) {
                unset($arr_para[$k]);
            }
        }
        return $arr_para;
    }

    /**
     * 剔除数组的value为空或者为Null的key
     *
     * @param array $arr_para
     * @return array
     */
    public static function filterEmpty(array $arr_para) {
        $arr_para = self::filterNull($arr_para);
        foreach ($arr_para as $k => &$v) {
            if ('' === $v) {
                unset($arr_para[$k]);
            }
        }
        return $arr_para;
    }


}