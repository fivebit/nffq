<?php
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/
/**
 * @file dev.php
 * @author fivebit(@fivebit.com)
 * @date 2015/11/25 15:25:57
 **/

return array(
    'name'=>"qiong",
    'default_action' => "qiong",
    "preloads" => array(
        "db","cache"),
    'init' => array("db"),
    "log" => array(
        "logname"=> "app",
        'logpath'=> "/home/work/webroot/nffq/apps/log",
    ),
    "db" => array(
        array(
            'charset' => 'utf-8',
            'host' => '192.168.180.50',
            'username' => 'root',
            'password' => 'root',
            'database' => 'ucenter',
            'port' => '8808',
            'dbRetry' => 1,
        ),
    ),
    'cache' => array(
        'server' => array(
            '192.168.180.50',
        ),
        'port' => 6379,
        'connect_timeout' => '5',
        'try' =>3,
        'redisExpire' => 3600,
        'enableMuti' => false,
    ),
);


