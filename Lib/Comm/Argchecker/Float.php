<?php
class Comm_Argchecker_Float {
    public static function basic($data) {
        return is_float($data);
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
}