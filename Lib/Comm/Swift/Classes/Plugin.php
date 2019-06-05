<?php
/**
 * 插件基类
 *
 * @package   Swift
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */

abstract class Swift_Plugin {
    public $is_cli_enable;
    
    public function get_name(){
        return substr(strtolower(get_class($this)), 7);
    }
    
    abstract public function run();
}
