<?php

class Tool_Uuid {


    /**
     * 发号
     * @param $type
     * @return string
     * @throws Exception
     */
    public static function gen_uuid() {
        try{
            $data = array(
                'url'=>'http://i.api.weibo.com/2/uuid/next_id.json',
                'data'=>array(
                    'source'=>1941657700,
            ),
            );
            $token = Tool_Curl::request($data);
            if(!empty($token)){
                $token = json_decode($token,true);
                return $token['uuid'];
            }else{
                return $token;
            }
        }catch(Exception $e) {
            Tool_Log::fatal($e->getMessage());
        }
    }

}