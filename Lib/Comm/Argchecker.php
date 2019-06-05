<?php
/**
 * 参数校验
 *
 * @package Swift
 * @copyright copyright(2011) weibo.com all rights reserved
 * @author weibo.com php team
 */
class Comm_Argchecker {
    const OPT_NO_DEFAULT = 1;
    const OPT_USE_DEFAULT = 2;
    const NEED = 3;
    const WRONG_NO_DEFAULT = 1;
    const WRONG_USE_DEFAULT = 2;
    const RIGHT = 3;
    
    /**
     * 检查int
     * 
     * @param mixed $data
     * @param string $rule	rule规则。如"max,5;min,-3;"。
     * @param enum $is_needed
     * @param enum $must_correct
     * @param mixed $default
     * @return bool
     * 
     * @throws Comm_Exception_Program
     */
    public static function int($data, $rule, $is_needed = 1, $must_correct = 1, $default = NULL) {
    	return self::run_checker('Comm_Argchecker_Int', $data, $rule, $is_needed, $must_correct, $default);
    }
    
    /**
     * 检查字符串
     * @param mixed $data
     * @param string $rule
     * @param enum $is_needed
     * @param enum $must_correct
     * @param mixed $default
     * @return bool
     */
    public static function string($data, $rule, $is_needed = 1, $must_correct = 1, $default = NULL) {
    	return self::run_checker('Comm_Argchecker_String', $data, $rule, $is_needed, $must_correct, $default);
    }
    
    /**
     * 检查浮点类型
     * @param mixed $data
     * @param string $rule
     * @param enum $is_needed
     * @param enum $must_correct
     * @param mixed $default
     * @return bool
     */
    public static function float($data, $rule, $is_needed = 1, $must_correct = 1, $default = NULL) {
    	return self::run_checker('Comm_Argchecker_Float', $data, $rule, $is_needed, $must_correct, $default);
    }

    /**
     * 检查枚举类型
     * @param mixed $data
     * @param string $rule
     * @param enum $is_needed
     * @param enum $must_correct
     * @param mixed $default
     * @return bool
     */
    public static function enum($data, $rule, $is_needed = 1, $must_correct = 1, $default = NULL) {
    	return self::run_checker('Comm_Argchecker_Enum', $data, $rule, $is_needed, $must_correct, $default);
    }
    
    /**
     * 检查多重数据
     * 如果规则的值里面包含逗号和分号，则需要将里面的逗号和分号转义为 \,和\;，否则会导致规则出错。比如：
     * 		delimeter,\,  //以 ,为delimeter规则的参数
     * 		delimeter2,\,,\;;delimeter3,'	//以","和";"分别为delimeter2规则的第一个参数和第二个参数，以"'"为delimeter3规则的第三个参数
     * @param mixed $data
     * @param string $rule 
     * @param enum $is_needed
     * @param enum $must_correct
     * @param mixed $default
     * @return bool
     */
    public static function datalist($data, $rule, $is_needed = 1, $must_correct = 1, $default = NULL) {
    	return self::run_checker('Comm_Argchecker_DataList', $data, $rule, $is_needed, $must_correct, $default);
    }
    
    /**
     * 将转义后的,和;解除转义
     * 
     * @param string $data
     * @return string
     */
    public static function extract_escaped_chars($data){
    	return str_replace(array('\,', '\;'), array(',', ';'), $data);
    }
    
    protected static function run_checker($argchecker_type, $data, $rule, $is_needed, $must_correct, $default){
        if (($return_data = self::get_value($data, $is_needed, $default)) !== TRUE) {
            return $return_data;
        }
        
        $parse_rules = self::parse_rules($argchecker_type, $rule);
        if ($parse_rules) {
            $data = self::validate($argchecker_type, $parse_rules, $data, $must_correct, $default);
        }
        
        return self::get_return($data, $is_needed, $must_correct, $default);
    }
    
    private static function parse_rules($argchecker_type, $rules) {
        $rules = preg_split('#(?<!\\\\);#', $rules);
        if (class_exists($argchecker_type) && method_exists($argchecker_type, 'basic')){
//        another possible approach to avoid the method_exists memleak under php <= 5.2.9          	
//        $class = new ReflectionClass($argchecker_type);
//        if ($class->hasMethod('basic')) {
            $parse_rules = array(array('method' => 'basic', 'para' => array()));
        }else{
        	$parse_rules = array();
        }
        if ($rules) {
            foreach ($rules AS $rule) {
                $rule = preg_split('#(?<!\\\\),#', $rule);
                $method_name = array_shift($rule);
                if(!$method_name){
                	continue;
                }
                if (!method_exists($argchecker_type, $method_name)) {
                    throw new Comm_Exception_Program('method_not_exist');
                }
                else {
                    $parse_rules[] = array('method' => $method_name,
                                           'para' => $rule);
                }
            }
        }  
        return $parse_rules;      
    }    
    
    private static function get_value($data, $is_needed, $default) {
        if (!in_array($is_needed, array(self::OPT_NO_DEFAULT, self::OPT_USE_DEFAULT, self::NEED))) {
            throw new Comm_Exception_Program('PARAM_ERROR');
        }        
        if ($data === NULL) { 
            // 可以没有，且不需要使用默认值
            if ($is_needed == self::OPT_NO_DEFAULT) {
                return NULL;
            }  
            // 可以没有，且需要使用默认值 
            if ($is_needed ==  self::OPT_USE_DEFAULT) {
                return $default;
            }
            // 必须要有
            if($is_needed == self::NEED) {
                throw new Comm_Exception_Program('PARAM_ERROR');
            }
        } 
        return True;       
    }
    

    private static function validate($argchecker_type, $rules, $data, $is_correct, $default) {
        if (!in_array($is_correct, array(self::RIGHT, self::WRONG_NO_DEFAULT, self::WRONG_USE_DEFAULT))) {
            throw new Comm_Exception_Program('PARAM_ERROR');
        }
        
        foreach ($rules AS $rule) {
			if(!$rule){
				continue;
			}
	        array_unshift($rule['para'], $data);
            $rst = call_user_func_array(array($argchecker_type, $rule['method']), $rule['para']);
            if ($rst === FALSE) {
                break;
            }
        }
        if ($rst === FALSE) {
            // 可以不对，且不需要使用默认值
            if ($is_correct == self::WRONG_NO_DEFAULT) {
                return NULL;
            }  
            // 可以不对，且需要使用默认值 
            if ($is_correct == self::WRONG_USE_DEFAULT) {
                return $default;
            }
            // 必须要对
            if($is_correct == self::RIGHT) {
                throw new Comm_Exception_Program('PARAM_ERROR');
            }            
        } 
        return $data;      
    }
    
    private static function get_return($data, $is_needed, $is_correct, $default) {
        if ($data === NULL && ($is_needed == self::OPT_USE_DEFAULT || $is_correct == self::WRONG_USE_DEFAULT)) {
            return $default;
            
        }
        return $data;
    }
}