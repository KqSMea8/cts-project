<?php

/**
 * Class Sso_Sdk_User
 */
class Sso_Sdk_User {

	/** @var \Sso_Sdk_Session_Session  */
	private $session;

	public function init(Sso_Sdk_Session_Session $session) {
		$this->session = $session;
	}

	/**
	 * 返回用户uid
	 * @return string
	 */
	public function get_uid() {
		if (!isset($this->session)) return null;
		return $this->session->get_uid();
	}

	/**
	 * 返回会话对象
	 * @return Sso_Sdk_Session_Session
	 */
	public function get_session() {
		return $this->session;
	}

	/**
	 * 检查是否未登录
	 * @return bool
	 */
	public function is_status_null() {
		return !isset($this->session);
	}

	/**
	 * 检查是否访客
	 * @return bool
	 */
	public function is_status_visitor() {
		if (!isset($this->session)) return false;
		return $this->session->get_status() == Sso_Sdk_Session_Session::STATUS_VISITOR;
	}

	/**
	 * 检查是否预登录
	 * @return bool
	 */
	public function is_status_prelogin() {
		if (!isset($this->session)) return false;
		return $this->is_status_normal_expired() || $this->session->get_status() == Sso_Sdk_Session_Session::STATUS_WEAK;
	}

	/**
	 * 检查是否正常登录
	 * @return bool
	 */
	public function is_status_normal() {
		if (!isset($this->session)) return false;
		return $this->session->get_status() == Sso_Sdk_Session_Session::STATUS_NORMAL && !$this->session->get_sid()->is_expired();
	}

	/**
	 * 检查是否登录过期
	 * @return bool
	 */
	public function is_status_normal_expired() {
		if (!isset($this->session)) return false;
		return $this->session->get_status() == Sso_Sdk_Session_Session::STATUS_NORMAL && $this->session->get_sid()->is_expired();
	}

	/**
	 * 检查是否已退出
	 * @return bool
	 */
	public function is_status_exited() {
		if (!isset($this->session)) return false;
		return $this->session->get_status() == Sso_Sdk_Session_Session::STATUS_EXITED;
	}

	/**
	 * 根据uid获取用户信息
	 * @param $uid
	 * @throws Exception
	 * @return mixed
	 */
	public static function get_userinfo_by_uid($uid) {

		$arr_query = array(
			'user' => $uid,
			'ag' => 0,
			'entry' => Sso_Sdk_Config::get_user_config('entry'),
			'm' => md5($uid . 0 .Sso_Sdk_Config::get_user_config('pin')),
		);
		$arr_weiboinfo = Sso_Sdk_Config::get_user_config('need_weiboinfo');
		if(is_array($arr_weiboinfo) && count($arr_weiboinfo) > 0) {
			$arr_query['weibo_infos'] = implode(',', $arr_weiboinfo);
		}
		$url = Sso_Sdk_Config::instance()->get("data.res.http.getsso");
		$content = Sso_Sdk_Tools_Http::request($url, "post", $arr_query);
		if($content === false){
			throw new Exception("network exception");
		}

		@parse_str($content, $arr);
		if($arr['result'] != 'succ'){
			throw new Exception("call url $url fail; content: $content");
		}
		// 将微博信息也转码为gbk编码输出，统一输出编码，方便使用
		if (is_array($arr_weiboinfo) && count($arr_weiboinfo) > 0) {
			foreach ($arr_weiboinfo as $key) {
				if (isset($arr[$key])) {
					$arr[$key] = @iconv('utf-8', 'gbk//IGNORE', $arr[$key]);
                }
            }
		}
		return $arr;
	}
}