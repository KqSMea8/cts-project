<?php
/**
 * curl封装
 *
 *
 * <code>
 *  get方式
 *  $h = new Sso_Sdk_Tools_Http('http://login.sina.com.cn/', '10.54.38.18');
 *  $h->add_header('Referer', 'http://weibo.com/');
 *  $h->add_cookie('cookie', 'sso');
 *  $h->add_query_field('get2', 'p2');
 *  $h->add_query_field('get1', 'p1');
 *  $h->send();
 *
 *  var_dump($h->get_response_state());
 *  var_dump($h->get_response_time());
 *  var_dump($h->get_response_content());
 *  var_dump($h);
 * </code>
 *
 * <code>
 *  post方式
 *  $h = new Sso_Sdk_Tools_Http('http://login.sina.com.cn/', '10.54.38.18');
 *  $h->set_method(Sso_Sdk_Tools_Http::HTTP_METHOD_POST);
 *  $h->add_header('Referer', 'http://weibo.com/');
 *  $h->add_cookie('cookie', 'sso');
 *  $h->add_post_field('get2', 'p2');
 *  $h->add_post_field('get1', 'p1');
 *  $h->send();
 *
 *  var_dump($h->get_response_state());
 *  var_dump($h->get_response_time());
 *  var_dump($h->get_response_content());
 *  var_dump($h);
 * </code>
 */
class Sso_Sdk_Tools_Http {
	const CRLF      = "\r\n";
	const USERAGENT = 'Sso_Sdk_Client';

	/**
	 * 暂时只支持这些请求方式
	 */
	const HTTP_METHOD_GET	= 'GET',
		HTTP_METHOD_POST	= 'POST',
		HTTP_METHOD_PUT		= 'PUT',
		HTTP_METHOD_DELETE	= 'DELETE';

	private static $http_method = array(
		self::HTTP_METHOD_GET,
		self::HTTP_METHOD_POST,
		self::HTTP_METHOD_PUT,
		self::HTTP_METHOD_DELETE
	);

	private $headers		= array();
	private $auth		    = '';
	private $cookies		= array();
	private $query_fields	= array();
	private $post_fields	= array();
	private $has_upload		= false;
	private $post_files		= array();

	private $url;
	private $method			= 'GET';
	private $host_name;
	private $host_port		= 80;
	private $is_ssl			= false;
	private $no_body		= false;
	private $req_range		= array();
	private $query_string	= '';
	private $accept_encoding = '';

	/**
	 * @var array 代理服务器信息
	 */
	private $proxy_server   = array();
	private $follow_location= false;
	private $response_state;
	private $curl_info      = array();
	private $curl_id;
	private $error_msg;
	private $error_no;
	private $response_header;
	private $response_content = false;

	private $debug			= false;
	private $urlencode		= 'urlencode_rfc3986';

	/**
	 * @var int $connect_timeout	连接超时时间，单位毫秒
	 * @var int $timeout			请求超时时间，单位毫秒
	 */
	private $connect_timeout    = 1000;
	private $timeout		= 1000;

	/**
	 * @var callable
	 */
	private $callback       = null;

	/**
	 * @var resource
	 */
	private $ch             = null;
	private $curl_cli       = '';

	/**
	 * @var int
	 */
	public static $last_error_no = CURLE_OK;

	/**
	 * @var string
	 */
	public static $last_error_msg = "";

	/**
	 * @var array
	 */
	public static $last_request_log = array();

	/**
	 * 初始化一次请求
	 *
	 * @param string $url 		url绝对路径
	 * @param string $host_ip	指定host的ip
	 */
	public function __construct($url, $host_ip='') {
		$this->set_url($url, $host_ip);
	}

