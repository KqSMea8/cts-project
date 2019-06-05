<?php
/**
 * 功能描述
 * @author: zhongfeng <zhongfeng@staff.weibo.com>
 * @date: 2018/1/5
 */
class Lib_Db {
    static $lib;
    /**
     * @param $alias
     * @return Lib_Mysql
     */
    private static function getDb($alias){
        if(!isset(self::$lib[$alias])){
            self::$lib[$alias] = new Lib_Mysql($alias);
        }
        return self::$lib[$alias];
    }
    public static function startTrans($alias){
        return self::getDb($alias)->begintransaction();
    }
    public static function rollback($alias){
        return self::getDb($alias)->rollback();
    }
    public static function inTrans($alias){
        return self::getDb($alias)->intransaction();
    }
    public static function commit($alias){
        return self::getDb($alias)->commit();
    }
    public static function fetchAll($alias, $sql, $data, $fetchIndex=false, $fromMaster=false){
        return self::getDb($alias)->fetchAll($sql, $data, $fetchIndex, $fromMaster);
    }
    public static function fetchOne($alias, $sql, $data, $fetchIndex=false, $fromMaster=false){
        return self::getDb($alias)->fetchOne($sql, $data, $fetchIndex, $fromMaster);
    }
    public static function insert($alias, $table, $data){
        return self::getDb($alias)->insert($table, $data);
    }
    public static function update($alias, $table, $data, $where, $whereData = array()){
        return self::getDb($alias)->update($table, $data, $where, $whereData);
    }
    public static function exec($alias, $sql, $data, $fromMaster=false){
        return self::getDb($alias)->exec($sql, $data, $fromMaster);
    }
    public static function setWrite($alias){
        return self::getDb($alias)->setWrite();
    }

    public static function setAuto($alias){
        return self::getDb($alias)->setAuto();
    }



    /**
     * 根据UID获取分表后表名
     */
    public static function getPartTableNameByUid($alias,$name,$uid)
    {
        return self::getDb($alias)->getPartTableNameByUid($name,$uid);
    }

}
