<?php
/**
 * Swift包的核心函数
 *
 * @package    Swift
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Swift_Core {
    
    const NAME = 'Swift';
    const VERSION = '0.2.7';
    
    /**
     * @var bool 判断该请求是否命令行调用
     */
    public static $is_cli = false;
    
    /**
     * @var string 框架支持以doc_root下某个子目录的方式启动
     */
    public static $base_url = '/';
    
    /**
     * @var bool 防止Swift重复初始化
     */
    private static $init = false;
    
    /**
     * @var array php exception的友好别名
     */
    private static $php_errors = array(E_ERROR => 'Fatal Error', E_USER_ERROR => 'User Error', E_PARSE => 'Parse Error', E_WARNING => 'Warning', E_USER_WARNING => 'User Warning', E_STRICT => 'Strict', E_NOTICE => 'Notice', E_RECOVERABLE_ERROR => 'Recoverable Error');
    
    /**
     * @var bool 是否显示跟踪调试信息
     */
    private static $show_trace_info = false;
    
    /**
     * @var array 指定find_file需要遍历的文件夹路径
     */
    private static $paths = array(APPPATH, SWFPATH);
    
    public static function get_full_name() {
        return self::NAME . ' v' . self::VERSION;
    }
    
    /**
     * 从版本号字符串中计算每级子版本
     * 给定一个字符串，按.(decimal)来分隔字符串，以求得到一个版本号，子版本号最多三级(例如 1.2.3)。同时会对计算出的各
     * 子版本号进行强制转整型。
     * @param string $version_string
     * @return array
     */
    public static function split_version_from_string($version_string) {
        $version = explode('.', $version_string, 3);
        foreach ($version as $i => $v) {
            $version[$i] = intval($v);
        }
        return $version;
    }
    
    /**
     * 根据指定的比较符(>,<,=或==,>=,<=,!=或<>不等于)去比较当前版本与指定版本号。若满足指定的条件，则返回真，否则返回假
     * 比较方式： 当前版本 (比较符) 指定版本
     * @param string $version_to_compare	指定的版本
     * @param string $comparision	比较符
     * @return boolean
     */
    public static function compare_version($version_to_compare, $comparision = '>=') {
        $now = self::split_version_from_string(self::VERSION);
        $compare_to = self::split_version_from_string($version_to_compare);
        
        $result = 0;
        foreach ($compare_to as $section => $ver) {
            $result = $ver - $now[$section];
            if ($result) {
                break;
            }
        }
        
        if ($result > 0) {
            return in_array($comparision, array('<', '<=', '!=', '<>'));
        } elseif ($result < 0) {
            return in_array($comparision, array('>', '>=', '!=', '<>'));
        } else {
            return in_array($comparision, array('=', '==', '<=', '>='));
        }
    }
    
    /**
     * 初始化Swift
     *
     * 禁止 register_globals 和 magic_quotes_gpc
     * 标准化 GET, POST, and COOKIE 变量
     *
     * @param array settings
     * @return void
     */
    public static function init(array $settings) {
        if (Swift_Core::$init) {
            throw new Swift_Exception_Program("Swift_Core can't init twice");
        }
        
        Swift_Core::$init = true;
        
        if (defined('E_DEPRECATED')) {
            //  PHP >= 5.3.0
            Swift_Core::$php_errors[E_DEPRECATED] = 'Deprecated';
        }
        
        if (isset($settings['error_to_exception']) && $settings['error_to_exception'] == true){
            // 将所有错误当成exception来处理
            set_error_handler(array('Swift_Core', 'error_handler'));
        }
        
        if (isset($settings['show_trace_info']) && $settings['show_trace_info'] === true) {
            self::$show_trace_info = true;
            set_exception_handler(array('Swift_Core', 'exception_handler'));
            register_shutdown_function(array('Swift_Core', 'shutdown_handler'));
        }
        
        if (isset($settings['base_url'])) {
            Swift_Core::$base_url = $settings['base_url'];
        }
        
        if (ini_get('register_globals')) {
            throw new Swift_Exception_Program("register_globals can not be enable");
        }
        
        Swift_Core::$is_cli = (PHP_SAPI === 'cli');
        
        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding('utf-8');
        }
        
        // hack, because object needle
        //        register_shutdown_function(array(new Swift_Log(), 'write_all'));
        ini_set('unserialize_callback_func', 'spl_autoload_call');
    }
    
    /**
     * 自动加载class
     *
     * @param string $class
     * @return boolean
     */
    public static function auto_load($class) {
        /*
    	  for security concerns, we should check the illegal chars.
    	  the reason of using trim instead of preg_match('/^\w$/iD', $class), is that
    	  even a trim(a_big_charlist) is a bit faster(~10%) than preg_match('\w')
    	 */
        if (trim($class, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz1234567890')) {
            return false;
        }
        $file = str_replace('_', '/', strtolower($class));
        
        // 以Swift_打头
        if (strpos($class, "Swift_") === 0) {
            // 去除Swift/
            $file = substr($file, strpos($file, '/') + 1);
            $path = SWFPATH . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . $file . '.php';
        }elseif (strpos($class, 'Comm_') === 0) {// 以Comm_打头
            // 去除Comm/
            $file = substr($file, strpos($file, '/') + 1);
            $path = LIBPATH . DIRECTORY_SEPARATOR . $file . '.php';
        }else{//其他
	        $path = APPPATH . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . $file . '.php';
        }
		if(file_exists($path)){
			require $path;
		}
		//最后再检查一次类是否已经正确加载，如果为false,说明类名与swift的加载规则不兼容，需要返回false以使后续程序能够正确处理。
        return class_exists($class, false);
        //smarty还定义了一个auto load方法，不能就此打住
    }
    
    /**
     * 在多个path中查找指定文件
     *
     * @param string 目录名称 (views, i18n, classes, extensions, etc.)
     * @param string 带有子目录的文件名 
     * @param boolean 返回路径数组或者单个路径
     * @param bool $find_multi 是否查找多个路径
     * @return array|string|bool 查找到的路径名数组或者单个路径名。如果未查找到，返回false
     */
    public static function find_file($dir, $file, $find_multi = false) {
        $path = $dir . DIRECTORY_SEPARATOR . $file . ".php";
        
        if ($find_multi) {
            $paths = Swift_Core::$paths;
            $paths = array_reverse($paths);
            $found = array();
            foreach ($paths as $dir) {
            	
                $tmp = $dir . DIRECTORY_SEPARATOR . $path;
                if (is_file($dir . DIRECTORY_SEPARATOR . $path)) {
                    $found[] = $dir . DIRECTORY_SEPARATOR . $path;
                }
            }
        } else {
            $found = false;
            $paths = Swift_Core::$paths;
            foreach ($paths as $dir) {
                if (is_file($dir . DIRECTORY_SEPARATOR . $path)) {
                    $found = $dir . DIRECTORY_SEPARATOR . $path;
                    break;
                }
            }
        }
        
        return $found;
    }
    
    /**
     * 包含一个文件
     *
     * @param string
     * @return mixed
     */
    public static function load($file) {
        return include $file;
    }
    
    /**
     * PHP 错误处理, 把所有的PHP错误转化为ErrorException. 
     * 这里的错误处理要根据error_reporting的设置来处理.
     *
     * @throws ErrorException
     * @return void
     */
    public static function error_handler($code, $error, $file = NULL, $line = NULL) {
        $need_ignore_errors = self::get_ignore_error_types();
    	
        if ((error_reporting() & $code &~ $need_ignore_errors) === $code) {
            throw new ErrorException($error, $code, 0, $file, $line);
        }elseif (error_reporting() & $code){
        	echo "NOTICE[{$code}]:{$error} @{$file}[{$line}]\n";
        }
    }
    
    /**
     * PHP exception 处理, 显示错误信息，exception类型，以及生成trace tree
     *
     * @uses  Swift_Core::exception_text
     * @param object exception 对象
     * @return boolean
     */
    public static function exception_handler(Exception $e) {
        try {
            $type = get_class($e);
            $code = $e->getCode();
            $message = $e->getMessage();
            $file = $e->getFile();
            $line = $e->getLine();
            $exception_txt = Swift_Core::exception_text($e);
            
            $trace = $e->getTrace();
            if ($e instanceof ErrorException) {
                // 替换为human readable
                if (isset(Swift_Core::$php_errors[$code])) {
                    $code = Swift_Core::$php_errors[$code];
                }
                
                if (version_compare(PHP_VERSION, '5.3', '<')) {
                    // 修复php 5.2下关于getTrace的bug
                    //@TODO bug url
                    for($i = count($trace) - 1; $i > 0; --$i) {
                        if (isset($trace[$i - 1]['args'])) {
                            $trace[$i]['args'] = $trace[$i - 1]['args'];
                            
                            unset($trace[$i - 1]['args']);
                        }
                    }
                }
            }
            
            ob_start();
            require 'debug.php';
            echo ob_get_clean();
        
        } catch (Exception $e) {
            ob_get_level() and ob_clean();
            echo Swift_Core::exception_text($e), "\n";
            exit(1);
        }
    }
    
    /**
     * 生成exception信息
     * 将实际路径替换为LIBPATH、APPPATH、SWFPATH
     *
     * Exception [ Code ] File [ Line x ] : Message
     *
     * @param object Exception
     * @return string
     */
    public static function exception_text(Exception $e) {
        $text = sprintf('%s [ %s ] %s [ line %d ]', get_class($e), $e->getCode(), Swift_Core::debug_path($e->getFile()), $e->getLine());
        
        $msg = strip_tags($e->getMessage());
        if (!empty($msg)) {
            $text .= " : " . $msg;
        }
        
        return $text;
    }
    
    /**
     * self::$shutdown_errors中的错误不会触发error_handler (php默认机制)
     * 如果开启了show_trace_info选项，
     *   为了确保所有错误都能显示错误消息，在init将register_shutdown_func此方法
     */
    public static function shutdown_handler() {
    	$need_ignore_errors = self::get_ignore_error_types();
        if (self::$show_trace_info && ($error = error_get_last()) && ((error_reporting() & $error['type'] &~ $need_ignore_errors) == $error['type'])) {
            ob_get_level() and ob_clean();
            self::exception_handler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
            exit(1);
        }
    }
    
    /**
     * 移除文件名中 application, system, modpath, or docroot 的 绝对地址，并用字符串取代他们
     *
     * echo Swift_Core::debug_path(Swift_Core::find_file('classes', 'swift'));
     *
     * @param string path to debug
     * @return string
     */
    public static function debug_path($file) {
        if (strpos($file, APPPATH) === 0) {
            $file = 'APPPATH' . substr($file, strlen(APPPATH));
        } elseif (strpos($file, SWFPATH) === 0) {
            $file = 'SWFPATH' . substr($file, strlen(SWFPATH));
        } elseif (strpos($file, LIBPATH) === 0) {
            $file = 'LIBPATH' . substr($file, strlen(LIBPATH));
        } elseif (defined('T3PPATH') && strpos($file, T3PPATH) === 0) {
            $file = 'T3PPATH' . substr($file, strlen(T3PPATH));
        }
        return $file;
    }
    
    /**
     * 返回HTML字符串
     * 高亮显示文件中指定的行
     *
     * @param string file to open
     * @param integer line number to highlight
     * @param integer number of padding lines
     * @return string source of file
     * @return false file is unreadable
     */
    public static function debug_source($file, $line_number, $padding = 5) {
        if (!$file or !is_readable($file)) {
            return false;
        }
        
        $file = fopen($file, 'r');
        $line = 0;
        
        $range = array('start' => $line_number - $padding, 'end' => $line_number + $padding);
        
        $format = '% ' . strlen($range['end']) . 'd';
        
        $source = '';
        while (($row = fgets($file)) !== false) {
            if (++$line > $range['end']) {
                break;
            }
            
            if ($line >= $range['start']) {
                $row = htmlspecialchars($row, ENT_NOQUOTES, "utf-8");
                $row = '<span class="number">' . sprintf($format, $line) . '</span> ' . $row;
                
                if ($line === $line_number) {
                    // 对该行高亮
                    $row = '<span class="line highlight">' . $row . '</span>';
                } else {
                    $row = '<span class="line">' . $row . '</span>';
                }
                $source .= $row;
            }
        }
        fclose($file);
        
        return '<pre class="source"><code>' . $source . '</code></pre>';
    }
    
    /**
     * 返回展现跟踪中每个步骤的HTML字符串
     *
     *
     * @param string path to debug
     * @return string
     */
    public static function trace(array $trace = NULL) {
        if ($trace === NULL) {
            $trace = debug_backtrace();
        }
        
        $statements = array('include', 'include_once', 'require', 'require_once');
        
        $output = array();
        foreach ($trace as $step) {
            if (!isset($step['function'])) {
                continue;
            }
            
            if (isset($step['file']) and isset($step['line'])) {
                $source = Swift_Core::debug_source($step['file'], $step['line']);
            }
            
            if (isset($step['file'])) {
                $file = $step['file'];
                
                if (isset($step['line'])) {
                    $line = $step['line'];
                }
            }
            
            // function()
            $function = $step['function'];
            
            if (in_array($step['function'], $statements)) {
                if (empty($step['args'])) {
                    $args = array();
                } else {
                    $args = array($step['args'][0]);
                }
            } elseif (isset($step['args'])) {
                if (!function_exists($step['function']) or strpos($step['function'], '{closure}') !== false) {
                    // Introspection on closures or language constructs in a stack trace is impossible
                    $params = NULL;
                } else {
                    if (isset($step['class'])) {
                        if (method_exists($step['class'], $step['function'])) {
                            $reflection = new ReflectionMethod($step['class'], $step['function']);
                        } else {
                            $reflection = new ReflectionMethod($step['class'], '__call');
                        }
                    } else {
                        $reflection = new ReflectionFunction($step['function']);
                    }
                    
                    $params = $reflection->getParameters();
                }
                $args = array();
                
                foreach ($step['args'] as $i => $arg) {
                    if (isset($params[$i])) {
                        $args[$params[$i]->name] = $arg;
                    } else {
                        // Assign the argument by number
                        $args[$i] = $arg;
                    }
                }
            }
            
            if (isset($step['class'])) {
                // Class->method() or Class::method()
                $function = $step['class'] . $step['type'] . $step['function'];
            }
            
            $output[] = array('function' => $function, 'args' => isset($args) ? $args : NULL, 'file' => isset($file) ? $file : NULL, 'line' => isset($line) ? $line : NULL, 'source' => isset($source) ? $source : NULL);
            
            unset($function, $args, $file, $line, $source);
        }
        
        return $output;
    }

    /**
     * 需要忽略处理的错误类型
     * 
     * 在PHP<5.3.0时，应该为 E_STRICT, E_NOTICE, E_USER_NOTICE；否则，应该再加上E_DEPRECATED和E_USER_DEPRECATED。
     */
    final static protected function get_ignore_error_types(){
    	$need_ignore_errors = E_STRICT | E_NOTICE | E_USER_NOTICE;
    	if(version_compare(PHP_VERSION, '5.3.0', '>=')){
    		$need_ignore_errors = $need_ignore_errors | E_DEPRECATED | E_USER_DEPRECATED;
    	}
    	return $need_ignore_errors;
    }
}