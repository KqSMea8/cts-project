<?php
/**
 * statuses相关接口
 *
 * @package    api
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_Statuses{
    const RESOURCE = 'statuses';
    
    /**
     * 获取最新的公共微博消息
     */
    public static function public_timeline(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'public_timeline');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_pagination();
        $request->support_base_app();
        
        return $request;
    }
    /**
     * 获取当前登录用户及其所关注用户的最新微博消息 
     */
    public static function friends_timeline(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'friends_timeline');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_pagination();
        $request->support_base_app();
        $request->support_trim_user();
        $request->support_cursor();
        $request->add_rule('feature', 'int', false);
        
        return $request;
    }
    
    /**
     * 获取双向关注用户的最新微博消息
     */
    public static function bilateral_timeline() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'bilateral_timeline');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_pagination();
        $request->support_base_app();
        $request->support_cursor();
        $request->add_rule('feature', 'int', false);
        
        return $request;
    }
    
    /**
     * 获取当前登录用户及其所关注用户的最新微博消息
     */
    public static function home_timeline() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'home_timeline');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_pagination();
        $request->support_base_app();
        $request->support_trim_user();
        $request->support_cursor();
        $request->add_rule('feature', 'int', false);
        
        return $request;
    }
    /**
     * Enter 返回用户最新发表的微博消息列表
     */
    public static function user_timeline() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'user_timeline');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->uid_or_screen_name();
        $request->support_pagination();
        $request->support_base_app();
        $request->support_cursor();
        $request->add_rule('feature', 'int', false);
        
        return $request;
    }
    /**
     * 批量获取指定的一批用户timeline
     */
    public static function timeline_batch() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'timeline_batch');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->uid_or_screen_name('uids', 'screen_name', true);
        $request->support_pagination();
        $request->support_base_app();
        $request->add_rule('feature', 'int', false);
        
        return $request;
    }
    /**
     * 返回一条原创微博的最新n条转发微博信息
     */
    public static function repost_timeline() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'repost_timeline');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('id', 'int64', true);
        $request->add_rule('filter_by_author', 'int');
        $request->support_pagination();
        $request->support_cursor();
        
        return $request;
    }
    /**
     * 用户的最新转发微博。获取当前用户最新转发的n条微博消息
     */
    public static function repost_by_me() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'repost_by_me');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        $request->support_pagination();
        $request->support_cursor();
        
        return $request;
    }
    
    /**
     * 获取@当前用户的最新微博
     */
    public static function mentions() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'mentions');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_pagination();
        $request->support_trim_user();
        $request->support_cursor();
        $request->add_rule('filter_by_author', 'int', false);
        $request->add_rule('filter_by_type', 'int', false);
        $request->add_rule('filter_by_source', 'int', false);
        
        return $request;
    }
    /**
     * 根据ID获取单条微博信息信息
     */
    public static function show() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'show');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('id', 'int64', true);
        
        return $request;
    }
    /**
     * 根据提供的ID批量获取一组微博的信息
     */
    public static function show_batch() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'show_batch');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('ids', 'string', true);
        $request->add_set_callback('ids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ',', 50));
        $request->add_rule('trim_user', 'int', false);
        
        return $request;
    }
    /**
     * 返回新浪微博官方所有表情、魔法表情的相关信息。包括短语、表情类型、表情分类，是否热门等。
     */
    public static function emotions() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url('emotions', '');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('type', 'string');
        $request->add_rule('language', 'string');
        
        return $request;
    }
    /**
     * 通过id获取mid。
     */
    public static function querymid() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'querymid');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('id', 'string', true);
        $request->add_rule('type', 'int', true);
        $request->add_rule('is_batch', 'int');
        
        return $request;
    }
    /**
     * 通过mid获取id。通过mid获取id。其中id为该条微博/评论/私信在API系统中的id；mid为该条微博/评论/私信在web系统中的id值。
     */
    public static function queryid() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'queryid');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('mid', 'string', true);
        $request->add_rule('type', 'int', true);
        $request->add_rule('is_batch', 'int');
        $request->add_rule('inbox', 'string');
        $request->add_rule('isBase62', 'string');
        
        return $request;
    }
    /**
     * 返回热门转发榜 
     * @param string $type weekly(按天)、 daily(按周)
     */
    public static function hot_repost($type = 'weekly') {
        $type = in_array($type, array('weekly', 'daily')) ? $type : 'weekly';
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'hot/repost_'.$type);
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_base_app();
        $request->add_rule('count', 'int');
        
        return $request;
    }
    /**
     * 返回热门评论榜
     * @param string $type weekly(按天)、 daily(按周)
     */
    public static function hot_comments($type = 'weekly') {
        $type = in_array($type, array('weekly', 'daily')) ? $type : 'weekly';
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'hot/comments_'.$type);
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_base_app();
        $request->add_rule('count', 'int');
        
        return $request;
    }
    /**
     * 转发一条微博信息 
     */
    public static function repost() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'repost');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('status', 'string');
        $request->add_rule('id', 'int64', true);
        $request->add_rule('is_comment', 'int');
        $request->add_rule('skip_check', 'int');
        
        return $request;
    }
    /**
     * 根据ID删除微博消息。注意：只能删除自己发布的微博消息。
     */
    public static function destroy() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'destroy');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('id', 'int64', true);
        
        return $request;
    }
    /**
     * 发布一条微博信息。 
     */
    public static function update() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'update');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('status', 'string', true);
        $request->add_rule('lat', 'float');
        $request->add_rule('long', 'float');
        $request->add_rule('annotations', 'string');
        $request->add_rule('skip_check', 'int');
        
        return $request;
    }
    
    /**
     * 发布一条微博信息，商业开放平台
     */
    public static function update_biz() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'update');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST', 9);
        unset($url);
    
        $request->add_rule('status', 'string', true);
        $request->add_rule('lat', 'float');
        $request->add_rule('long', 'float');
        $request->add_rule('annotations', 'string');
        $request->add_rule('skip_check', 'int');
    
        return $request;
    }

    /**
     * 发布一条微博信息，新key 
     */
    public static function update_new() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'update');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST', 2);
        unset($url);
        
        $request->add_rule('status', 'string', true);
        $request->add_rule('lat', 'float');
        $request->add_rule('long', 'float');
        $request->add_rule('annotations', 'string');
        $request->add_rule('skip_check', 'int');
        
        return $request;
    }
    
    /**
     * 发布一条微博信息，新key
     * 国庆活动
     */
    public static function update_app() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'update');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST', 3);
        unset($url);
    
        $request->add_rule('status', 'string', true);
        $request->add_rule('lat', 'float');
        $request->add_rule('long', 'float');
        $request->add_rule('annotations', 'string');
        $request->add_rule('skip_check', 'int');
    
        return $request;
    }

    /**
     * 发布一条微博信息，新key
     * 防寒保暖活动
     */
    public static function update_winter() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'update');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST', 4);
        //echo json_encode($request);
        unset($url);
    
        $request->add_rule('status', 'string', true);
        $request->add_rule('lat', 'float');
        $request->add_rule('long', 'float');
        $request->add_rule('annotations', 'string');
        $request->add_rule('skip_check', 'int');
    
        return $request;
    }

    /**
     * 发布一条微博，同时指定已经上传的图片picid或internet上的图片url.
     */
    public static function upload_url_text($key = '') {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'upload_url_text');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST', $key);
        $request->set_request_timeout(10000, 10000);
        unset($url);
        
        $request->add_rule('status', 'string', true);
        $request->add_rule('pic_id', 'string');
        $request->add_rule('url', 'string');
        $request->add_rule('skip_check', 'int');
        
        return $request;
    }
    /**
     * 上传图片并发布一条微博信息
     */
    public static function upload() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'upload');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('status', 'string', true);
        $request->add_rule('pic', 'filepath', true);
        $request->add_rule('lat', 'float');
        $request->add_rule('long', 'float');
        
        return $request;
    }
    /**
     * 上传图片
     */
    public static function upload_pic() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'upload_pic');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('pic', 'filepath', true);
        
        return $request;
    }
    
    /**
     * 解析地理信息
     */
    public static function get_addr() {
        $url = 'http://api.t.sina.com.cn/location/geocode/geo_to_address.json';
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url); 
        $request->add_rule('coordinate', 'string', true);        
        return $request;
    }
    
    /**
     * 屏蔽某个@提到我的微博，以及后续对其转发而引起的@提到我 
     */
    public static function mentions_shield() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'mentions/shield');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url); 
        
        $request->add_rule('id', 'int64', true);
        $request->add_rule('follow_up', 'int', false);
        
        return $request;
    }

    /**
     * 红包发分享微博。
     */
    public static function update_bonus() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'update');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST', 5);
        unset($url);

        $request->add_rule('status', 'string', true);
        $request->add_rule('lat', 'float');
        $request->add_rule('long', 'float');
        $request->add_rule('annotations', 'string');
        $request->add_rule('skip_check', 'int');

        return $request;
    }

}
