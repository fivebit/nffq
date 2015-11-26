<?php
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/
/**
 * 
 */
class MysqlManagers {
	protected $_mysqli = NULL;
	protected $_isConnected = false;
    protected $config = array();
	
	public function __construct($config = array()) {
        $this->_mysqli = mysqli_init();
        $this->config = $config;
    }
    public function __destruct() {
    	if($this->_isConnected) {
       		$this->_mysqli->close();
    	}
    }

	/**
	 * 
	 * 开启一个事务
	 */
	public function startTransaction() {
		if(false === $this->query('START TRANSACTION')) {
			return false;
		}
		return true;
	}

	/**
	 * 
	 * 结束一个事务
	 * @param boolean $commit 是否提交事务
	 */
	public function endTransaction($commit = false) {
		$sql = 'ROLLBACK';
		if($commit) {
			$sql = 'COMMIT';
		}
		if(false === $this->query($sql)) {
            return false;
        }
        return true;	
	}

	public function getError() {
		if(!$this->_isConnected) {
            return false;
        }
		return array(
			'errno' => $this->_mysqli->errno,
			'errmsg' => $this->_mysqli->error,
		);	
	}
	
	/**
	 * 
	 * 获得上次数据库访问影响到的行数
	 */
	public function getAffectRows() {
		if(!$this->_isConnected) {
            return false;
        }
		return $this->_mysqli->affected_rows;	
	}

	/**
	 * 
	 * 执行一条SQL语句
	 * @param string $sql 要执行的SQL语句
	 */
	public function query($sql) {
		$start = intval(microtime(true) * 1000);
		if(!$this->_isConnected) {
			return false;
		}
        $result = $this->_mysqli->query($sql);
		$end = intval(microtime(true) * 1000);
		$cost = $end - $start;
        if(false === $result &&  $this->_mysqli->errno ) {
            return false;
        }

        if ( true === $result ) {
            return $result;
        }

        $ret = array();
        while($row = $result->fetch_assoc()) {
            array_push($ret, $row);
        }
        $result->close();
        return $ret;	
	}
	

	/**
	 * 
	 * 连接到数据库
	 */
	public function fetchMysqlHandler($dbname=null ) {
		if($this->_isConnected) {
			$this->_mysqli->close();
       		$this->_isConnected = false;
       		$this->_mysqli = mysqli_init();
    	}
		if(!is_null($dbname)) {
			if(!isset($this->config[$dbname])) {
				return false;
			}
			$arrMysqlServer = $this->config[$dbname];
		} else {
			$arrMysqlServer = $this->config;
		}
		
		$totalNum = count($arrMysqlServer);
		$index = mt_rand(0, $totalNum-1);
		for($i = 0; $i < $totalNum; $i++) {
			$mysqlServer = $arrMysqlServer[$index];
			if(!isset($mysqlServer['host']) || !isset($mysqlServer['username']) || 
				!isset($mysqlServer['password']) || !isset($mysqlServer['database']) || 
				!isset($mysqlServer['port'])) {
				return false;
			}
			if(false === $this->_mysqli->real_connect( $mysqlServer['host'], 
													$mysqlServer['username'], $mysqlServer['password'], 
													$mysqlServer['database'], $mysqlServer['port'], 
													NULL, 0)) {
				$index = (++$index % $totalNum);
				continue;
			}
			$this->_mysqli->set_charset($mysqlServer['charset']);
			$this->_isConnected = true;
			break;
		}
		if(false === $this->_isConnected) {
			return false;
		}
		return true;
	}
	
	/**
	 * 
	 * 转义一个字符串
	 * @param string $strVal 要转义的字符串
	 */
	public function escapeString($strVal) {
		if(!$this->_isConnected) {
			return false;
		}
		return $this->_mysqli->escape_string($strVal);
	}
	
	/**
	 * 
	 * 批量转义字符串
	 * @param array $arrFields 要转义的数组
	 * @param array $arrStrFields 哪些KEY是字符串类型的
	 */
	public static function escapeStrings(&$arrFields, $arrStrFields = null) {
		$db = new MysqlManager();
        if(false === $db->fetchMysqlHandler(null, 0)) {   
			return false;
        }
		foreach($arrFields as $k => &$v) {
			if(is_null($arrStrFields)) {
				(!is_null($v)) && $v = "'" . $db->escapeString($v) . "'";
				continue;
			}
			in_array($k, $arrStrFields) && (!is_null($v)) && $v = "'" . $db->escapeString($v) . "'";
		}
		return true;
	}
}
?>
