<?php
/**
 * 解析、加载配置文件函数
 * @author     chunyu7
 */
class Ini_Load {


    /**
     *
     * 加载配置文件
     * @param unknown_type $path
     */
    public static function load_ini($path) {
        $arrIni = array();
        //不存在此目录
        if(!is_dir($path)){
            return false;
        }
        $arrFileName = scandir($path);
        if(empty($arrFileName)){
            return false;
        }
        foreach($arrFileName as $v){
            if(in_array($v,array('.','..'))){
                continue;
            }
            $fileInfo = pathinfo($v);
            $ext = $fileInfo ['extension'];
            if($ext != 'ini'){
                continue;
            }
            $arrIni[$fileInfo['filename']] = self::parse_ini_file_multi($path.'/'.$v,true);
        }
        $GLOBALS['APP_INI'] = $arrIni;
        return true;
    }

    /**
     *
     * 获取$key指定的相关配置
     * @param unknown_type $key
     */
    public static function get_ini($key) {
        if (strpos($key, '.') !== false) {
            list($file, $path) = explode('.', $key, 2);
        }else{
            $file = $key;
        }
        if(empty($GLOBALS['APP_INI']) || empty($GLOBALS['APP_INI'][$file])){
            return NULL;
        }
        if (isset($path)) {
            $val = Comm_Array::path($GLOBALS['APP_INI'][$file], $path, "#not_found#");
            if ($val === "#not_found#"){
                return NULL;
            }
            return $val;
        }else{
            // 获取整个配置
            return $GLOBALS['APP_INI'][$file];
        }
    }

    /**
     *
     * 解析多维(PHP官方)
     * @param unknown_type $file
     * @param unknown_type $process_sections
     * @param unknown_type $scanner_mode
     */
    public static function parse_ini_file_multi($file, $process_sections = false, $scanner_mode = INI_SCANNER_NORMAL) {
        $explode_str = '.';
        $escape_char = "'";
        // load ini file the normal way
        //$data = parse_ini_file($file, $process_sections, $scanner_mode);
        $data = parse_ini_file($file, $process_sections);
        if (!$process_sections) {
            $data = array($data);
        }
        foreach ($data as $section_key => $section) {
            // loop inside the section
            foreach ($section as $key => $value) {
                if (strpos($key, $explode_str)) {
                    if (substr($key, 0, 1) !== $escape_char) {
                        // key has a dot. Explode on it, then parse each subkeys
                        // and set value at the right place thanks to references
                        $sub_keys = explode($explode_str, $key);
                        $subs =& $data[$section_key];
                        foreach ($sub_keys as $sub_key) {
                            if (!isset($subs[$sub_key])) {
                                $subs[$sub_key] = array();
                            }
                            $subs =& $subs[$sub_key];
                        }
                        // set the value at the right place
                        $subs = $value;
                        // unset the dotted key, we don't need it anymore
                        unset($data[$section_key][$key]);
                    }
                    // we have escaped the key, so we keep dots as they are
                    else {
                        $new_key = trim($key, $escape_char);
                        $data[$section_key][$new_key] = $value;
                        unset($data[$section_key][$key]);
                    }
                }
            }
        }
        if (!$process_sections) {
            $data = $data[0];
        }
        return $data;
    }
}