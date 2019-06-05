<?php
class Tool_Formatter_Face {
    public static $face_type = array(
        'small' => 1, 
        'medium' => 1,
        'big' => 1,
    );
    
    public static $face_urls = array();
    
    /**
     * 判断用户是否上传了头像(规则:URL的倒数第二位参数为0 例如:http://xxx.xxx.xxx/xx/xx/0/xx)
     * 
     * @return BOOL
     */
    
    public static function check_has_profile_image($profile_image_url) {
    	return true;
    	if($profile_image_url) {
            $tmp_arr = explode('/', $profile_image_url);
            if($tmp_arr[count($tmp_arr)-2] == 0) {
                return FALSE;
            } else {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    /**
     * 将默认头像格式化为V4版对应头像
     * @param string $gender
     * @param string $type
     */
    public static function format_profile_image($gender, $type = 'medium') {
        $type = isset(self::$face_type[$type]) ? $type : 'medium';
        $key = $gender . '_' . $type;
        if(!isset(self::$face_urls[$key])) {
            $profile_image_url = Comm_Util::conf("env.css_domain").'style/images/face/' . Tool_Formatter_Gender::format_to_enname($gender) . '_' . $type. '.png';
            self::$face_urls[$key] = $profile_image_url;
        } else {
            $profile_image_url = self::$face_urls[$key];
        }
        return $profile_image_url;
    }
    
    /**
     * 将默认头像转化为带上传提示的头像
     * @param string $profile_image_url
     */
    public static function convert_to_upload($profile_image_url) {
        return str_replace('.png', '_uploading.png', $profile_image_url);
    }
}