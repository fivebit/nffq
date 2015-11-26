<?php
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/

class logger{
	// 日志级别
	const LOG_FATAL   = 0;
	const LOG_WARNING = 2;
	const LOG_NOTICE  = 4;
	const LOG_TRACE   = 8;
	const LOG_DEBUG   = 16;

    // 日志切割方式
	const SLICE_NONE  = 0;   //!< 不切割
    const SLICE_MONTH = 1;   //!< 按月切割
    const SLICE_DAY   = 2;   //!< 按日切割
	const SLICE_HOUR  = 3;   //!< 按小时切割

	static $LOG_NAME = array(
        self::LOG_FATAL   => 'FATAL',
        self::LOG_WARNING => 'WARNING', 
        self::LOG_NOTICE  => 'NOTICE', 
        self::LOG_TRACE   => 'TRACE', 
        self::LOG_DEBUG   => 'DEBUG',
	);

	private $_logname   = "";
	private $_logfile   = "";
	private $_logwffile = "";
	private $_loglevel  = self::LOG_DEBUG;
	private $_slice     = self::SLICE_NONE;
	private $_print     = false;
    private $reqid = '';

    /**
     * 构造函数
     */
    public function __construct ($arrConfig) {
		$this->_logname   = strtoupper($arrConfig['logname']);
		$this->_logfile   = $arrConfig['logpath']."/".$arrConfig['logname'].".log";
		$this->_logwffile = $arrConfig['logpath']."/".$arrConfig['logname'].".log.wf";
		$this->_loglevel  = $arrConfig['loglevel'];
		$this->_slice     = $arrConfig['slice'];

		$this->_print     = isset($arrConfig['print']) ? $arrConfig['print'] : false;
        $this->reqid      = isset($arrConfig['requestid'])?$arrConfig['requestid']:'';
    }

	/* 记录日志接口 */
    public function fatal()  { $arg=func_get_args(); $this->write_log(self::LOG_FATAL,$arg); }
    public function warning(){ $arg=func_get_args(); $this->write_log(self::LOG_WARNING,$arg); }
    public function notice (){ $arg=func_get_args(); $this->write_log(self::LOG_NOTICE,$arg); }
    public function trace()  { $arg=func_get_args(); $this->write_log(self::LOG_TRACE,$arg); }
    public function debug()  { $arg=func_get_args();$this->write_log(self::LOG_DEBUG,$arg); }


	//=============以下是私有函数==============

    private function write_log ($strType, $arr_data) {
		//1. 日志级别过滤
		$loglevel = intval($strType);
		if($loglevel>$this->_loglevel || ($loglevel%2)===1){
			return;
		}

		//2. 根据日志级别，获取相应的日志文件
		$logfile = $this->_logfile;
        if($loglevel <= self::LOG_WARNING) {
            $logfile = $this->_logwffile;
        }
        
		//3. 切割方案
		switch($this->_slice){
			case self::SLICE_NONE:  break;
			case self::SLICE_MONTH: $logfile.=".".date('Ym'); break;
			case self::SLICE_DAY:   $logfile.=".".date('Ymd'); break;
			case self::SLICE_HOUR:  $logfile.=".".date('Ymd-H'); break;
		}

		//4. 日志内容
		$str = sprintf("%s\t%s\t%s\t", self::$LOG_NAME[$loglevel], date("m-d H:i:s"),$this->reqid, $this->_logname);
        if(!empty($arr_data)) {            
            $str .= implode("\t",$arr_data);        
        }
        $strLog = '';
        switch ($strType) {
            case self::LOG_WARNING:
            case self::LOG_FATAL:
            case self::LOG_DEBUG:
                $debug_info = debug_backtrace();                
                $strLog .= "[{$debug_info[1]['file']}" . ":" . $debug_info[1]['line'] . "]\t";                
                $strLog .= $str;
                break;
            case self::LOG_TRACE:
            case self::LOG_NOTICE:
                $debug_info = debug_backtrace();                
                $strLog .= "[{$debug_info[1]['file']}" . ":" . $debug_info[1]['line'] . "]\t";                
                $strLog .= $str;
                break;
            default:
                break;
        }
		$strLog = str_replace("\n","",$strLog);
        
		//5. 写日志
		$this->write_file($logfile, $strLog."\n");
    }

    private function write_file ($strPath, $str) {
        @error_log($str, 3, $strPath);
		if ($this->_print) {
			echo $str;
		}
        return;
    }
}
function log_fatal(){
    global $logger;
    if(isset($logger)){
        $arg = implode("\t",func_get_args());
        $logger->fatal($arg[0]);
    }
}
function log_warning(){
    global $logger;
    if(isset($logger)){
        $arg = implode("\t",func_get_args());
        $logger->warning($arg[0]);
    }
}
function log_notice(){
    global $logger;
    if(isset($logger)){
        $arg = implode("\t",func_get_args());
        $logger->notice($arg[0]);
    }
}
function log_trace(){
    global $logger;
    if(isset($logger)){
        $arg = implode("\t",func_get_args());
        $logger->trace($arg[0]);
    }
}
function log_debug(){
    global $logger;
    if(isset($logger)){
        $arg = implode("\t",func_get_args());
        $logger->debug($arg);
    }
}


// vim600: sw=4 ts=4 fdm=marker syn=php
?>
