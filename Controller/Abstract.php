<?php

abstract class Controller_Abstract{
    protected $request;
    protected $checkRule = array();
    protected $_params = array();
    protected $params = array();
    protected $needCheckSign = false;
    protected $uid;
    protected $weiboInfo;
    protected $autoRedirect = true;
    protected $needLogin = true;
    protected $needCheckRefer = false;
    protected $onlyPost = false;
    //禁用的Referer
    protected $deny_referer = array(
        '<',
        '>',
        'document\. ',
        '(.)?([a-zA-Z]+)?(Element)+(.*)?(\()+(.)*(\))+',
        '(<script)+[\s]?(.)*(>)+',
        'src[\s]?(=)+(.)*(>)+',
        '[\s]+on[a-zA-Z]+[\s]?(=)+(.)*',
        'new[\s]+XMLHttp[a-zA-Z]+',
        '\@import[\s]+(\")?(\')?(http\:\/\/)?(url)?(\()?(javascript:)?',
    );

    public function __construct(){

    }

    public function getRequest(){
        return $this->request;
    }

    public function __init() {
        $this->request = Lib_Request::createRequest();
        $this->_params = $this->getRequest()->all();
       // $this->uid = Lib_WeiboSSO::getUID($this->autoRedirect);

        if ($this->needCheckRefer) {
            $this->checkReferer();
        }

        /*if(!$this->uid && $this->needLogin) {
            $error = Lib_Config::i18n("common.need_login");
            throw new Exception($error['msg'], $error['code']);
        }*/

    }


    public function checkParams() {
        if($this->onlyPost) {
            if (!empty($this->checkRule)) {
                $validatorObj = new Lib_Validation_Validator();
                $validatorObj->check($this->request->form(), $this->checkRule);
                $defaultArr = $validatorObj->getDefaults();
                if (!empty($defaultArr)) {
                    foreach ($defaultArr as $key => $val) {
                        $this->request->setEnv('POST', $key, $val);
                    }
                }
            }
            $this->params = $this->getRequest()->form();
            //echo print_r($this->params);
        }else{
            if (!empty($this->checkRule)) {
                $validatorObj = new Lib_Validation_Validator();
                $validatorObj->check($this->request->all(), $this->checkRule);
                $defaultArr = $validatorObj->getDefaults();
                if (!empty($defaultArr)) {
                    foreach ($defaultArr as $key => $val) {
                        $this->request->setEnv('GET', $key, $val);
                    }
                }
            }
            $this->params = $this->getRequest()->all();
        }
        return true;
    }

    /**
     * MD5签名验证
     * @param array $params 带校验参数
     * @param array $source source值
     * @param array $sign_filter 需要过滤掉的参数，不纳入签名校验
     * @throws Exception
     */
    public static function checkSignMD5($params, $signFilter = null) {
        if (!empty($signFilter)) {
            foreach ($signFilter as $value) {
                unset($params[$value]);
            }
        }
        $key = Lib_Config::get("auth." . $params['source']);
        if (!empty($key)) {
            if(Lib_SignMD5::check($key, $params)){
                return true;
            }
        }
        $error = Lib_Config::i18n("common.sign_error");
        throw new Exception($error['msg'], $error['code']);
    }

    public function main() {
        try {
            //公共初始化
            $this->__init();
            //如果控制器存在init方式，就调用
            if (method_exists($this, "init")) {
                $this->init();
            }
            if($this->needCheckSign) {
                $this->checkSign();
            }
            //参数校验
            $this->checkParams();
            //运行程序方法
            $this->run();
        } catch (Exception $e) {
            Lib_Log::warning($e->getFile() . ":" . $e->getLine() . "#" . $e->getMessage());
            $this->__handleException($e);
        }
        return true;
    }

    public function __handleException($e) {
        if ($e->getCode() < 100000) {
            $this->outputJson(array('code' => '100011', 'msg' => sprintf('操作异常，请稍后重试(%s)', $e->getCode())));
            return;
        }

        $this->outputJson(array('code' => $e->getCode(), 'msg' => $e->getMessage()));
    }
    public function checkReferer(){
        // if (isset($_SERVER['HTTP_REFERER'])) {
            //检查Referer是否是本站的
            $urlInfo = parse_url($_SERVER['HTTP_REFERER']);
            $allowReferer = array(
                $_SERVER['HTTP_HOST'],
            );
            if(!in_array($urlInfo['host'], $allowReferer)) {
                $this->outputJson(array('code'=>'100011','msg'=>'非法请求1'));
                exit;
            }

            //检查Referer合法性
            foreach ($this->deny_referer as $reg) {
                $ref = urldecode($_SERVER['HTTP_REFERER']);
                if (preg_match('/' . $reg . '/', $ref)) {
                    $this->outputJson(array('code'=>'100011','msg'=>'非法请求2'));
                    exit;
                }
            }
        // }

    }
    public  function is_xmlhttprequest () {
        if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest'){
            $this->outputJson(array('code'=>'100011','msg'=>'非法请求3'));
            exit;
        }
    }
    //输出json
    public function outputJson($data) {
        if (!is_array($data)) {
            $data = array();
        }
        $jsonp = $this->request->all('callback');
        if (!is_null($jsonp)) {
            header('Content-type: text/javascript');
            $jsonData = $jsonp . '(' . json_encode($data) . ')';
        } else {
            header('Content-type: application/json');
            $jsonData = json_encode($data);
        }
        echo $jsonData;
        exit;
    }

    //输出html页面, 使用原生模板引擎
    public function outputHtml($data, $tpl_name) {

        //$data['js_version'] = JsVersion::getVersion();
        @extract($data);
        unset($data);
        include (PATH_VIEW . DS . $tpl_name. ".html");
        exit;
    }
    public function location($url, $js = '') {
        if ($js != '') {
            echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
            echo "<script> $js </script>";
            exit;
        }
        header("content-type:text/html; charset=utf-8");
        header("Location: {$url}");
        exit;
    }
}