	/**
	 * @param $url
	 * @param string $http_method
	 * @param array $data
	 * @param array $options	// [timeout,conn_timeout]
	 * @return bool|mixed
	 * @throws Exception
	 */
	public static function request($url, $http_method='GET', array $data=array(), $options = array()) {
		// 重置错误
		self::$last_error_no = CURLE_OK;
		self::$last_error_msg = "";
		self::$last_request_log = array();
		$ipc = new Sso_Sdk_Tools_Ipc(__METHOD__);
		$ipc_key = $url.'?'.http_build_query($data);
		if (($result = $ipc->get($ipc_key)) !== null) return $result;

		$host = parse_url($url, PHP_URL_HOST);
		$proxy = Sso_Sdk_Config::get_user_config("proxy.".str_replace('.', '_', $host));

		$http = new Sso_Sdk_Tools_Http($url);
		$http->set_method($http_method);

		// set connection timeout
		if (isset($options['conn_timeout']) && $options['conn_timeout'] != null) {
			$http->set_connect_timeout(intval($options['conn_timeout']));
		}

		// set operation timeout
		if (isset($options['timeout']) && $options['timeout'] != null) {
			$timeout = intval($options['timeout']);
		} else {
			$timeout = (int)Sso_Sdk_Config::instance()->get("data.timeout.http.timeout");
		}
		$http->set_timeout($timeout);

		// set proxy
		if ($proxy) {
			$http->set_proxy_server($proxy);
		}
		if (!empty($data)) {
			if (strcasecmp($http_method, 'GET') === 0) {
				foreach ($data as $k=>$v) {
					$http->add_query_field($k, $v);
				}
			} else {
				foreach ($data as $k=>$v) {
					$http->add_post_field($k, $v);
				}
			}
		}
		$http->send();
		$content = $http->get_response_content();
		if ($content !== false) {
			$ipc->set($ipc_key, $content, 10); //这里的10s就不要在走配置了吧
		}
		// 记录最后一次错误
		self::$last_error_no = $http->get_error_no();
		self::$last_error_msg = $http->get_error_msg();
		self::$last_request_log = $http->get_log_info();
		return $content;
	}
	/**
	 * 解析url，如果有host，设置host头
	 *
	 *
	 * @param string $url
	 * @param string $host_ip
	 * @throws Exception
	 */
	private function set_url($url, $host_ip) {
		$parse = parse_url($url);

		if ($parse['scheme'] === 'https') {
			$this->is_ssl = true;
			$this->host_port = '443';
		} elseif ($parse['scheme'] !== 'http') {
			throw new Exception('only support http now');
		}

		if ($host_ip) {
			$this->add_header('Host', $parse['host']);
			$this->host_name = $host_ip;
		} else {
			$this->host_name = $parse['host'];
		}

		$this->url = $parse['scheme'] . '://' . $this->host_name;
		if (isset($parse['port'])) {
			$this->host_port = $parse['port'];
			$this->url .= ':' . $parse['port'];
		}
		if (isset($parse['path'])) {
			$this->url .= $parse['path'];
		}

		if (!empty($parse['query'])) {
			parse_str($parse['query'], $query_fields);
			$keys = array_map(array($this, 'run_urlencode'), array_keys($query_fields));
			$values = array_map(array($this, 'run_urlencode'), array_values($query_fields));
			$this->query_fields = array_merge($this->query_fields, array_combine($keys, $values));
		}
	}

	/**
	 * @param $method
	 * @throws Exception
	 */
	public function set_method($method) {
		$this->method = strtoupper($method);
		if (!in_array($this->method, self::$http_method)) {
			throw new Exception('Not support http method '.$method);
		}
	}

	/**
	 * 设置代理服务器
	 *
	 * @param array $proxy_server
	 *  array(
	 *      host => '',
	 *      port => '',
	 *      auth => 'user:password',    //basic方式
	 *      type => 'http或socket5'     //默认http
	 *  )
	 */
	public function set_proxy_server(array $proxy_server) {
		$this->proxy_server = $proxy_server;
		if (!isset($this->proxy_server['type'])) {
			$this->proxy_server['type'] = 'http';
		}
	}

	/**
	 * @param int $timeout 毫秒
	 */
	public function set_connect_timeout($timeout) {
		$this->connect_timeout = (int)$timeout;
	}

	/**
	 * @param int $timeout 毫秒
	 */
	public function set_timeout($timeout) {
		$this->timeout = (int)$timeout;
	}

	public function set_request_range($start, $end) {
		$this->req_range = array($start, $end);
	}

	public function set_urlencode($urlencode) {
		$this->urlencode = $urlencode;
	}

	public function set_callback($callback) {
		$this->callback = $callback;
	}

