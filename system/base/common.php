<?php
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/
 
/**
 * @file common.php
 * @author fivebit(@fivebit.com)
 * @date 2015/11/23 17:08:27
 *  
 **/
function load_class($class,$directory='base',$param=null){
    static $_classes = array();
    if(isset($_classes[$class])){
        return $_classes[$class];
    }
    load_file($class,$directory,$param);
    $names = array('nffq_'.$class,$class);
    foreach($names as $name){
        if (class_exists($name, FALSE) === TRUE) {
            $_classes[$class] = isset($param) ? new $name($param) : new $name();
            return $_classes[$class];
        }
    }
    return false;
}
function load_file($file,$directory='base',$param=null){
    static $_files = array();
    if (isset($_files[$file])) {
        require_once($_files[$file]);
        return ;
    }
    foreach (array(NFFQ_ROOT) as $path) {
        if (file_exists($path.$directory.'/'.$file.'.php')) {
            $_files[$file] = $path.$directory."/".$file.".php";
            break;
        }
    }
    require_once($path.$directory.'/'.$file.'.php');
}


load_file("loader",'base');
load_file('env','base');
load_file('plugin');
function getMQ($name,$default=null){
    if(isset($GLOBALS[$name])){
        return $GLOBALS[$name];
    }
    return $default;
}
function setMQ($key,$value){
    return $GLOBALS[$key] = $value;
}


/* vim: set ts=4 sw=4 sts=4 tw=100 @qiong*/
?>
