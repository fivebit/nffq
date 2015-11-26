<?php
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/
class nffq_plugin{
	public $enabled = FALSE;
	protected $hooks =	array();
	protected $_objects = array();
	protected $_in_progress = FALSE;

	public function __construct() {
		if (file_exists(APP_ROOT.'config/hooks.php')) {
			include(APP_ROOT.'config/hooks.php');
		}
		if ( ! isset($hook) OR ! is_array($hook)) {
		}
		$this->hooks = array();
		$this->enabled = TRUE;
	}
	public function call_hook($which ,$arg= '') {
		if ( ! $this->enabled ||  ! isset($this->hooks[$which])) {
			return FALSE;
		}
		if (is_array($this->hooks[$which]) && !isset($this->hooks[$which]['function'])) {
            ksort($this->hooks[$which]);
            reset($this->hooks[$which]);
            do{
                foreach ((array)current($this->hooks[$which]) as $val) {
                    $this->_run_hook($val,$arg);
                }
            }while(next($this->hooks[$which]) !== false);
		} else {
			$this->_run_hook($this->hooks[$which],$arg);
		}
		return TRUE;
	}

	/**
	 * Run Hook
	 * @param	array	$data	Hook details
	 * @return	bool	TRUE on success or FALSE on failure
	 */
	protected function _run_hook($data,$arg) {
		if (is_callable($data)) { // Closures/lambda functions and array($object, 'method') callables
			is_array($data) ? $data[0]->{$data[1]}() : $data();
			return TRUE;
		} elseif ( ! is_array($data)) {
			return FALSE;
		}

		// If the script being called happens to have the same
		// hook call within it a loop can happen
		if ($this->_in_progress === TRUE) {
			return;
		}
		if (  isset($data['filepath'], $data['filename'])) {
		    $filepath = APP_ROOT.$data['filepath'].'/'.$data['filename'];
            if ( ! file_exists($filepath)) {
                return FALSE;
            }
        }
		// Determine and class and/or function names
		$class		= empty($data['class']) ? FALSE : $data['class'];
		$function	= empty($data['function']) ? FALSE : $data['function'];
		$params		= isset($data['params']) ? $data['params'] : '';
		if (empty($function)) {
			return FALSE;
		}
		$this->_in_progress = TRUE;         // Set the _in_progress flag
		if ($class !== FALSE) {             // Call the requested class and/or function
			if (isset($this->_objects[$class])) { // The object is stored?
				if (method_exists($this->_objects[$class], $function)) {
					$this->_objects[$class]->$function($params);
				} else {
					return $this->_in_progress = FALSE;
				}
			} else {
				class_exists($class, FALSE) OR require_once($filepath);
				if ( ! class_exists($class, FALSE) OR ! method_exists($class, $function)) {
					return $this->_in_progress = FALSE;
				}

				$this->_objects[$class] = new $class(); // Store the object and execute the method
				$this->_objects[$class]->$function($params);
			}
		} else {
            if(is_string($function)){
                function_exists($function) OR require_once($filepath);
                if ( ! function_exists($function)) {
                    return $this->_in_progress = FALSE;
                }
                $function($params);
            }else{
                $args = array();
                if ( is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) ){
                    $args[]= & $arg[0];
                }else{
                    $args[] = $arg;
                }
                for($a=2,$num = func_num_args();$a<$num;$a++){
                    $args[] = func_get_arg($a);
                }
                call_user_func_array($function,array_slice($args,0,(int)$data['accepted_args']));
            }
		}
		$this->_in_progress = FALSE;
		return TRUE;
	}
    public function add_action($tag,$function_to_add,$priorty=10,$accepted_args=1){
        $idx = $this->_nffq_action_build_unique_id($tag,$function_to_add,$priorty);
        $this->hooks[$tag][$priorty][$idx] = array("function" => $function_to_add,"accepted_args" => $accepted_args);
    }
    public function _nffq_action_build_unique_id($tag, $function, $priority) {
        if ( is_string($function) ){
            return $function;
        }
        if ( is_object($function) ) {        // Closures are currently implemented as objects
            $function = array( $function, '' );
        } else {
            $function = (array) $function;
        }
        if (is_object($function[0]) ) {
            if ( function_exists('spl_object_hash') ) {
                return spl_object_hash($function[0]) . $function[1];
            } else {
                $obj_idx = get_class($function[0]).$function[1];
                return $obj_idx;
            }
        } elseif ( is_string( $function[0] ) ) {        // Static Calling
            return $function[0] . '::' . $function[1];
        }
    }
}
function add_action($tag,$function_to_add,$priorty=10,$accepted_args=1){
    global $hook;
    if(!isset($hook)){
        $hook = new nffq_plugin();
    }
    $hook->add_action($tag,$function_to_add,$priorty,$accepted_args);
}
function do_action($tag,$params = array()){
    global $hook;
    if(!isset($hook)){
        $hook = new nffq_plugin();
    }
    $hook->call_hook($tag,$params);
}
