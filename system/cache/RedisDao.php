<?php 
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/
/**
 * RedisDao
 * RedisDao提供redis基础管理逻辑
 */
class RedisDao{
	private $_redisObj = null;
    private $config = array();
    private $isinit = false;
    public function __construct($config=null){
        $this->config = $config;
    }
    public function init($config=null){
        if($config){
            $this->config = $config;
        }
        $this->getConnection(true);
    }
	/**
	 * 获取一个redis连接
	 * @param boolean 是否强制重置连接
	 * @throws Exception
	 */
	public function getConnection($force = false){
		if(false === !!$force && !is_null($this->_redisObj)){
			return $this->_redisObj;
		}
		if(!is_null($this->_redisObj)){
			unset($this->_redisObj);
			$this->_redisObj = null;
		}
		$servers  = $this->config['server'];
		$port = $this->config['port'];
		shuffle($servers);
		$this->_redisObj = new Redis();
		foreach ($servers as $server){
    		$conRet = $this->_redisObj->connect($server, $port, $this->config['connect_timeout']);
			if(false === $conRet){
				unset($this->_redisObj);
				$this->_redisObj = new Redis();
			}else{
				break;
			}
		}
		if(is_null($this->_redisObj)){
			throw new Exception('redis connect to redis all failed failed.');
		}
        $this->isinit = true;
		return $this->_redisObj;
	}
	
	/**
	 * 获取N个redis KEY的信息
	 * @param array $arrKeys 要获取哪些KEY
	 * @throws Exception
	 */
	public function mgetEx($arrKeys){
		if(empty($arrKeys)){
			return array();
		}
		if(is_string($arrKeys)){
            $arrKeys = array($arrKeys);
        }else{
            return array();
        }
		$retry = 0;
		$ret = false;
		do{	
			$this->getConnection($retry !== 0);
			try{
				$ret = $this->_redisObj->mget($arrKeys);
				break;
			}catch(Exception $ex){
				sleep(1);
				$ret = false;
			}
		}while($retry++ < $this->config['try']);
		if(false === $ret){
			throw new Exception('query redis to get all failed.');
		}
		if(count($arrKeys) !== count($ret)){
			throw new Exception('redis return count is not equal to arr key [ return: %s, key: %s ].', count($ret), count($arrKeys));
		}
		$total = count($arrKeys);
		$finRet = array();
		for($i = 0; $i < $total; $i++){
			$finRet[$arrKeys[$i]] = $ret[$i];
		}
		return $finRet;
	}
	
	/**
	 * 批量删除redis中key的信息
	 * 
	 * @param array $keys 要删除哪些key的信息
	 * @throws Exception
	 */
	public function del($keys){
		foreach ($keys as $key){
			$retry = 0;
			$ret = false;
			do{
				$this->getConnection($retry !== 0);
				try{
					$ret = $this->_redisObj->del($key);
					break;
				}catch(Exception $ex){
					sleep(1);
					$ret = false;
				}
	        }while($retry++ < $this->config['try']);
			if(false === $ret){
				throw new Exception('query redis to del keys failed.');
			}
		}
	}
	
    /**
     * 设置key的过期时间
     */
    public function setExpire($key,$second=86400){
		$retry = 0;
		$ret = false;
		do{	
            $this->getConnection($retry !== 0);
            try{
                $ret = $this->_redisObj->expire($key, $second);
                break;
            }catch(Exception $ex){
				sleep(1);
            }
	    }while($retry++ < $this->config['try']);
		if(false === $ret){
			throw new Exception('query redis to get all failed.');
		}
        return $ret;
    }
    /**
     * 原子增加一个
     */
    public function setIncr($key){
		$retry = 0;
		$ret = false;
		do{	
            $this->getConnection($retry !== 0);
            try{
                $ret = $this->_redisObj->incr($key);
                break;
            }catch(Exception $ex){
				sleep(1);
            }
	    }while($retry++ < $this->config['try']);
		if(false === $ret){
			throw new Exception('query redis to get all failed.');
		}
        return $ret;
    }
	
    public function set($key,$value,$time){
        if(!$this->isinit){
            return;
        }
        $retry = 0;
        $ret = false;
        do{
            try{
                $ret = $this->_redisObj->setex($key, $time, $value);
                break;
            }catch(Exception $ex){
                sleep(1);
                $ret = false;
            }
        }while($retry++ < $this->config['try']);
        if(false === $ret){
            throw new Exception('query redis to save all failed.');
        }
    }
	/**
	 * 批量设置key的信息
	 * 
	 * @param array $arrValues 要设置的key value对集合
	 * @throws Exception
	 */
	public function msetEx($arrValues){
		if(!is_array($arrValues)){
			throw new Exception('internal invalid $arrValues was received, no array [ keys: %s ].', $arrValues);
		}
		if(!isset($this->config['enableMuti']) || false === $this->config['enableMuti']){
			foreach ($arrValues as $key => $value){
				$retry = 0;
				$ret = false;
				do{
					$this->getConnection($retry !== 0);
					try{
						$ret = $this->_redisObj->setex($key, $this->config['redisExpire'], $value);
						break;
					}catch(Exception $ex){
						sleep(1);
						$ret = false;
					}
				}while($retry++ < $this->config['try']);
				if(false === $ret){
					throw new Exception('query redis to save all failed.');
				}
			}
		}else{
			$retry = 0;
			$ret = false;
			do{
				$this->getConnection($retry !== 0);
				try{
					$ret = $this->_redisObj->mset($arrValues);
					break;
				}catch (Exception $ex){
					sleep(1);
					$ret = false;
				}
			}while($retry++ < $this->config['try']);
			if(false === $ret){
				throw new Exception('query redis to save all info failed.');
			}
		}
	}
}
?>
