<?php
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/
/**
 * auto loader
 */
set_include_path(NFFQ_ROOT. PATH_SEPARATOR . get_include_path());

function nffq_loader($className) {
    if (class_exists($className, false)) {
        return;
    }
    $className = str_replace('_', '/', $className);
    $include_pathes = explode(PATH_SEPARATOR, get_include_path());
    $sys_pathes = array(NFFQ_ROOT.'base',
        NFFQ_ROOT.'compose',
        );
    $app_pathes = array();
    if(!defined(APP_ROOT)){
        $app_pathes = array(
            APP_ROOT."models",
            APP_ROOT."controllers",
            APP_ROOT.'libs',
            );
    }
    $realClassFile = $className.".php";
    $add_path = '';
    /*
    if (substr($className, -10) === 'Controller') {
        $add_path = "controllers/";
        $realClassFile .= strtolower(substr($className, 0, -10)) . '.php';
    } elseif (substr($className, -6) === 'Module') {
        $realClassFile = "module.php";
    } else {
        $realClassFile = $className.".php";
    }
     */
    $got_it = false;
    $add_path?$include_pathes[] = $add_path:"";
    if($sys_pathes){
        $include_pathes = array_merge($include_pathes,$sys_pathes,$app_pathes);
    }
    foreach($include_pathes as $path){
        if (file_exists($path."/".$realClassFile)){
            $got_it = true;
            include $path."/".$realClassFile;
            break;
        }
    }
    if($got_it == false){

    }
}
function nffq_load_lib($lib){

}
function nffq_load_model($model){

}

spl_autoload_register('nffq_loader');
?>
