<?php
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/
 
/**
 * @file index.php
 * @author fivebit(@fivebit.com)
 * @date 2015/11/19 17:26:03
 *  
 **/
defined("BASE_ROOT") or define("BASE_ROOT",dirname(dirname(__FILE__))."/");
defined("APP_ROOT") or define("APP_ROOT",dirname(__FILE__)."/");
$nffq = BASE_ROOT."system/nffq.php";
require_once($nffq);
defined('ENVIRONMENT') or define("ENVIRONMENT",'dev');
$config=APP_ROOT."config/".ENVIRONMENT.".php";
Nffq::createWebApp($config)->run();


/* vim: set ts=4 sw=4 sts=4 tw=100 @qiong*/
?>
