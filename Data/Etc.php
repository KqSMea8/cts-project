<?php
/**
 * 功能描述
 * @author: zhongfeng <zhongfeng@staff.weibo.com>
 * @date: 2018/9/05
 */
class Data_Etc extends Data_Exchange {
    public static function getAppToken($appKey){
        $ret = self::getEtc(Model_Const::APP_TOKEN_ETC_NAME, Model_Const::APP_TOKEN_ETC_KEY1, $appKey);
        if(empty($ret)){
            return $ret;
        }
        return $ret[0]['val'];
    }
    protected static function getEtc($name, $key1='', $key2='', $key3='', &$where=false, &$whereData=false){
        $sql = 'select val from ' . self::TABLE_ETC . ' where ';
        $where = 'name=?';
        $whereData = array($name);
        if(!empty($key1)){
            $where .= " and key1=?";
            $whereData[] = $key1;
            if(!empty($key2)){
                $where .= " and key2=?";
                $whereData[] = $key2;
                if(!empty($key3)){
                    $where .= " and key3=?";
                    $whereData[] = $key3;
                }
            }
        }
        return self::fetchAll($sql . $where, $whereData);
    }
    public static function updateEtc($val, $name, $key1='', $key2='', $key3=''){
        $etc = self::getEtc($name, $key1, $key2, $key3, $where, $whereData);
        if($etc === false){
            return false;
        }
        if(empty($etc)){//add
            $data = array(
                'name'=>$name,
                'key1'=>$key1,
                'key2'=>$key2,
                'key3'=>$key3,
                'val'=> $val,
                'md5'=>md5($name . $key1 . $key2 . $key3 . $val),
                'ctime'=>date('Y-m-d H:i:s'),
            );
            return self::insert(self::TABLE_ETC, $data);
        }else{//update
            $data = array(
                'val'=> $val,
            );
            return self::update(self::TABLE_ETC, $data, $where, $whereData);
        }
    }
}