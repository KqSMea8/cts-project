<?php
class Comm_Argchecker_Enum {
    
	public static function enum($data, $enumerates){
		$args = func_get_args();
		array_shift($args);
		
		return in_array(strval($data), $args, true);
	}
}