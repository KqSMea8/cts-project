<?php

/**
 * 各种API的各种工具、处理过程
 *
 */
class Comm_Weibo_Api_Util{
    /**
     * 检查是否为整型数值
     * @param unknown_type $val
     * @throws Comm_Exception_Program
     */
    public static function check_int($val) {
        if(!preg_match('/^\d+$/', $val)) {
            throw new Comm_Exception_Program('the type of the batch parameters must be int: '.$val);
        }
    }
    
    /**
     * 
     * 检查批量传入的int型参数格式
     * @param string $val
     */
    public static function check_batch_values($val, $type, $delimit = ',', $max = false, $min = false) {
    	if(empty($val)){
    		return $val;
    	}
        $values = explode($delimit, $val);
        switch($type) {
            case 'int':
            case 'int64':
                foreach($values as $k => $v) {
                    self::check_int($v);
                }
                break;
        }
        $count = count($values);
        if($max && $count > $max) {
            throw new Comm_Exception_Program('the number of the batch values must be less than' . $max);
        }
        if($min && $count < $min) {
            throw new Comm_Exception_Program('the number of the batch values must be greater than' . $min);
        }
        return $val;
    }
    
    /**
     * 检查二者必选且仅可选其一参数 callback方法
     * @param Comm_Weibo_Api_Request_Platform $platform platform对象
     * @param string $one 参数1
     * @param string $other 参数2
     */
    public static function check_alternative($one, $other, Comm_Weibo_Api_Request_Platform $platform){
        if((is_null($platform->$one) xor is_null($platform->$other)) === FALSE){
          throw new Comm_Exception_Program("one of the {$one} and {$other} params must be send, and can only send one!");
        }
    }
    
    /**
     * 定义2个批量参数互斥规则
     * @param array $actual_names uid和screen_name参数名组合
     */
    public static function one_or_other_multi($request, $actual_names = array(array('uid', 'screen_name'))){
        foreach($actual_names as $actual_name) {
            $request->add_rule($actual_name[0], "string");
            $request->add_rule($actual_name[1], "string");
        }
        $request->add_before_send_callback('Comm_Weibo_Api_Util', "check_alternative_multi", array($actual_names));
    }
    
    /**
     * 检查多个二者必选且仅可选其一参数 callback方法
     * @param object $platform 对象
     * @param array $actual_names 参数名组合
     * @throws Comm_Exception_Program
     */
    public static function check_alternative_multi($actual_names, $platform){
        foreach($actual_names as $actual_name) {
            self::check_alternative($actual_name[0], $actual_name[1], $platform);
        }
    }
}