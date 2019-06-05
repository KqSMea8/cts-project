<?php
class Tool_Log_Ssmformater extends Comm_Log_Formatter {
    const DELIMITER = "\t";
    public $type = "ssm";
    public $actiontype = array();
    public $propty = array();
    public $ext = "";
    public $loglevel = LOG_NOTICE;
    public $propty_key = array(
		'date','ip','uid','action','source','method','status','optobj','fuid',
    );
	/* (non-PHPdoc)
     * @see Comm_Log_Formatter::__construct()
     */
    public function __construct($loglevel=LOG_NOTICE) {
    	$this->actiontype = Comm_Config::get("action_log.action");
		$this->loglevel = $loglevel;
    }

	/* (non-PHPdoc)
     * @see Comm_Log_Formatter::get_date()
     */
    public function get_date() {
       return date("Y-m-d");
    }

	/* (non-PHPdoc)
     * @see Comm_Log_Formatter::get_string()
     */
    public function get_string() {
		$result = $this->propty['action'] . "|" . self::format_propty() . self::DELIMITER . self::format_ext();
		return $result;
    }

	/* (non-PHPdoc)
     * @see Comm_Log_Formatter::get_syslog_level()
     */
    public function get_syslog_level() {
        return $this->loglevel;    
    }

	/* (non-PHPdoc)
     * @see Comm_Log_Formatter::get_type()
     */
    public function get_type() {
        return $this->type;
    }
	public function set_propty($log_actionid, $propty, $ext){
		if(!is_array($propty)){
			return FALSE;
		}
		if (!in_array($log_actionid, $this->actiontype)) {
			return FALSE;
		} else {
		    $propty['action'] = $log_actionid;
		}
		$propty['date'] = date("Y-m-d");
		$propty['ip'] = Comm_Context::get_client_ip();
		$this->propty = $propty;
		
		$ext['location'] = (isset($ext['location']) && !empty($ext['location'])) ? $ext['location'] : Comm_Context::get_server('HTTP_REFERER');
		$ext['version'] = '4';
		$ext['Layout'] = (Tool_Version::get_version_mark() == Tool_Version::VERSION_V36) ? 'narrow' : 'wide';
		$this->ext = $ext;
		return;
	}
    
	public function format_ext(){
		if(empty($this->ext)){
			return "";
		}
		if(!is_array($this->ext)){
			return $this->ext;
		}
		$restr = "";
		$s = "";
		foreach ($this->ext as $k => $v){
			$restr .= $s . $k . "=>" . $v ;
			$s = ",";
		}
		return $restr;
	}
	public function format_propty(){
		$restr = "";
		$s = "";
		foreach ($this->propty_key as $k){
			$restr .= $s . (isset($this->propty[$k]) ? $this->propty[$k] : '');
			$s = self::DELIMITER;
		}
		return $restr;
	}
	
	public function write(){
		$syslog_obj = new Comm_Log_Writer_Syslog("miniblogV4", LOG_PID, LOG_LOCAL6);
		$syslog_obj->write(array($this));
	}
     
}