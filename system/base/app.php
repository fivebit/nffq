<?php
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.lei@gmail.com
 * This framework is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation
 **************************************************************************/
class App {
    public $config = array();
    public $charset = 'utf-8';

    public $default_module = 'site';
    public $default_controller = 'index';
    public $default_action = 'index';

	public $controller = null;
	public $action = null;
	public $module = null;

    public $app_root = null;
	public $remoteIP = null;
	public $requestURI = null;
	public $timer = null;
	public $rqtTime = null; 
    public $logger = null;
    public $requestid = '';


    public function __construct($config=null){
        if(is_string($config)){
            $config = require($config);
        }
        $this->config = $config;
        $this->init();
    }
	public function init_request() {
        $httpHost = $_SERVER['HTTP_HOST'];
        $script_name = $_SERVER['SCRIPT_NAME'];
        $requestURI = $this->getRequestURI();
        $pos = strpos($requestURI,"?");
        if($pos !== false){
            $requestURI = substr($requestURI,0,$pos);
        }
        $prefix = $script_name;
        if(strlen($script_name) > strlen($requestURI)){
            $prefix = $requestURI;
        }
        $requestURI = str_replace($prefix,"",$requestURI);

        $uriArray = explode('/', trim($requestURI, '/'));
        $module = $this->default_module;
        $controller = $this->default_controller;
        $action = $this->default_action;
        if( 3 === count($uriArray)){
			$module = strtolower($uriArray[0]);
			$controller = strtolower($uriArray[1]);
			$action = strtolower($uriArray[2]);
		}elseif( 2 === count($uriArray)){
			$controller = strtolower($uriArray[0]);
			$action = strtolower($uriArray[1]);
        }elseif(1 === count($uriArray) && !empty($uriArray[0]) ){
			$controller  = strtolower($uriArray[0]);
        }
        //may be add
        if(isset($_GET['action']) && preg_match("/^[_a-zA-Z0-9-]+$/", $_GET['action'])){
            $action = strtolower($_GET['action']);
        }
		if(isset($_REQUEST['module']) && !empty($_REQUEST['module']) && preg_match("/^[_a-zA-Z0-9-]+$/", $_GET['module'])) {
			$_REQUEST['module'] = trim($_REQUEST['module']);
			$module = strtolower($_REQUEST['module']);
		}
		if(isset($_REQUEST['controller']) && !empty($_REQUEST['controller']) && preg_match("/^[_a-zA-Z0-9-]+$/", $_GET['controller'])){
			$_REQUEST['controller'] = trim($_REQUEST['controller']);
			$controller = strtolower($_REQUEST['controller']);
		}
		$this->module = $module;
		$this->controller = $controller;
		$this->action = $action;
	}
	
    public function getRequestURI() {
        if(!is_null($this->requestURI)){
            return $this->requestURI;
        }
        if (isset ($_SERVER ['REQUEST_URI'])) {
            $requestURI = $_SERVER ['REQUEST_URI'];
        } else if (isset ($_SERVER ['QUERY_STRING'])) {
            $requestURI = $_SERVER ['QUERY_STRING'];
        } else {
            $requestURI = $_SERVER ['PHP_SELF'];
        }
        $this->requestURI = $requestURI;
        return $this->requestURI;
    }

	public function getClientIp() {
		if(!is_null($this->remoteIP)){
			return $this->remoteIP;
		}
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$ip  = $ips[0];
		} else if (!empty($_SERVER['HTTP_CLIENTIP'])) {
			$ip = $_SERVER['HTTP_CLIENTIP'];
		} else if (!empty($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		} else {
			$ip = '127.0.0.1';
		}
        $this->remoteIP = $ip;
		return $ip;
	}

