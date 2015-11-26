<?php
/***************************************************************************
 * NFFQ for qiong
 * This framework is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation
 **************************************************************************/
abstract class CController {
	var $request = null;
	var $response = null;
    var $render = 'ajax';        //php/ajax
    var $template = '';
	
    public function init(){
    }
    public function setRender($render){
        $this->render = $render;
    }
    public function setTemplate($tpl){
        $this->template = $tpl;
    }
    public function _output(){
        load_file('render','base');
        nffq_render::renderResult($this->response,$this->render,$this->template);
    }
    public function __get($name){
        $getter = 'get'.$name;
        if(method_exists($this,$getter)){
            return $this->$getter();
        }else{
            $name .= "Model";
            $obj = new $name();
            return $obj;
        }
    }
}
abstract class CModel extends CController{
    var $_db = null;
    var $_table = null;
    public function __construct(){
        $this->setDb(getMQ('db'));
    }
    public function setTable($table){
        $this->_table = $table;
    }
    public function setDb($db){
        $this->_db = $db;
    }

    public function get($field = ' * ',$where = null,$order = null,$limit = null,$join=null,$on = null){
        $sql = "select ";
        if(is_array($field)){
            $sql .= implode(',',$field);
        }else{
            $sql .= $field;
        }
        $sql .= ' from ' .$this->_table;
        if(!is_null($join)){
            $sql .= ' left join ' . $join;
            if(!is_null($on)){
                $sql .= ' on ' . $on;
            }
        }
        if(is_array($where)){
            if(!empty($where)){
                $sql .= ' where ';
                foreach($where as $k =>$v){
                    if(strpos($k,'!=') > 0){
                        $k = trim(str_replace("!=","",$k));
                        $sql .= '`'.$k . '` != \'' . $this->_db->escapeString($v) . '\' and ';
                    }else if(strpos($k,'>=') > 0){
                        $k = trim(str_replace(">=","",$k));
                        $sql .= '`'.$k . '` >= \'' . $this->_db->escapeString($v) . '\' and ';
                    }else if(strpos($k,'<=') > 0){
                        $k = trim(str_replace("<=","",$k));
                        $sql .= '`'.$k . '` <= \'' . $this->_db->escapeString($v) . '\' and ';
                    }else if( strpos( $k,'#in') > 0 ){
                        $k = trim(str_replace("#in","",$k));
                        if(is_array($v)){
                            $n = "";
                            foreach($v as $m){
                                $n .= '\''.$this->_db->escapeString($m).'\',';
                            }
                            $n = rtrim($n,',');
                            $sql .= '`'.$k . '` in( ' .$n. ') and ';
                        }else{
                            $sql .= '`'.$k . '` in( ' . $this->_db->escapeString($v) . ') and ';
                        }
                    }else{
                        $sql .= ' '.$k . ' = \'' . $this->_db->escapeString($v) . '\' and ';
                    }

                }
                $sql = rtrim($sql,'and ');
            }
        }

        if(!is_null($order)){
            $sql .= ' order by '.$order ." ";
        }

        if(!is_null($limit)){
            $sql .=  ' limit ' . $limit;
        }
        getMQ('logger')->notice($this->_table.' get sql:'.$sql);
        $list = $this->_db->query($sql);
        return $list==false?array():$list;
    }

    public function modify($data,$where,$limit = null){
        $sql = 'update ' . $this->_table;
        if(is_array($data)){
            if(!empty($data)){
                $sql .= ' set ';
                foreach($data as $k =>$v){
                    $sql .= $k . ' = \'' . $this->_db->escapeString($v) . '\' ,';
                }
                $sql = rtrim($sql,',');
            }
        }
        if(is_array($where)){
            if(!empty($where)){
                $sql .= ' where ';
                foreach($where as $k =>$v){
                    $sql .= $k . ' = \'' . $this->_db->escapeString($v) . '\' and ';
                }
                $sql = rtrim($sql,'and ');
            }
        }

        if(is_null($limit)){
            $sql .= ' limit 1';
        }
        getMQ('logger')->notice($this->_table.' update sql:'.$sql);
        return $this->_db->query($sql);
    }

    public function create($data){
        $sql = 'insert into ' . $this->_table;
        if(is_array($data)){
            if(!empty($data)){
                $sql .= ' (';
                foreach($data as $k =>$v){
                    $sql .= ' `'. $k . '` ,';
                }
                $sql = rtrim($sql,','). ') values ( ';
                foreach($data as $k =>$v){
                    $sql .= ' \'' . $this->_db->escapeString($v) . '\' ,';
                }
                $sql = rtrim($sql,',').' );';
            }
        }
        getMQ('logger')->notice($this->_table.' create sql:'.$sql);
        return $this->_db->query($sql);
    }
    /**
     * 获取最后插入的自增ID
     */
    public function getLastId(){
        $sql = 'SELECT LAST_INSERT_ID() AS id';
        $dbRet = $this->_db->query($sql);
        if(false === $dbRet || !isset($dbRet[0]['id']) || 0 == $dbRet[0]['id']){
            return false;
        }
        return $dbRet[0]['id'];
    }
    public function real_escape_string($str) {
        if (!is_string($str)) return $str;
        $len = strlen($str);
        if ($len==0) return $str;
        $res = "";
        for ($i=0; $i<$len; ++$i) { 
            $c = $str[$i];
            if ($c=="\r") $c = "\\r";
            if ($c=="\n") $c = "\\n";
            if ($c=="\x00") $c = "\\0";
            if ($c=="\x1a") $c = "\\Z";
            if ($c=="'" || $c=='"' || $c=='\\') $res.="\\";
            $res.= $c; 
        }
        return $res;
    }
}
?>
