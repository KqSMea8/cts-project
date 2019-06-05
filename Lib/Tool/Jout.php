<?php

class Tool_Jout {

	public static function normal($code, $msg = '', $data = null) {
		if (!headers_sent()) {
			header('Content-type: application/json; Charset: utf-8', TRUE);
		}

		$optional_k = htmlspecialchars(Comm_Context::param('_k', ''), ENT_QUOTES);
		if ($optional_k) {
			Comm_Response::set_meta_data('key', $optional_k);
		}

		$data = $data or new stdClass();
		$type = Comm_Context::param('_t', 0);
		if ($type === '1') {
			header('Cache-control: maxage=1');
			Comm_Response::use_jsonp_as_callback();
			Comm_Response::out_jsonp(Comm_Context::param('_v', 'callback'), $code, $msg, $data);
		} else if ($type === '2') {
			Comm_Response::use_jsonp_as_var();
			Comm_Response::out_jsonp_iframe(Comm_Context::param('_v', 'v'), $code, $msg, $data, FALSE);
		} else {
			Comm_Response::out_json($code, $msg, $data, FALSE);
		}
	}

	public static function notice($code, $msg ='', $data = null) {
		$callback = isset($_REQUEST['callback']) ? $_REQUEST['callback'] : '';
		$_GET['_t'] = '1';
		$_GET['_v'] = $callback;
		self::normal($code, $msg, $data);
	}

}