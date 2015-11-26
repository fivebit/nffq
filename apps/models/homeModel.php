<?php
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/
 
/**
 * @file index.php
 * @author fivebit(@fivebit.com)
 * @date 2015/11/26 10:21:09
 *  
 **/
class homeModel extends CModel{
    public function showmsg(){
        return $this->index->index();
    }
}






/* vim: set ts=4 sw=4 sts=4 tw=100 @qiong*/
?>
