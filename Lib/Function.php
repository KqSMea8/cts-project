<?php

/*
 * 公用函数
 *
 *
 */

class Lib_Function
{
    /**
     * redis hash
     * @param  [string] $redis key       [description]
     * @param  [interger] $hash_part [description]
     * @return [interger]            [hash value]
     */
    public static function redisHash($key, $hash_part)
    {
        if (empty($key) || is_array($key) || empty($hash_part)) {
            return 0;
        }

        $md5_key = md5($key);
        $key_len = strlen($md5_key);

        for ($i = $key_len - 4; $i < $key_len; $i++) {
            $ascii_int .= ord($md5_key[$i]);
        }
        
        return $ascii_int % $hash_part;
    }

}
