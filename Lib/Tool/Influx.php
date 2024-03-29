<?php
/*
 * InfluxDB操作工具
 */

class Tool_Influx {
    /*
     * 插入数据
     */
    public static function write($db_name, $table_name, $fields, $tags = '', $timestamp = '') {
        if (empty($db_name) || empty($table_name) || !is_array($fields))
            return false;
        try {
//            $url = "http://120.27.32.179:18086/write?db={$db_name}"; //阿里云部署的influxdb  --已废弃
            $url = "http://10.77.113.181:18086/write?db={$db_name}";  //微博内部influxdb

            $arr_fileds = array();
            if ($fields) {
                foreach ($fields as $k => $v) {
                    $arr_fileds[] =  $k.'='.$v;
                }
            }
            $str_fileds = implode(',', $arr_fileds);

            $arr_tags = array();
            if ($tags) {
                foreach ($tags as $k => $v) {
                    $arr_tags[] = $k.'='.$v;
                }
            }
            $str_tags = implode(',', $arr_tags);
            if ($timestamp) {
                //influxdb的time单位是纳秒, 且是唯一索引,  所以也无妨如果指定了秒级时间戳,需要转换成纳秒级的时间戳
                list($usec, $sec) = explode(" ", microtime());
                $time = (intval($timestamp * pow(10,9)) + intval($usec * pow(10,8)));

            }

            $str_post = "{$table_name}";
            if ($str_tags) {
                $str_post .= ",{$str_tags}";
            }
            if ($str_fileds) {
                $str_post .= " {$str_fileds}";
            }
            if ($time) {
                $str_post .= " {$time}";
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FAILONERROR, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $str_post);
            $headers = array('content-type: application/x-www-form-urlencoded;charset=UTF-8');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new Exception(curl_error($ch), 0);
            } else {
                $http_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                //注：服务器不返回任何数据，状态码为204
                if ( 204 !== $http_status_code) {
                    throw new Exception($response, $http_status_code);
                }
            }
            curl_close($ch);
            return $response;

        } catch (Exception $e) {
            Tool_Log::info("TOOL INFLUX WRITE CATCH EXCEPTION: {$e->getMessage()} request params:".json_encode(func_get_args()));
        }
        return false;
    }

}

