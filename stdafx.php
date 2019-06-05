<?php

define('DS', DIRECTORY_SEPARATOR);
define('PATH_ROOT', str_replace("\\", '/', dirname(__FILE__)));
define('PATH_THIRD_LIB', PATH_ROOT . DS . "Lib" . DS . "Third"); //第三方库路径
define('PATH_VIEW', PATH_ROOT . DS . "View"); //模板路径
define('PATH_DAEMON', PATH_ROOT . DS . "Daemon"); //Daemon路径

define('ENV_CLI', isset($_SERVER['SHELL']) || (PHP_SAPI === 'cli') ? TRUE : FALSE);



define('DEBUG_SQL', false);

spl_autoload_register('autoload');
date_default_timezone_set('Asia/Shanghai');

//加载环境变量
/*$ini_file = PATH_ROOT . "/system/SINASRV_CONFIG";
if (!is_file($ini_file)) {
    echo "Can't find the SINASRV_CONFIG";
    exit;
}
$initData = parse_ini_file($ini_file);
if (!empty($initData)) {
    $_SERVER = array_merge($_SERVER, $initData);
}*/

if (class_exists(Database_DB::class)) {
    class_alias(Database_DB::class, 'DB');
}

if(ENV_CLI) {
    $ini_file = PATH_ROOT . "/system/SINASRV_CONFIG";
    if (!is_file($ini_file)) {
        echo "Can't find the SINASRV_CONFIG";
        exit;
    }
    $initData = parse_ini_file($ini_file);
    if (!empty($initData)) {
        $_SERVER = array_merge($_SERVER, $initData);
    }
}
define('ENVIRONMENT',$_SERVER['ENVIRONMENT']);
function autoload($classname)
{
    $classname = str_replace("\\", '_', trim($classname, "\\"));
    $classFile = PATH_ROOT . DS . str_replace('_', DS, $classname) . ".php";
    !file_exists($classFile) && $classFile = PATH_ROOT . DS . "Lib" . DS . str_replace('_', DS, $classname) . ".php";
    if (file_exists($classFile)) {
        include $classFile;
    } else {
        header('Location:https://weibo.com/sorry');
        exit;
    }
}