    function run(){
        $this->timer->set("runstart");
        $this->onBeginRequest();
        $this->process();
        $this->onEndRequest();
        $this->timer->set("run_end");
        $this->logger->notice("request cost time:".$this->timer->getString());
    }
    //子类需要继承
    function process(){
        $this->timer->set("process_begin");
        $classfile = $this->app_root."controllers/".$this->controller.".php";
        if(file_exists($classfile)){
            require_once $classfile;
            $class_name = ucfirst($this->controller)."Controller";
            $func = ucfirst($this->action)."Action";
            if(!class_exists($class_name)){
                $this->logger->fatal("cant find: ".$class_name);
                throw new Exception("cant find class_name");
            }
            $obj = new $class_name();
            $this->logger->notice("init class:$class_name");
        }else{
            $this->logger->fatal("cant find: ".$this->controller." controller");
            throw new Exception("cant find controller");
        }
        try {
            $this->_callmethod ( $obj, '_before' );
            if (! $this->_callmethod ( $obj, $func, array() )) {
                throw new Exception ( "method failed [ method name: $func ]" );
            }
        } catch ( Exception $ex ) {
            $this->_callmethod ( $obj, '_after' );
            throw $ex;
        }
        $this->_callmethod ( $obj, '_output' );
        $this->timer->set("process_end");
    }
    private function _callmethod($controller, $method, $args = array()) {
        if (is_callable ( array ($controller, $method ) )) {
            $reflection = new ReflectionMethod ( $controller, $method );
            $argnum = $reflection->getNumberOfParameters ();
            if ($argnum > count ( $args ) + 1 ) {
                throw new Exception ( "not_found call method failed [ method name: $method ]." );
            }
            $reflection->invokeArgs ( $controller, $args );
            return true;       
        }                      
        return false;
    }
    //这里面添加钩子
    public function onBeginRequest(){

    }
    public function onEndRequest(){

    }
    public function init(){
        $this->init_config();
        $this->init_env();
        $this->init_handler();
        $this->preload();

        $this->init_request();
    }
    public function preload(){
        static $_preloads = array();
        $load_hash = array('db'=>"Mysql_BaseDb",'cache'=>"RedisDao");
        if(isset($this->config['preloads'])){
            $preloads = $this->config['preloads'];
            foreach($preloads as $type){
                if(isset($load_hash[$type])){
                    load_file($load_hash[$type],$type);
                    $_preloads[$type] = new $load_hash[$type]();
                }
            }
        }
        if(isset($this->config['init'])){
            foreach($this->config['init'] as $type){
                if(isset($load_hash[$type])){
                    if(!isset($_preloads[$type])){
                        load_file($load_hash[$type],$type);
                        $_preloads[$type] = new $load_hash[$type]();
                    }
                    $config = array();
                    if(isset($this->config[$type])){
                        $config = $this->config[$type];
                    }
                    $_preloads[$type]->init($config); 
                    setMQ($type,$_preloads[$type]);
                }
            }
        }
    }
    public function init_config(){
        $config = $this->config;
        if(isset($config['default_action'])){
            $this->default_action = $config['default_action'];
        }
        if(isset($config['default_controller'])){
            $this->default_controller = $config['default_controller'];
        }
    }
    public function init_handler(){
		set_error_handler(array($this,'errorHandler'));
		set_exception_handler(array($this,'exceptionHandler'));
    }
    public function init_env(){
        if(isset($this->config['app_root'])){
            $this->app_root = $this->config['app_root'];      //配置文件可以指定
        }else{
            $this->app_root = APP_ROOT;     //依赖具体应用，用户路由和log的生成
        }
        $this->requestid = $this->genRequestId();
        $this->init_log();
        $this->timer = load_class('timer','compose');
    }
    public function init_log(){
        load_file("logger",'base');
        $logconfig = array();
        if(isset($this->config['log'])){
            $logconfig = $this->config['log'];
        }
        isset($logconfig['logname'])?"":$logconfig['logname']='qiong';
        isset($logconfig['logpath'])?"":$logconfig['logpath'] =$this->app_root."log";
        isset($logconfig['loglevel'])?"":$logconfig['loglevel'] = 16;
        isset($logconfig['slice'])?"":$logconfig['slice'] = 0;
        $logconfig['requestid'] = $this->requestid;
        $this->logger = new logger($logconfig);
        $this->logger->notice('init system begin');
        setMQ('logger',$this->logger);
    }
    public function errorHandler(){
        restore_error_handler();
        $error = func_get_args();       
        $st = false;
        if (!($error[0] & error_reporting())) {
            $this->logger->debug('caught info, errno:%d,errmsg:%s,file:%s,line:%d',$error[0],$error[1],$error[2],$error[3]);
            set_error_handler(array($this,'errorHandler'));
        } elseif ($error[0] === E_USER_NOTICE) { 
            $this->logger->trace('caught trace, errno:%d,errmsg:%s,file:%s,line:%d',$error[0],$error[1],$error[2],$error[3]);
            set_error_handler(array($this,'errorHandler'));
        } elseif($error[0] === E_STRICT) {
            set_error_handler(array($this,'errorHandler'));
        } else {               
            $this->logger->fatal('caught error, errno:%d,errmsg:%s,file:%s,line:%d',$error[0],$error[1],$error[2],$error[3]);
            $st = true;       
        }              
        if($st == false){
            return;
        }
        unset($error[4]);
        echo "<pre>\n";
        print_r($error);
        echo "\n</pre>";
    }
    public function exceptionHandler($ex){
        restore_exception_handler();
        $errMsg = $ex->getMessage();
        $redirect = !!$ex->getCode();
        $this->logger->fatal($errMsg);
        echo "<pre>\n";
        print_r($ex->__toString());
        echo "\n</pre>";
    }
    private function genRequestId() {
        if (isset ( $_SERVER ['HTTP_CLIENTAPPID'] )) {
            return intval ( $_SERVER ['HTTP_CLIENTAPPID'] );
        }   
        $reqip = $this->getClientIp();
        $time = gettimeofday (); 
        $time = $time ['sec'] * 100 + $time ['usec'];
        $ip = ip2long ( $reqip );
        $id = ($time ^ $ip) & 0xFFFFFFFF;
        load_file('sign','compose');
        $id = Sign::sign64($id . '_' . rand(1, 800000000));
        return $id;
    }                                                     
}
?>
