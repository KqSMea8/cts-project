<?php
/**
 * 功能描述
 * @author: zhongfeng <zhongfeng@staff.weibo.com>
 * @date: 2018/1/5
 */
class Data_Wbdh{

    const ALIAS = Model_Const::ALIAS_JINGCAI;
    const TABLE_TEST        = 'jingcai_test';
    const TABLE_ETC         = 'etc';


    public static function startTrans(){
        return Lib_Db::startTrans(self::ALIAS);
    }
    public static function rollback(){
        return Lib_Db::rollback(self::ALIAS);
    }
    public static function inTrans(){
        return Lib_Db::inTrans(self::ALIAS);
    }
    public static function commit(){
        return Lib_Db::commit(self::ALIAS);
    }
    public static function fetchAll($sql, $data=array(), $fetchIndex=false, $fromMaster=false){
        return Lib_Db::fetchAll(self::ALIAS, $sql, $data, $fetchIndex, $fromMaster);
    }
    public static function fetchOne($sql, $data=array(), $fetchIndex=false, $fromMaster=false){
        return Lib_Db::fetchOne(self::ALIAS, $sql, $data, $fetchIndex, $fromMaster);
    }
    public static function insert($table, $data){
        try {
            return Lib_Db::insert(self::ALIAS, $table, $data);
        } catch (Exception $e) {
            Lib_Log::warning($e->getMessage());
            return false;
        }
    }
    public static function update($table, $data, $where, $whereData = array()){
        try {
            return Lib_Db::update(self::ALIAS, $table, $data, $where, $whereData); 
        } catch (Exception $e) {
            Lib_Log::warning($e->getMessage());
            return false;
        }
    }
    public static function exec($sql, $data=array(), $fromMaster=false){
        return Lib_Db::exec(self::ALIAS, $sql, $data, $fromMaster);
    }
    public static function getPartTableNameByUid($name,$uid)
    {
        return Lib_Db::getPartTableNameByUid(self::ALIAS, $name, $uid);
    }

    public static function getList($table, $field, $where_str, $where_value_list = array(), $groupby = '', $orderby = '', $limit = 0)
    {
        $sql = "SELECT {$field} FROM `{$table}`";

        if (!empty($where_str)) {
            $sql .= " WHERE {$where_str}";
        }
        if (!empty($groupby)) {
            $sql .= " GROUP BY {$groupby}";
        }
        if (!empty($orderby)) {
            $sql .= " ORDER BY {$orderby}";
        }
        if (!empty($limit)) {
            $sql .= " LIMIT {$limit}";
        }
        
        return Lib_Db::fetchAll(self::ALIAS, $sql, $where_value_list);
    }

    public function getStat($table, $field, $where_str, $where_value_list = array())
    {
        $sql = "SELECT {$field} FROM `{$table}`";
        if (!empty($where_str)) {
            $sql .= " WHERE {$where_str}";
        }

        return Lib_Db::fetchOne(self::ALIAS, $sql, $where_value_list);
    }

    public static function updateByMap($table, $update_map, $where_list)
    {
        list($where_str, $where_value_list) = Lib_ParseSql::parseWhere($where_list);
        try {
            return Lib_Db::update(self::ALIAS, $table, $update_map, $where_str, $where_value_list);
        } catch (Exception $e) {
            Lib_Log::warning($e->getMessage());
            return false;
        }
    }

    public static function setWrite(){
        return Lib_Db::setWrite(self::ALIAS);
    }

    public static function setAuto()
    {
        return Lib_Db::setAuto(self::ALIAS);
    } 
}
