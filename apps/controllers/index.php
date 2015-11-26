<?php
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/
 
/**
 * @file index.php
 * @author fivebit(@fivebit.com)
 * @date 2015/11/24 14:56:59
 *  
 **/
class IndexController extends CController{

    public function indexAction(){
        $this->setRender("ajax");
        $msg = $this->home->showmsg();
        $this->response = $msg;
    }
    public function qiongAction(){
        $this->setRender("smarty");
        $this->setTemplate('index');
        $msg = $this->home->showmsg();
        $this->response = json_encode($msg);
    }
}
/* vim: set ts=4 sw=4 sts=4 tw=100 @qiong*/
?>
