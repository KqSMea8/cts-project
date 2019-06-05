<?php

class Lib_Memcached extends Memcached {
    
    
    public function __construct($alias) {
        $config = Lib_Config::get("resource.memcached.$alias"); 
        
        if (is_string($config)) {
            $config = explode(' ', $config);
        } else {
            throw new Exception('Memached Config Error:'.$config); 
    	}
        
        parent::__construct();

        foreach ($config as $server) {
            list($addr, $port) = explode(':', $server, 2);
            $this->addServer($addr, $port);
        }
        $this->setOption(Memcached::OPT_NO_BLOCK, true);
        $this->setOption(Memcached::OPT_CONNECT_TIMEOUT, 200);
        $this->setOption(Memcached::OPT_POLL_TIMEOUT, 50);
        $this->setOption(Memcached::OPT_COMPRESSION, true);
        return;
    }
}
