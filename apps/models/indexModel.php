<?php
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/
 
/**
 * @file indexModel.php
 * @author fivebit(@fivebit.com)
 * @date 2015/11/26 10:59:32
 *  
 **/
class indexModel extends CModel{
    public function __construct(){
        parent::__construct();
        $this->setTable("dev_app_info");
        $this->setDb(getMQ('db'));
    }
    public function index(){
        $rows = $this->get("*",array("app_id"=>3),null,1);
        $rows = getMQ('db')->query("select * from dev_app_info limit 1");
        return "new framework for qiong";

    }
}






/* vim: set ts=4 sw=4 sts=4 tw=100 @qiong*/
?>
