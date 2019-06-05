<?php
/**
 * 省、市、县
 * @author: zhongfeng <zhongfeng@staff.weibo.com>
 * @date: 2017/2/22
 */
class Lib_Area{
    protected static $config;
    protected static $arr_province = array();
    protected static $arr_city = array(
        '其它'=>array('其它'),
    );

    public static function getProvince(){
        $arr_code = array();
        if(empty(self::$arr_province)) {
            self::$config = Lib_Config::get('area');
            foreach (self::$config as $key => $val) {
                list($name, $parent) = $val;
                if ($parent == '1') {//省份
                    $arr_code[$key] = $name;
                    self::$arr_province[$name] = array(
                        'name'=>$name,
                        'city'=>array(),
                    );
                }else if(isset($arr_code[$parent])){
                    self::$arr_province[$arr_code[$parent]]['city'][] = $name;
                }
            }
        }
        self::$arr_province['其它'] = array(
            'name'=>'其它',
            'city'=>array('其它'),
        );
        return self::$arr_province;
    }
    public static function getCity($province_name){
        $province = self::getProvince();
        if(!isset($province[$province_name])){
            return array();
        }
        return self::$arr_province[$province_name]['city'];
    }
    public static function code2long($province,$city,$district){
        $address = array();
        $address['province'] = $province.'0000';
        //直辖市
        if(self::is_zhixia($province)){
            $address['city'] = $province.str_pad($city, 2,'0',STR_PAD_LEFT)."00";
            $address['area'] = "";
        }
        else{
            $address['city'] = $province.str_pad($city, 2,'0',STR_PAD_LEFT).'00';
            $address['area'] = $province.str_pad($city, 2,'0',STR_PAD_LEFT).str_pad($district, 2,'0',STR_PAD_LEFT);
        }
        return $address;
    }
    /*
	 * 判断是否直辖
	 * */
    public static function is_zhixia($province_code){
        $zhixia = array(
            '11',//北京
            '12',//天津
            '50',//重庆
            '31',//上海
            '71',//台湾
            '81',//香港
            '82',//澳门
        );
        return 	in_array($province_code, $zhixia);
    }
}