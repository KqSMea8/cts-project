<?php
class Tool_Log_Ssm {
	static protected $writer;
	static protected $formatter;
	
	static public function get_writer(){
		if(self::$writer === NULL){
			self::$writer = new Comm_Log_Writer_Syslog("miniblog", LOG_PID, LOG_LOCAL6);	
		}
		return self::$writer;
	}
	static public function get_formatter(){
		if(self::$formatter === NULL){
			self::$formatter = new Tool_Log_Ssmformater(LOG_INFO);
		}
		return self::$formatter;
	}
        
    public static function write($log_actionid, $propty, $ext) {
        self::get_formatter()->set_propty($log_actionid, $propty,$ext);
        self::get_writer()->write(array(self::get_formatter()));
    	return TRUE;
    }   
}