	/**
	 * 是否追踪30x
	 * @param int $max
	 */
	public function set_follow_location($max=1) {
		$this->follow_location = true;
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->ch, CURLOPT_MAXREDIRS, $max);
		$this->curl_cli .= ' --max-redirs '.$max;
	}


	public function add_header($name, $value, $urlencode = false) {
		$name = $this->run_urlencode($name, $urlencode);
		$value = $this->run_urlencode($value, $urlencode);
		$this->headers[] = $name.':'.$value;
	}

	public function add_auth($user, $password) {
		$this->auth = $user.':'.$password;
	}

	public function add_cookie($name, $value, $urlencode = false) {
		$name = $this->run_urlencode($name, $urlencode);
		$value = $this->run_urlencode($value, $urlencode);
		$this->cookies[$name] = $value;
	}

	public function add_query_field($name, $value, $urlencode = false) {
		$name = $this->run_urlencode($name, $urlencode);
		$value = $this->run_urlencode($value, $urlencode);
		$this->query_fields[$name] = $value;
	}

	public function add_post_field($name, $value, $urlencode = false) {
		$name = $this->run_urlencode($name, $urlencode);
		$value = $this->run_urlencode($value, $urlencode);
		$this->post_fields[$name] = $value;
	}

	public function add_post_file($name, $path) {
		$this->has_upload = true;
		$name = $this->run_urlencode($name);
		$this->post_files[$name] = '@' . $path;
	}

	public function set_accept_encoding($encoding) {
		$this->accept_encoding = $encoding;
	}
	/**
	 * 若用户指定了一个函数，则用那个函数进行 urlencode 。
	 * 否则，若类成员 urlencode 存在，则调用 本对象的 urlencode 。
	 * 否则，不进行 urlencode 原样返回。
	 *
	 * @param string         $input
	 * @param Callable|bool  $urlencode
	 *
	 * @return String
	 */
	public function run_urlencode($input, $urlencode = false) {
		if (is_callable($urlencode)) {
			return call_user_func($urlencode, $input);
		} elseif ($this->urlencode) {
			return $this->{$this->urlencode}($input);
		} else {
			return $input;
		}
	}




	public function curl_init() {
		if ($this->ch !== null) {
			throw new Exception('curl init already');
		}

		$this->ch       = curl_init();
		$this->curl_id  = $this->get_curl_id($this->ch);
		$this->curl_cli = 'curl -v ';

		$this->curl_setopt();
	}

	public function get_ch() {
		return $this->ch;
	}

	public function send() {
		$retry = '';
		$time_start = microtime(1);
		$this->curl_init();
		$content = curl_exec($this->ch);

		if(curl_errno($this->ch) != 0) {
			$info = curl_getinfo($this->ch);
			if ($info['pretransfer_time'] == 0) {
				// 还没有发送数据就失败了，则自动重试一次,但是记录失败那次的dns解析时间和连接时间
				$retry = round($info['namelookup_time']*1000, 1). ",".round($info['connect_time']*1000, 1);
				$content = curl_exec($this->ch);
			}
		}

		if (curl_errno($this->ch) === 0) {
			$rtn = true;
			$this->set_response_state(true, '', curl_errno($this->ch));
		} else {
			$this->set_response_state(false, curl_error($this->ch), curl_errno($this->ch));
			$rtn = false;
		}

		$this->set_response($content);  //这里解析响应信息, $this->get_response* 系列函数在此调用之后方有效
		$info = $this->get_response_info();

		if (Sso_Sdk_Tools_Debugger::is_enable()) { //该判断只为线上运行时不会执行到self::http_build_query 语句
			Sso_Sdk_Tools_Debugger::info(array(
				'post'  => $this->post_fields,
				'get'  => $this->query_fields,
				'post_query'    => self::http_build_query($this->post_fields),    // 方便调试
				'get_query'     => self::http_build_query($this->query_fields),
				'result'        => $this->get_response_content(),
				''
				), $this->url
			);
			Sso_Sdk_Tools_Debugger::info($info, $this->url. ' info');
		}

		$time_end = microtime(1);
		$time_threshold = Sso_Sdk_Config::instance()->get('data.log.slowlog.http.timeout');
		if ($time_threshold && ($time_end - $time_start) * 1000 > $time_threshold) {
			// 记录超时请求日志
			Sso_Sdk_Tools_Log::instance()->notice('timeout', $this->get_log_info($retry));
		}
		if (!$rtn) {
			Sso_Sdk_Tools_Log::instance()->warn('http', $this->get_log_info($retry));
		}

		$this->reset_ch();
		return $rtn;
	}

	public function reset_ch() {
		$this->ch = null;
		$this->curl_id = null;
	}

	public function get_curl_cli() {
		return $this->curl_cli;
	}

	public function get_url() {
		return $this->url;
	}

	public function get_curl_id($ch=null) {
		if ($ch) {
			$this->curl_id = self::generate_id($ch);
		}
		return $this->curl_id;
	}

	public static function generate_id($ch) {
		return (string)$ch;
	}

	public function set_response_state($state, $error_msg, $error_no) {
		$this->response_state = $state;
		$this->error_msg = $error_msg;
		$this->error_no = $error_no;
	}

	/**
	 * 分析返回信息，设置相应头和相应内容
	 *
	 * @param $content
	 * @param bool $invoke_callback
	 * @return mixed
	 */
	public function set_response($content, $invoke_callback = true) {
		$this->curl_info = curl_getinfo($this->ch);
		if (empty($content)) {
			return;
		}

		$section_separator          = self::CRLF.self::CRLF;
		$section_separator_length   = 4;
		// pick out http 100 status header
		$http_100 = 'HTTP/1.1 100 Continue' . $section_separator;
		if (false !== strpos($content, $http_100)) {
			$content = substr($content, strlen($http_100));
		}

		$last_header_pos = $pos = 0;
		if ($this->follow_location) {
			// 如果设置follow location，则去掉相应头中跳转的信息
			// put header and content into each var, 3xx response will generate many header :(
			$redirect_count = $this->get_response_info('redirect_count');
			for ($i = 0; $i <= $redirect_count; $i++) {
				if ($i + 1 > $redirect_count && $pos) {
					$last_header_pos = $pos + $section_separator_length;
				}
				$pos += $i > 0 ? $section_separator_length : 0;
				$pos = strpos($content, $section_separator, $pos);
			}
		} else {
			$pos = strpos($content, $section_separator);
		}

		$this->response_content = substr($content, $pos + $section_separator_length);
		$this->_parse_response_header(substr($content, $last_header_pos, $pos - $last_header_pos));
		// is there callback?
		if ($invoke_callback && $this->callback) {
			call_user_func_array($this->callback, array($this));
		}
	}

	/**
	 * 解析响应头，由于比较耗时，所以只有需要取头信息时才解析
	 * @param string $headers
	 */
	private function _parse_response_header($headers='') {
		static $_headers;
		if (!$_headers && $headers) {
			$_headers = $headers;
			return;
		}

		$headers = explode(self::CRLF, $_headers);
		foreach ($headers as $header) {
			if (false !== strpos($header, 'HTTP/1.1')) {
				continue;
			}

			$tmp = explode(':', $header, 2);
			$response_header_key = strtolower(trim($tmp[0]));
			if (!isset($this->response_header[$response_header_key])){
				$this->response_header[$response_header_key] = trim($tmp[1]);
			} else {
				if (!is_array($this->response_header[$response_header_key])) {
					$this->response_header[$response_header_key] = (array)$this->response_header[$response_header_key];
				}
				$this->response_header[$response_header_key][] = trim($tmp[1]);
			}
		}
	}

	public function get_response_state() {
		return $this->response_state;
	}

	public function get_error_msg() {
		return $this->error_msg;
	}

	public function get_error_no() {
		return $this->error_no;
	}

	/**
	 * @return int 请求消耗时间，单位毫秒（ms）
	 */
	public function get_response_time() {
		return round($this->get_response_info('total_time') * 1000);
	}

	/**
	 * 获取相应信息
	 * 详细内容见 http://php.net/manual/zh/function.curl-getinfo.php
	 * @param string $key
	 * @return mixed
	 */
	public function get_response_info($key = '') {
		if (empty($key)) {
			return $this->curl_info;
		} else {
			if (isset($this->curl_info[$key])) {
				return $this->curl_info[$key];
			}
		}
		return null;
	}

	/**
	 * 获取响应头信息
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get_response_header($key = '') {
		if (!$this->response_header) {
			$this->_parse_response_header();
		}
		if (empty($key)) {
			return $this->response_header;
		} else {
			if (isset($this->response_header[$key])) {
				return $this->response_header[$key];
			}
		}
		return null;
	}

	public function get_http_code() {
		return $this->get_response_info('http_code');
	}

	public function get_response_content() {
		return $this->response_content;
	}

	public function get_method(){
		return $this->method;
	}

	public static function urlencode($input) {
		if (is_array($input)) {
			return array_map(array(__CLASS__, 'urlencode'), $input);
		} else if (is_scalar($input)) {
			return urlencode($input);
		} else {
			return '';
		}
	}

	public static function urlencode_raw($input) {
		if (is_array($input)) {
			return array_map(array(__CLASS__, 'urlencode_raw'), $input);
		} else if (is_scalar($input)) {
			return rawurlencode($input);
		} else {
			return '';
		}
	}

	public static function urlencode_rfc3986($input) {
		if (is_array($input)) {
			return array_map(array(__CLASS__, 'urlencode_rfc3986'), $input);
		} else if (is_scalar($input)) {
			return strtr(rawurlencode($input), array('+'=>' ', '%7E'=>'~'));
		} else {
			return '';
		}
	}

	/**
	 * 拼装http查询串（不经过urlencode）
	 * @param array $query_data
	 * @return string
	 */
	public static function http_build_query($query_data = array()) {
		if(empty($query_data)){
			return '';
		}
		$pairs = array();
		foreach ($query_data as $key => $value){
			$pairs[] = "{$key}={$value}";
		}
		$query_string = implode('&', $pairs);
		return $query_string;
	}

	/**
	 * 封装请求需要的curl设置
	 *
	 */
	private function curl_setopt() {
		curl_setopt($this->ch, CURLOPT_URL, $this->url);
		// -v
		curl_setopt($this->ch, CURLOPT_HEADER, true);

		if ($this->is_ssl) {
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
			$this->curl_cli .= ' -k';
		}

		if ($this->no_body) {
			curl_setopt($this->ch, CURLOPT_NOBODY, true);
		}

		if (!empty($this->req_range)) {
			curl_setopt($this->ch, CURLOPT_RANGE, $this->req_range[0].'-'.$this->req_range[1]);
		}

		if (defined('CURLOPT_ENCODING') && !is_null($this->accept_encoding)) {    // cURL 7.10 开始添加该特性，所以先判断一下
			@curl_setopt($this->ch, CURLOPT_ENCODING, $this->accept_encoding);
		}
		// -v
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		// default
		curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		// not use
		curl_setopt($this->ch, CURLOPT_USERAGENT, self::USERAGENT.'/'.Sso_Sdk_Client::get_version());

		if ($this->debug) {
			// -v
			curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
		}

		$this->load_timeout_settings();
		$this->load_proxy_server();
		$this->load_cookies();
		$this->load_auth();
		$this->load_headers();
		$this->load_query_fields();
		$this->load_post_fields();

		if ($this->method) {
			curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $this->method);
			$this->curl_cli .= " -X \"{$this->method}\"";
		}
		$this->curl_cli .= ' "' . $this->url . ($this->query_string ? '?' . $this->query_string : '') . '"';
	}

	private function load_timeout_settings() {
		$version = curl_version();
		if (version_compare($version['version'], '7.16.2') < 0) {
			//如果timeout为0，则curl将wait indefinitely.故此处将意外设置timeout < 1sec的情况，重新设置为1s
			$timeout = floor($this->connect_timeout / 1000);
			if($this->connect_timeout > 0 && $timeout <= 0){
				$timeout = 1;
			}
			curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
		} else {
			curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connect_timeout);
			curl_setopt($this->ch, CURLOPT_TIMEOUT_MS, $this->timeout);
			if ($this->timeout < 1000) {
				// http://www.laruence.com/2014/01/21/2939.html
				curl_setopt($this->ch, CURLOPT_NOSIGNAL, 1);
			}
		}
		unset($version);
		$this->curl_cli .= ' --connect-timeout ' . round($this->connect_timeout / 1000, 3);
		$this->curl_cli .= ' -m ' . round($this->timeout / 1000, 3);
	}

	private function load_proxy_server() {
		if (empty($this->proxy_server)) {
			return;
		}

		switch ($this->proxy_server['type']) {
			case 'socks5':
				curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
				break;
			default:
				curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
		}
		curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);

		curl_setopt($this->ch, CURLOPT_PROXY,       $this->proxy_server['host']);
		curl_setopt($this->ch, CURLOPT_PROXYPORT,   $this->proxy_server['port']);
		$this->curl_cli .= ' -x ' . $this->proxy_server['host'] . ':' . $this->proxy_server['port'];

		if (!empty($this->proxy_server['auth'])) {
			curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $this->proxy_server['auth']);
			curl_setopt($this->ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
			$this->curl_cli .= " --proxy-basic -U \"{$this->proxy_server['auth']}\"";
		}
	}

	private function load_cookies() {
		if (empty($this->cookies)) {
			return;
		}
		$pairs = array();
		foreach ($this->cookies as $name => $value) {
			$pairs[] = $name . '=' . $value;
		}

		$cookie = implode('; ', $pairs);
		curl_setopt($this->ch, CURLOPT_COOKIE, $cookie);
		$this->curl_cli .= ' -b "' . $cookie . '"';
	}

	private function load_auth() {
		if (!empty($this->auth)) {
			$this->curl_cli .= "-u \"{$this->auth}\" ";
			curl_setopt($this->ch, CURLOPT_USERPWD, $this->auth);
		}
	}

	private function load_headers() {
		if (empty($this->headers)) {
			return;
		}

		foreach ($this->headers as $v) {
			$this->curl_cli .= " -H \"{$v}\"";
		}

		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
	}

	private function load_query_fields() {
		$this->query_string = '';
		if (empty($this->query_fields)) {
			return;
		}
		$pairs = array();
		foreach ($this->query_fields as $name => $value) {
			$pairs[] = $name . '=' . $value;
		}

		if($pairs){
			$this->query_string = implode('&', $pairs);
		}
		curl_setopt($this->ch, CURLOPT_URL, $this->url . '?' . $this->query_string);
	}

	private function load_post_fields() {
		if (empty($this->post_fields)) {
			return;
		}
		if (true == $this->has_upload) {
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->post_fields);
		} else {
			// 处理直接put数据的需求，如rest接口的tt操作
			if (count($this->post_fields) === 1 && key($this->post_fields) === 0) {
				curl_setopt($this->ch, CURLOPT_POSTFIELDS, urldecode($this->post_fields[0]));
			} else {
				curl_setopt($this->ch, CURLOPT_POSTFIELDS, self::http_build_query($this->post_fields));
			}
		}

		foreach ($this->post_fields as $name => $value) {
			if ($this->has_upload) {
				$this->curl_cli .= ' --form "' . $name . '=' . $value . '"';
			} else {
				$pairs[] = $name . '=' . $value;
			}
		}

		if (!empty($pairs)) {
			$this->curl_cli .= ' -d "' . implode('&', $pairs) . '"';
		}
	}

	/**
	 * build 详细的请求信息
	 * @param string $retry_info
	 * @return array
	 */
	private function get_log_info($retry_info = '') {
		$info = $this->get_response_info();
		$rtn = false;
		if ($this->ch) {
			$rtn = ($this->get_error_no() === CURLE_OK);
		}
		$arr = array(
			'class'     => __CLASS__,
			'method'    => __METHOD__,
			'resource'  => $this->url,
			'params'    => array(
				'header'    => !empty($this->headers)?self::http_build_query($this->headers):array(),
				'cookie'    => !empty($this->cookies)?self::http_build_query($this->cookies):array(),
				'get'       => !empty($this->query_fields)?self::http_build_query($this->query_fields):array(),
				'post'      => !empty($this->post_fields)?self::http_build_query($this->post_fields):array(),
			),
			'extension' => array (
				'response_state' => $this->get_response_state() ? '' : $this->get_error_no().':'.$this->get_error_msg(),
				'namelookup_time' => round($info['namelookup_time']*1000, 1),
				'connect_time' => round($info['connect_time']*1000, 1),
				'pretransfer_time' => round($info['pretransfer_time']*1000, 1),
				'starttransfer_time' => round($info['starttransfer_time']*1000, 1),
				'total_time' => round($info['total_time']*1000, 1),
				'http_code' => $info['http_code'],
				'size_download' => $info['size_download'],
				'retry'	=> $retry_info,
				'result' => $rtn?'succ':'fail',
			),
		);
		if (isset($info['local_ip'])) { // curl version > 7.21.0
			$arr['extension']['local'] = $info['local_ip'].':'.$info['local_port'];
			$arr['extension']['remote'] = $info['primary_ip'].':'.$info['primary_port'];
		}
		return $arr;
	}
}
