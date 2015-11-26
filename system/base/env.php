<?php
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/

//全局变量
class MQ {
    public $_q = array();
    public function __get($name){
        if(isset($this->_q[$name])){
            return $this->_q[$name];
        }
        return null;
    }
    public function __set($name,$value){
        return $this->_q[$name] = $value;
    }
}
/* vim: set ts=4 sw=4 sts=4 tw=100 @for qiong*/
?>
