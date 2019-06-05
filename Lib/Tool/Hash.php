<?php
class Tool_Hash {
	/**
	 * 得到一个订单id
	 * 
	 * @param string $uid 用户微博uid
	 * @param int $index 每个表的自增id
	 * @return string
	 */
    public static function get_order_id($uid, $index) {

        // --- oid算法：版本号（1位） + 分表名（4位） + 订单表的自增ID（8位）
        $table_id = Data_Abstract::get_table_id($uid);
        $table_id = sprintf("%04d", $table_id);
        $index = sprintf("%08d", $index);
        return "3{$table_id}{$index}";
    }
    
    /**
     * 从oid中获取table id，用于hash key
     * 
     * @param string $oid
     * @return boolean|string
     */
    public static function get_hash_key($oid) {
        if(strlen($oid) != 13)
            return false;
        
		return substr($oid, 1, 4);
    }
    /**
     * oid中获取真实的oid
     *
     * @param string $oid
     * @return boolean|string
     */    
    public static function get_real_oid($oid) {
        $index = substr($oid, 0, 1);
        if($index == 2) //新订单直接返回
            return $oid;

        return  "1991412". substr($oid, 5);
    }    
    /**
     * oid中获取版本号
     *
     * @param string $oid
     * @return boolean|string
     */
    public static function get_oid_version($oid) {
        $index = substr($oid, 0, 1);
        return  $index;
    }

}