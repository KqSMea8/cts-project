<?php
/**
 * Created by JetBrains PhpStorm.
 * User: shiliang5
 * Date: 14-9-5
 * Time: 下午5:17
 * To change this template use File | Settings | File Templates.
 */
class Tool_Randcode {
    const RANDOM_STR = 'abcdefghijklmnopqrstuvwxyz0123456789';
    public  function create_datas($params, $count) {
        $retry = $count;
        $validate = self::validate($params['length'], $params['numeric'], $params['alpha'], $count);
        if (false == $validate) {
            return false;
        }
        $index = 0;
        $datas = array();
        while ($index <= $count) {
            $str = self::get_random($params['length'], $params['numeric'], $params['alpha']);
            $datas[] = $str;
            $index++;
        }
        $datas = array_unique($datas);
        $index = count($datas);
        while ($index <= $count && --$retry) {
            $str = self::get_random($params['length'], $params['numeric'], $params['alpha']);
            $datas[] = $str;
            $datas = array_unique($datas);
            $index = count($datas);
        }
        array_shift($datas);
        return $datas;
    }
    public static  function get_random($length, $is_numeric, $is_alpha) {
        $str = self::RANDOM_STR;//'abcdefghijklmnopqrstuvwxyz0123456789';
        $randString = '';
        $len = strlen($str)-1;
        $start = $is_alpha ? 0 : 26;
        $end = $is_numeric ? $len : 25;
        if ($start > $end) {
            $start = 0;
            $end = $len;
        }
        for($i = 0;$i < $length;$i ++){
            $num = mt_rand($start, $end);
            $randString.= $str[$num];
        }
        return $randString;
    }
    public static  function validate($length, $is_numeric, $is_alpha, $count) {
        $total = 0;
        if ($is_numeric && $is_alpha || !$is_numeric && !$is_alpha) {
            $total = pow(strlen(self::RANDOM_STR), $length);
        }
        elseif ($is_numeric) {
            $total = pow(10, $length);
        }
        elseif ($is_alpha) {
            $total = pow(26, $length);
        }
        if ($total < $count) {
            return false;
        }
        else {
            return true;
        }
    }

    /**
     * 生成$exist_coupons 中不存在的 特定数量的 不重复 随机数
     * @param $params{'count', 'length', 'numeric', 'alpha'}
     * @param array $exist_coupons
     * @param int $retry 重试次数
     * @return array|bool
     */
    public static function generate_unique_datas ($params, $exist_coupons = array(), $retry = 3) {
        $is_success = false;
        $coupons = array();
        while (!$is_success && $retry) {
            $retry  = $retry - 1;
            $coupons = self::create_datas($params, $params['count']);
            //长度设置过短
            if (false == $coupons) {
                return false;
            }
            $coupons = array_diff($coupons, $exist_coupons);
            $coupon_length = count($coupons);
            if ($params['count'] == $coupon_length) {
                $is_success = true;
            }
        }
        if (false == $is_success) {
            return false;
        }
        return $coupons;
    }
}