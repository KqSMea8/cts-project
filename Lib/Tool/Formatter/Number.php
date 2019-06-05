<?php
class Tool_Formatter_Number{
	static function number($num){
		$num += 0; 
        if($num<99999) return $num; 
        $num /= 10000; 
        return floor($num)."万"; 		
	}
}