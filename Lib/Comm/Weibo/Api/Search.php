<?php
/**
 * search相关接口
 *
 * @package    api
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_Search{
    const RESOURCE = 'search';
    const TIMEOUT = 3000;
    const CONNECT_TIMEOUT = 3000;
    
    /**
     * 搜索用户时的即时搜索建议
     */
    public static function suggestions_users() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'suggestions/users');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        self::support_q_count($request);
        
        return $request;
    }

    /**
     * 搜索微博时的即时搜索建议
     */
    public static function suggestions_statuses() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'suggestions/statuses');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        self::support_q_count($request);
        
        return $request;
    }
    
    /**
     * 搜索学校时的即时搜索建议
     */
    public static function suggestions_schools() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'suggestions/schools');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        self::support_q_count($request);
        $request->add_rule('type', 'int', false);
        
        return $request;
    }

    /**
     * 搜索公司时的即时搜索建议
     */
    public static function suggestions_companies() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'suggestions/companies');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        self::support_q_count($request);
        
        return $request;
    }
    
    /**
     * 搜索应用时的即时搜索建议
     */
    public static function suggestions_apps() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'suggestions/apps');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        self::support_q_count($request);
        
        return $request;
    }

    /**
     * 在@某人时，实时获取用户名建议
     */
    public static function suggestions_at_users() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'suggestions/at_users');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        $request->set_request_timeout(2000, 2000);
        unset($url);
        
        self::support_q_count($request, true);
        $request->add_rule('type', 'int', true);
        $request->add_rule('range', 'int', false);
        $request->add_rule('sid', 'string', false);
        return $request;
    }
    
    /**
     * 综合联想搜索，给出符合的用户、微群以及应用搜索建议 
     */
    public static function suggestions_integrate() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'suggestions/integrate');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('query', 'string', true);
        $request->add_rule('uid', 'int64', true);
        $request->add_rule('sort_user', 'int', false);
        $request->add_rule('sort_app', 'int', false);
        $request->add_rule('sort_grp', 'int', false);
        $request->add_rule('user_count', 'int', false);
        $request->add_rule('app_count', 'int', false);
        $request->add_rule('grp_count', 'int', false);
        
        return $request;
    }

    /**
     * user_timeline的高级搜索接口
     */
    public static function statuses_user_timeline_sp() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'statuses/user_timeline_sp');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('query', 'string', true);
        $request->add_rule('uid', 'int64', false);
        $request->support_pagination('start', 'num');
        
        return $request;
    }

    /**
     * user_timeline的高级搜索接口 (新方式，简化query串的内容分解为参数传递)
     */
    public static function statuses_user_timeline() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'statuses/user_timeline');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', false);
        self::add_timeline_rules($request);
        $request->add_rule('ids', 'string', false);
        $request->add_set_callback('ids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', '~', 2000));
        $request->add_rule_method('ids', 'POST');
        
        $request->set_request_timeout(self::CONNECT_TIMEOUT, self::TIMEOUT);
        
        return $request;
    }
    
    /**
     * friends_timeline的高级搜索接口 (旧接口方式)
     */
    public static function statuses_friends_timeline_sp() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'statuses/friends_timeline_sp');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('query', 'string', true);
        $request->add_rule('uid', 'int64', false);
        $request->add_rule('gid', 'int64', false);
        $request->support_pagination('start', 'num');
        
        return $request;
    }

    /**
     * friends_timeline的高级搜索接口 (新方式，简化query串的内容分解为参数传递)
     */
    public static function statuses_friends_timeline() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'statuses/friends_timeline');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        self::add_timeline_rules($request);
        $request->add_rule('uid', 'int64', false);
        $request->add_rule('gid', 'int64', false);
        $request->add_before_send_callback('Comm_Weibo_Api_Util', "check_alternative", array('uid', 'gid'));
        
        $request->set_request_timeout(self::CONNECT_TIMEOUT, self::TIMEOUT);
        
        return $request;
    }
    
    /**
     * 评论搜索 范围是人名与评论内容 (旧接口方式)
     */
    public static function statuses_comments() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'statuses/comments');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('query', 'string', true);
        $request->add_rule('uid', 'int64', false);
        $request->support_pagination('start', 'num');
        
        return $request;
    }
    
    /**
     * 评论搜索范围是人名与评论内容(新方式，简化query串的内容分解为参数传递)
     */
    public static function comments() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'comments');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        self::add_comments_rules($request);
        $request->set_request_timeout(self::CONNECT_TIMEOUT, self::TIMEOUT);
        
        return $request;
    }
    
    /**
     * 搜索评论人 即在评论箱（发出以及接收到的）中的联系人的搜索 
     */
    public static function users_comments_users_sp() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'users/comments_users_sp');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('query', 'string', true);
        $request->add_rule('uid', 'int64', false);
        $request->support_pagination('start', 'num');
        
        $request->set_request_timeout(self::CONNECT_TIMEOUT, self::TIMEOUT);
        
        return $request;
    }

    /**
     * 搜索用户
     */
    public static function users() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'users');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('q', 'string', false);
        $request->add_rule('snick', 'int', false);
        $request->add_rule('sdomain', 'int', false);
        $request->add_rule('sintro', 'int', false);
        $request->add_rule('stag', 'int', false);
        $request->add_rule('province', 'int', false);
        $request->add_rule('city', 'int', false);
        $request->add_rule('gender', 'string', false);
        $request->add_rule('comorsch', 'string', false);
        $request->add_rule('sort', 'int', false);
        $request->support_pagination();
        $request->support_base_app();
        $request->add_rule('callback', 'string', false);
        
        $request->set_request_timeout(self::CONNECT_TIMEOUT, self::TIMEOUT);
        
        return $request;
    }
    
    /**
     * 提到我的微博的高级搜索接口
     */
    public static function statuses_mentions () {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'statuses/mentions');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', false);
        self::add_timeline_rules($request);
        $request->add_rule('atme', 'int', TRUE);
        $request->set_request_timeout(self::CONNECT_TIMEOUT, self::TIMEOUT);
        
        return $request;
    }
    
    /**
     * 提到我的评论的高级搜索接口
     */
    public static function comments_mentions() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'comments/mentions');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        self::add_comments_rules($request);
        $request->set_request_timeout(self::CONNECT_TIMEOUT, self::TIMEOUT);
        
        return $request;
    }
    /**
     * 私信搜索
     */
    public static function direct_messages() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'direct_messages');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('key', 'string', TRUE);
        $request->add_rule('cuid', 'int64', TRUE);
        self::add_sid_rule($request);
        $request->support_pagination('start', 'num');
        $request->add_rule('isred', 'int', FALSE);
        $request->add_rule('startime', 'int64', FALSE);
        $request->add_rule('endtime', 'int64', FALSE);
        $request->add_rule('type', 'int', FALSE);
        $request->add_rule('contact', 'int', FALSE);
        
        $request->set_request_timeout(4000, 4000);
        
        return $request;
    }
    
    /**
     * 收藏搜索
     */
    public static function statuses_favorites(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "statuses/favorites");
        $request = new Comm_Weibo_Api_Request_Platform($url, "GET");
        $request->add_rule("key", "string", TRUE);
        $request->add_rule("cuid", "int64", TRUE);
        $request->add_rule("sid", "string", TRUE);
        $request->support_pagination("start","num");
        $request->add_rule("isred", "int");
        $request->add_rule("istag", "int");
        $request->add_rule("onlytotal", "int");
        $request->add_rule("onlyid", "int");
        $request->add_rule("contact", "int");
        $request->add_rule("uid", "int64");
        
        $request->set_request_timeout(self::CONNECT_TIMEOUT, self::TIMEOUT);
        
        return $request;
    } 
    
    /**
     * 微号搜索
     * Enter description here ...
     */
    public static function suggestions_weihao() {
    	$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "suggestions/weihao");
    	$request = new Comm_Weibo_Api_Request_Platform($url, "GET");
    	$request->add_rule("q",'string',TRUE);
    	$request->add_rule('count','int',FALSE);
    	return $request;
    }
    
    /**
     * 添加关键字和搜索结果数规则
     * @param Comm_Weibo_Api_Request_Platform $request
     * @param boolean $q
     * @param boolean $count
     */
    private static function support_q_count(Comm_Weibo_Api_Request_Platform $request, $q = false, $count = false) {
        $request->add_rule('q', 'string', $q);
        $request->add_rule('count', 'int', $count);
    }
    
    /**
     * 添加搜索标识sid
     * @param Comm_Weibo_Api_Request_Platform $request
     */
    private static function add_sid_rule(Comm_Weibo_Api_Request_Platform $request) {
        $request->add_rule('sid', 'string', TRUE);
    }
    
    /**
     * 添加搜索timeline通用参数规则
     * @param Comm_Weibo_Api_Request_Platform $request
     */
    private static function add_timeline_rules(Comm_Weibo_Api_Request_Platform $request) {
        $request->add_rule('key', 'string', FALSE);
        $request->add_rule('cuid', 'int64', TRUE);
        self::add_sid_rule($request);
        $request->support_pagination('start', 'num');
        $request->add_rule('isred', 'int', FALSE);
        $request->add_rule('xsort', 'int', FALSE);
        $request->add_rule('zone', 'string', FALSE);
        $request->add_rule('starttime', 'int64', FALSE);
        $request->add_rule('endtime', 'int64', FALSE);
        $request->add_rule('haspic', 'int', FALSE);
        $request->add_rule('haslink', 'int', FALSE);
        $request->add_rule('hasori', 'int', FALSE);
        $request->add_rule('hasret', 'int', FALSE);
        $request->add_rule('hasat', 'int', FALSE);
        $request->add_rule('hasvideo', 'int', FALSE);
        $request->add_rule('hasmusic', 'int', FALSE);
        $request->add_rule('hastext', 'int', FALSE);
        $request->add_rule('appid', 'int', FALSE);
        $request->add_rule('nofilter', 'int', FALSE);
        $request->add_rule('istag', 'int', FALSE);
        $request->add_rule('status', 'int', FALSE);
        $request->add_rule('onlytotal', 'int', FALSE);
        $request->add_rule('onlymid', 'int', FALSE);
    }
    
    /**
     * 添加搜索comments通用参数规则
     * @param Comm_Weibo_Api_Request_Platform $request
     */
    private static function add_comments_rules(Comm_Weibo_Api_Request_Platform $request) {
        $request->add_rule('key', 'string', TRUE);
        $request->add_rule('cuid', 'int64', TRUE);
        self::add_sid_rule($request);
        $request->add_rule('uid', 'int64', FALSE);
        $request->support_pagination('start', 'num');
        $request->add_rule('isred', 'int', FALSE);
        $request->add_rule('atme', 'int', FALSE);
        $request->add_rule('startime', 'int64', FALSE);
        $request->add_rule('endtime', 'int64', FALSE);
        $request->add_rule('type', 'int', FALSE);
        $request->add_rule('contact', 'int', FALSE);
    }
    
    
    public static function statuses_search(){
    	$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'statuses');
    	$request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
    	unset($url);
    	
    	//$request->add_rule('uid', 'int64', false);
    	$request->add_rule('q', 'string', true);
    	$request->add_rule('page', 'int', false);
    	$request->add_rule('sort','string',false);
    	self::add_timeline_rules($request);
    	//$request->add_rule('ids', 'string', false);
    	//$request->add_set_callback('ids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', '~', 2000));
    	//$request->add_rule_method('ids', 'POST');
    	
    	$request->set_request_timeout(self::CONNECT_TIMEOUT, self::TIMEOUT);
    	
    	return $request;
    }
}