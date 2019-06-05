<?php
class Comm_Argchecker_Int {
    public static function basic($data) {
        return (bool) preg_match('/^-?[\d]+$/iD', $data, $match);
    }
    
    public static function min($data, $min) {
        if ($data < $min) {
            return FALSE;
        }
        
        return TRUE;
    }
    
    public static function max($data, $max) {
        if ($data > $max) {
            return FALSE;
        }
        return TRUE;
    }    
    
    public static function range($data, $left_value, $right_value) {
        if ($left_value >= $right_value) {
            $min = $right_value;
            $max = $left_value;
        }
        else {
            $min = $left_value;
            $max = $right_value;
        }
        if ($data> $max || $data < $min) {
            return FALSE;
        }
        return TRUE;
    }
    
    public static function len($data, $min_len = 1, $max_len = NULL) {
        if (!$min_len ) {
            throw new Comm_Exception_Program('param_is_uncorrect');
        }
        
        if ($min_len && $max_len) {
            if ($min_len > $max_len) {
                $min_len = $min_len + $max_len;
                $max_len = $min_len - $max_len;
                $min_len = $min_len - $max_len;
            }
            if ($min_len == $max_len) {
                $match_string = '/\d{' . $min_len . '}/';
                
            }
            else {
                $match_string = '/\d{' . $min_len . ',' . $max_len . '}/';
            }
        }
        else {
            $match_string = '/\d{' . $min_len . ',}/';
        }
        
        return (BOOL) preg_match($match_string, $data, $match);   
    }
}