<?php



abstract class Daemon_Abstract {
    
    private $ip;
    private $pid;
    private $proc_total;
    private $proc_no;
    
    private $stop;

    protected $cronName = '';
    protected $exceptions = array();
    
    public function __construct() {
        $this->cronName .= time();
        echo $this->cronName, ' start at ', date('Y-m-d H:i:s'), "\n";
        $this->proc_total = $_SERVER['argv'][1];
        $this->proc_no = $_SERVER['argv'][2];
        $this->stop = false;
        $this->pid = posix_getpid();

//        pcntl_signal(SIGTERM,	array(&$this, 'signalHandler'));
//        pcntl_signal(SIGINT,	array(&$this, 'signalHandler'));
//        pcntl_signal(SIGHUP,	array(&$this, 'signalHandler'));
//        pcntl_signal(SIGCHLD,	array(&$this, 'signalHandler'));
        
        $this->run();
        echo $this->cronName, ' end at ', date('Y-m-d H:i:s'), "\n";
    }
    
    

    abstract public function run() ;
    
    public function stop() {
        $this->stop = true;
    }
    
    
    public function isStop() {
//        pcntl_signal_dispatch();
        return $this->stop;
    }
    
    public function signalHandler($signo){
        switch ($signo) {
            case SIGUSR1: 
                echo "SIGUSR1\n"; break;
            case SIGUSR2: 
                echo "SIGUSR2\n"; break;
            case SIGTERM: 
                echo "SIGTERM\n"; break;
            case SIGINT: 
                echo "SIGINT\n"; break;
            case SIGHUP: 
                echo "SIGHUP\n"; break;
            case SIGCHLD: 
                echo "SIGCHLD\n"; break;
            default:      
                echo "unknow";    break;
        
        }
    }
    
    
    protected function log($type, $content) {
        
    }
    
    //获取进程总数
    protected function getProcTotal() {
        return $this->proc_total;
    }

    //获取当前进程数,  从1开始
    protected function getProcNo() {
        return $this->proc_no;
    }
    
    //获取当前进程ID
    protected function getPid() {
        return $this->pid;
    }
    
    protected function getIp() {
        if($this->ip == null) {
            $str = "/sbin/ifconfig | grep 'inet addr' | awk '{ print $2 }' | awk -F ':' '{ print $2}' | head -1";
            $ip = exec($str);
            $this->ip = $ip;
        }
	return $this->ip;
    }
    
    //获取任务名称
    protected function getDaemonName() {
        if(!empty($this->cronName)){
            return $this->cronName;
        }
        return get_class($this);
    }
    protected function info($msg){
        Lib_Log::info("{$this->getDaemonName()}|{$msg}");
    }
    protected function warning($msg){
        Lib_Log::warning("{$this->getDaemonName()}|{$msg}");
    }
}