<?php
/**
 * 一些零碎的模板操作
*@author  hongxue <hongxue@staff.sina.com.cn>
*@copyright	1.0
*@link		定义在线连接
*@package	Tool
*@subpackage	Tool
*
 */
class Tool_Parsetpl{
    public static function parse($data, $tpl){
    	    $smarty = new WBSmarty();
    	    $viewer = Comm_Context::get('viewer', FALSE);
    	    $data = array_merge($data, array(
                'g_domain' =>  Comm_Util::conf('domain.weibo'),
                'g_js_domain' =>  Comm_Util::conf('env.js_domain'),
                'g_css_domain' => Comm_Util::conf('env.css_domain'),
                'g_img_domain' => Comm_Util::conf('env.css_domain'),
                'g_skin_domain' => Comm_Util::conf('env.skin_domain'),
    	        'g_viewer' => $viewer,
    	    ));
    	       	    
    	    $smarty->assign($data);
    	    return $smarty->fetch($tpl);
    }
}