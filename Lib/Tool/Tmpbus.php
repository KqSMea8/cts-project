<?php
/**
 * Tool_Tmpbus 
 * 一些临时业务处理
 * @author    xiamengyu<mengyu5@staff.sina.com.cn> 
 * @created   2014-1-24
 * @copyright copyright(2013) weibo.com all rights reserved
 */
class Tool_Tmpbus
{

    public static function checkVertical($iid)
    {
        $articleUrl =  Comm_Config::get('misc.article_page');
        $outerId = array_keys($articleUrl);
        if (in_array($iid, $outerId))
        {
            header('Location: ' . $articleUrl[$iid]);
            exit;
        }
    }
}
