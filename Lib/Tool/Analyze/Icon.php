<?php
/**
 * 表情解析
 * @package    Tool
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Tool_Analyze_Icon
{
	const LANG_SC = 'cnname';
	const LANG_TC = 'twname';
    public static $face_list_lite = array();
	
	protected static $face_list = array();
	
	/**
	 * 对内容中的表情文字做处理
	 * @param	string	$content 内容
	 * @return	string	$content 内容
	 */
	public static function text_to_icon($content) {
		//增加解析emoji表情
        try{
		//$content = Tool_Emoji::filter($content);
		self::load_face();
		$content = preg_replace_callback('/\[([\x{4e00}-\x{9fa5}a-zA-Z0-9]+)\]/us', "self::render_icon", $content);
		return $content;
        }catch(Exception $e){echo $e->getMessage();}
	}
	
	protected static function load_face(){
		if(self::$face_list){
			return true;
		}
		try{
			$face_list = array();
			$sc = self::get_face_list_lite('face', self::LANG_SC);
			if(is_array($sc)){
				$face_list = array_merge($face_list, $sc);
			}

			$tc = self::get_face_list_lite('face', self::LANG_TC);
			if(is_array($tc)){
				$face_list = array_merge($face_list, $tc);
			}
			
//			$tmp = array();
//			foreach($face_list as $v){
//				$tmp[$v['phrase']] = $v;
//			}
			self::$face_list = $face_list;
			return true;
		}catch (Exception $e){
		}
		return false;
	}
	
	/**
     * 
     * 将内容中表情文字替换为表情图标
     * @param $name 表情中文
     */
	function render_icon($name) {
		if(mb_strwidth($name[1],"utf-8") > 20){
			return "[{$name[1]}]";
		}
		$name_format = "[".$name[1]."]";
		$tmp = self::$face_list;
		if(isset($tmp[$name_format]) && $tmp[$name_format] && $tmp[$name_format]['url']) {
			$brand_face = array('[起亚律动]', '[起亞律動]');
		    if (in_array($name_format, $brand_face)) {
		        //表情支持商业新特性渲染lianwei2
		        $return = "<a target=\"_blank\" href=\"http://e.weibo.com/1784571423/app_3362719035?ref=biaoqing\"><img src=\"{$tmp[$name_format]['url']}\" brand_face=\"[{$name}]\" title=\"[{$name[1]}]\" alt=\"[{$name[1]}]\" type=\"face\" /></a>";
		    } else {
		        $return = "<img src=\"{$tmp[$name_format]['url']}\" title=\"[{$name[1]}]\" alt=\"[{$name[1]}]\" type=\"face\" />";
		    }
		} else {
			$return = "[{$name[1]}]";
		}
		return $return;
	}  	

	/*
	 *@TODO 按类型获取表情列表
	 *@param $face_type 表情类型
	 *@return array(do_face,do_face) 
	 */
	public static function get_face_list_lite($face_type = 'face',$language=NULL){
		$cache_key = $face_type . $language;
        if (!isset(self::$face_list_lite[$cache_key])) {
			try{			    
				/*$comm_weibo_api_face = Comm_Weibo_Api_Statuses::emotions();
				$comm_weibo_api_face->type =  $face_type;
				if(!is_null($language)){
					$comm_weibo_api_face->language = $language;
				}
				
				$arr_face = $comm_weibo_api_face->get_rst();*/

				$apiWeibo = new Api_Weibo();
				$arr_face = $apiWeibo->getEmotions($face_type,$language);
				
				if(!is_array($arr_face) || empty($arr_face)){
					throw new Comm_Weibo_Exception_Api("error_data");
				}
				foreach($arr_face as $one_face){
					//只保存需要的数据
					$required_data = array(
						'type' => $one_face['type'],
						'phrase' => $one_face['phrase'], 
						'url' => $one_face['url']
					);
					$do_faces[$one_face['phrase']] = $required_data; 
				}
				self::$face_list_lite[$cache_key] = $do_faces;
			}catch(Exception $ex){
				throw $ex;
			}
		}
		return self::$face_list_lite[$cache_key];
	}
}

