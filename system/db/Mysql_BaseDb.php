<?php
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/
require_once (dirname(__FILE__) . '/MysqlManagers.php');
class Mysql_BaseDb {
	private static $_dbObj = null;
	private $_inTrans = false;
    protected $isinit = false;
    protected $config = array();
    public function init($config){
        $throw = false;
        if($this->isinit == true && $this->_dbObj !== NULL){
            return;
        }
        $this->config = $config;
        $st = $this->getConnection($throw,true);
        if($st === false){
            return false;
        }
        $this->isinit = true;
    }
	public function getConnection($throw = true, $force = false) {
		if(true === !!$force){
			$this->freeConnection();
		}
		if(!is_null(self::$_dbObj)){
			return self::$_dbObj;
		}
		self::$_dbObj = new MysqlManagers($this->config);
		if(false === self::$_dbObj->fetchMysqlHandler(null)) {
			$errInfo = self::$_dbObj->getError();
			$this->freeConnection();
			if(true === $throw){
				throw new Exception('database ' . __FUNCTION__ .  " failed, fetch mysql hander failed [mysql error: " . $errInfo['errmsg'] . ' ].');
			}else{
                return false;
			}
		}	
	}

	public function freeConnection($commitTransaction = false){
		if(!is_null(self::$_dbObj)){
			if(true === $this->_inTrans){
				self::$_dbObj->endTransaction($commitTransaction);
				$this->_inTrans = false;
			}
			if(isset(self::$_obj) && !is_null(self::$_obj)){
				unset(self::$_dbObj);
			}
			self::$_dbObj = null;
		}
	}
	
	public function startTransaction($commitLast = false, $throw = true){
		$this->getConnection($throw);
		if(true === $this->_inTrans){
			self::$_dbObj->endTransaction($commitLast);
			$this->_inTrans = false;
		}
		$ret = self::$_dbObj->startTransaction();
		if(false === $ret){
			if(true === $throw){
				throw new Exception('database ' . __FUNCTION__ . ' failed, start transaction failed [ commit last: ' . $commitLast . ' ].');
			}else if(!empty($throw)){
				throw new Exception('database ' . $throw . ' ' . __FUNCTION__ . ' failed, start transaction failed [ commit last: ' . $commitLast . ' ].', 1);
			}
		}
		if(true === $ret){
			$this->_inTrans = true;
		}
		return $ret;
	}
	
	public function endTransaction($commit = false, $throw = true){
		$this->getConnection($throw);
		$ret = true;
		if(true === $this->_inTrans){
			$ret = self::$_dbObj->endTransaction($commit);
			if(false === $ret){
				if(true === $throw){
					throw new Exception('database ' . __FUNCTION__ . ' failed, end transaction failed [ commit: ' . $commit . ' ].');
				}else if(!empty($throw)){
					throw new Exception('database ' . $throw . ' ' . __FUNCTION__ . ' failed, end transaction failed [ commit: ' . $commit . ' ].', 1);
				}
			}
		}
		return $ret;
	}

	public function query($sql, $throw = true) {
        $ret = false;
        $retry = 0;
		$this->getConnection($throw, false);
        while($retry++ < $this->config[0]['dbRetry']){
            $ret = self::$_dbObj->query($sql);
            if(false !== $ret){ //成功，直接返回
                return $ret;
            }
            $errInfo = self::$_dbObj->getError();
            if(1062 === intval($errInfo['errno'])){ //执行成功，明确返回key冲突，直接返回
                if(true === $throw){
                    throw new Exception('duplicate ' . __FUNCTION__ . " failed, execute sql failed [ sql: $sql, mysql error: " . $errInfo['errmsg'] . ' ].');
				}
                return false;
            }
            sleep(1);
			$this->getConnection($throw, true);
        }
        if(true === $throw){
            throw new Exception('database ' . __FUNCTION__ . " failed, all retry failed [ sql: $sql ].");
		}
        return false;
    }
	
	public function checkTableExist($table){
		$result = $this->query("SHOW TABLES LIKE '$table'");
		return isset($result[0]) && !empty($result[0]);
	}
	
	public function getAffectRows($throw = true){
		$this->getConnection($throw);
		if(is_null(self::$_dbObj)){
			if(true === $throw){
				throw new Exception('database ' . __FUNCTION__ . ' failed, object has not been init yet');
			}
			return false;
		}
		$ret = self::$_dbObj->getAffectRows();
		if(false === $ret){
			$errInfo = self::$_dbObj->getError();
			if(true === $throw){
				throw new Exception('database ' . __FUNCTION__ . ' failed, get affect rows failed [ error info: ' . $errInfo['errmsg'] . ' ].');
			}
		}
		return $ret;
	}

	public function escapeString($strVal, $throw = true) {
		$this->getConnection($throw);
		$ret = self::$_dbObj->escapeString($strVal);
		if(false === $ret){
			$errInfo = self::$_dbObj->getError();
			if(true === $throw){
				throw new Exception('database ' . __FUNCTION__ . ' failed [ error info: ' . $errInfo['errmsg'] . ", str: $strVal ].");
			}
			return $ret;
		}
		return $ret;
	}
}
?>
