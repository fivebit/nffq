<?php
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/
 
/**
 * @file nffq.php
 * @author fivebit(@fivebit.com)
 * @date 2015/11/19 17:39:03
 *  
 **/

define("VERSION",'0.1');
defined('NFFQ_BEGIN_TIME') or define('NFFQ_BEGIN_TIME',microtime(true)); 
define('ENVIRONMENT', isset($_SERVER['NFFQ_ENV']) ? $_SERVER['NFFQ_ENV'] : 'dev');

switch(ENVIRONMENT){
case 'dev':
    error_reporting(-1);
    ini_set("display_errors",1);
    break;
case 'test':
case 'prod':
    ini_set("display_errors",0);
    if (version_compare(PHP_VERSION, '5.3', '>=')) {
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
    }else{
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE); 
    }
default:
    echo "environment error";
    exit(1);
}
defined('NFFQ_ROOT') or define('NFFQ_ROOT',dirname(__FILE__)."/");
ini_set('date.timezone','Asia/Shanghai'); 

require_once NFFQ_ROOT."base/common.php";

class Nffq{
    public static $app;
    public static function app(){
        return self::$app;
    }
    public static function setApp($app){
        if(self::$app=== null || $app === null){
            self::$app = $app;
        }
    }
    public static function createWebApp($config=null){
        self::$app = load_class("app",'base',$config);
        return self::$app;
    }
    public static function createCmdApp($config = null){
        self::$app = load_class("app",'base',$config);
        return self::$app;
    }
}
/* vim: set ts=4 sw=4 sts=4 tw=100 @for qiong*/
?>
