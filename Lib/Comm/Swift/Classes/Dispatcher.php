<?php
/**
 * Swift请求分发
 *
 * @package  Swift
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Swift_Dispatcher {
    public static $uri;
    public static $plugins;
    public static $plugins_order;
    
    public static $controller;
    public static $controller_class;
    public static $controller_prefix = 'Controller';
    public static $controller_obj;
    public static $default_controller = "home";
    
    const CONTROLLER_PATTERN = '#^(?P<controller>(/{1}[a-z0-9]+){0,5})/?$#uD';
    
    /**
     * 将URL转换为controller
     * 
     * @param string  URI
     * @return bool
     */
    private static function match($uri) {
        // URI不区分大小写，明确的界定符/，确保正则匹配
        $uri = "/" . trim(strtolower($uri), "/");
        if (preg_match(self::CONTROLLER_PATTERN, $uri, $matches)) {
            if (empty($matches['controller'])) {
                self::$controller = self::$default_controller;
            } else {
                self::$controller = trim($matches['controller'], "/");
            }
            return true;
        } else {
            return false;
        }
    }
    
    public static function parse_uri() {
        if (Swift_Core::$is_cli) {
            $options = Swift_Dispatcher::detect_cli_options('uri', 'get', 'post');
            
            if (isset($options['uri'])) {
                $uri = $options['uri'];
            }
            
            if (isset($options['get'])) {
                parse_str($options['get'], $_GET);
            }
            
            if (isset($options['post'])) {
                parse_str($options['post'], $_POST);
            }
        } else {
            $uri = Swift_Dispatcher::detect_uri();
        }
        
        $uri = preg_replace('#//+#', '/', $uri);
        $uri = preg_replace('#\.[\s./]*/#', '', $uri);
        $uri = trim($uri, '/');
        
        self::$uri = $uri;
    }
    
    /**
     * 添加plugin
     * 
     * @var string $when before_pre_execute, before_execute, before_post_execute
     * @var $plugin_obj plugin实例
     */
    public static function add_plugin($when, $plugin_obj) {
        if (!$plugin_obj instanceof Swift_Plugin) {
            throw new Swift_Exception_Program("must be is a Swift_Plugin");
        }
        
        if (!in_array($when, array("before_route", "after_route", "before_execute", "after_execute"))) {
            throw new Swift_Exception_Program("invalid plugin position");
        }
        
        $plugin_name = $plugin_obj->get_name();
        if (isset(self::$plugins[$plugin_name])) {
            throw new Swift_Exception_Program("the plugin added already");
        }
        
        self::$plugins_order[$when][] = $plugin_name;
        self::$plugins[$plugin_name] = $plugin_obj;
    }
    
    /**
     * 获取plugin
     * 
     * @param string $name
     * @throws Swift_Exception_Program
     */
    public static function get_plugin($name) {
        if (!isset(self::$plugins[$name])) {
            throw new Swift_Exception_Program("plugin not exists");
        }
        
        return self::$plugins[$name];
    }
    
    /**
     * 调用各阶段插件
     * 
     * @param string $when
     */
    public static function run_plugin($when) {
        if (isset(self::$plugins_order[$when])) {
            foreach (self::$plugins_order[$when] as $plugin_name) {
                $plugin_obj = self::get_plugin($plugin_name);
                if (Swift_Core::$is_cli && !$plugin_obj->is_cli_enable()) {
                    continue;
                }
                
                $plugin_obj->run();
            }
        }
    }
    
    /**
     * 封装请求的全部步骤
     *
     * @return void
     */
    public static function dispatch() {
        self::parse_uri();
        self::route();
        self::execute();
    }
    
    /**
     * 在请求正式执行前需要执行的程序
     * 
     * 主要是执行plugin
     *
     * @param string $uri
     * @return Swift_Dispatcher
     */
    public static function route() {
        self::run_plugin("before_route");
        
        if (!self::match(self::$uri)) {
            throw new Swift_Exception_404(self::$uri);
        }
        
        $tmp = explode("/", self::$controller);
        $tmp = array_map("ucfirst", $tmp);
        self::$controller_class = implode("_", $tmp);
        unset($tmp);
        
        if (!empty(self::$controller_prefix)) {
            self::$controller_class = self::$controller_prefix . "_" . self::$controller_class;
        }
        
        self::run_plugin("after_route");
    }
    
    /**
     * 处理请求, 执行controller里面的run函数，其中controller由router得出
     * 
     * 默认地,所有的从controller输出的部分都被捕获并返回， 
     * 
     * 无http头发送
     *
     * @throws  Swift_Exception_Program
     */
    public static function execute() {
        // 避免触发auto_load以及在include_path中查找，提升安全性
        if (Swift_Core::auto_load(self::$controller_class) !== true) {
            throw new Swift_Exception_404(self::$uri);
        }
        
        $class = new ReflectionClass(self::$controller_class);
        if ($class->isAbstract()) {
            throw new Swift_Exception_Program('cannot create instances of abstract controller');
        }
        
        self::$controller_obj = $class->newInstance();
        self::run_plugin("before_execute");
        
        $class->getMethod('run')->invoke(self::$controller_obj);
        self::run_plugin("after_execute");
    }
    
    /**
     * 返回命令行请求的参数值. 
     * 
     * 选项由标准CLI语法指定：
     *
     * php index.php --username=john.smith --password=secret --var="some value with spaces"
     *
     * // 获取"username" 和"password"的值
     * $auth = CLI::options('username', 'password');
     *
     * @param   string  参数名
     * @param   ...
     * @return  array
     */
    public static function detect_cli_options($options) {
        $options = func_get_args();
        
        $values = array();
        
        // 第一个参数为当前执行文件
        for($i = 1; $i < $_SERVER['argc']; $i ++) {
            if (!isset($_SERVER['argv'][$i])) {
                break;
            }
            
            $opt = $_SERVER['argv'][$i];
            if (substr($opt, 0, 2) !== '--') {
                continue;
            }
            // 移除前缀 "--"
            $opt = substr($opt, 2);
            
            if (strpos($opt, '=')) {
                list($opt, $value) = explode('=', $opt, 2);
            } else {
                $value = NULL;
            }
            
            if (in_array($opt, $options)) {
                $values[$opt] = $value;
            }
        }
        
        return $values;
    }
    
    /**
     * 自动的使用 PATH_INFO,REQUEST_URI, PHP_SELF or REDIRECT_URL获取请求URI,
     * 
     * @return  string  URI
     * @throws  Swift_Exception_Program
     */
    public static function detect_uri() {
        // 不受base_url影响
        if (!empty($_SERVER['PATH_INFO'])) {
            $uri = $_SERVER['PATH_INFO'];
        } else {
            if (isset($_SERVER['REQUEST_URI'])) {
                // 提取path部分
                $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $uri = rawurldecode($uri);
            } elseif (isset($_SERVER['PHP_SELF'])) {
                $uri = $_SERVER['PHP_SELF'];
            } elseif (isset($_SERVER['REDIRECT_URL'])) {
                $uri = $_SERVER['REDIRECT_URL'];
            } else {
                throw new Swift_Exception_Program('can not detect uri');
            }
            
            $base_url = rtrim(Swift_Core::$base_url, '/') . '/';
            $base_url = parse_url($base_url, PHP_URL_PATH);
            
            if (strpos($uri, $base_url) === 0) {
                // Remove the base URL from the URI
                $uri = substr($uri, strlen($base_url));
            }
        }
        
        return $uri;
    }
}
