<?php
require_once 'stdafx.php';
header("Content-type: text/html; charset=utf-8");

//用命令行启动时, 初始化GPC参数: --get,  --post,  --uri
if (isset($_SERVER['SHELL']) || (PHP_SAPI === 'cli')) {

    for ($i = 1; $i < $_SERVER['argc']; $i++) {
        if (!isset($_SERVER['argv'][$i])) {
            break;
        }

        $opt = $_SERVER['argv'][$i];

        if (substr($opt, 0, 2) !== '--') {
            continue;
        }

        $value = null;
        if (strpos($opt, '=')) {
            list($opt, $value) = explode('=', $opt, 2);
        }

        if ($opt == '--uri') {
            $_SERVER['PATH_INFO'] = $value;
        } else if ($opt == '--get') {
            parse_str($value, $_GET);
        } else if ($opt == '--post') {
            parse_str($value, $_POST);
        }
    }
}
//else{
//    header("Location: http://wawa.weibo.com/topic/show?id=1545708524243&portrait_only=1");
//    exit;
//}

//分发
try {
    $controllerName = 'Controller_' . implode('_', array_map('ucfirst', explode('/', str_replace('_', '/', trim($_SERVER['PATH_INFO'], '/')))));
    if ($controllerName == 'Controller_') {
        $controllerName = "Controller_Index_Index";
    } else {
        //不存在的路由默认去index
        $classname_t = str_replace("\\", '_', trim($controllerName, "\\"));
        $classFile = PATH_ROOT . DS . str_replace('_', DS, $classname_t) . ".php";
        if (!file_exists($classFile)) {
            if (stripos($classname_t, "Controller_Api") === false) {
                $controllerName = "Controller_Index_Index";
            } else {
                exit(json_encode(array('code' => 220002, 'msg' => '非法请求')));
            }
        }
    }

    $controllerObj = new $controllerName();
    $controllerObj->main();
} catch (Exception $e) {
    Lib_Log::warning('未知异常:' . $e->getMessage());
    exit(json_encode(array('code' => 100001, 'msg' => '未知异常')));
}
