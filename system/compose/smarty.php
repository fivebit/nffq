<?php
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/
require(dirname(__FILE__) . '/smarty/Smarty.class.php');
class nffq_smarty{
	private $smarty = null;
	private $viewData = array ();
	private $gpc = false;
	function __construct($config=array()) {
		$this->gpc = get_magic_quotes_gpc ();
		$this->smarty = new Smarty ( );
        $dir = isset($config['template_dir'])?$config['template_dir']:BASE_ROOT."apps/views";
		$this->smarty->template_dir = $dir;
		$this->smarty->force_compile = true;
		$this->smarty->compile_dir = $dir. '/templates_c';
		$this->smarty->cache_dir = $dir. '/cache';
		$this->smarty->config_dir = isset($this->smarty->template_dir[0])?$this->smarty->template_dir[0]:$this->smarty->template_dir . '/conf';
		$this->smarty->caching = false;
		$this->smarty->left_delimiter = '<%';
		$this->smarty->right_delimiter = '%>';
	}
	
	function display($template,$data,$display=true) {
		$this->smarty->assign ( 'tpl_data', $data);
		$template = trim($template);
		if(false === strrpos($template, '.tpl')){
			$template .= '.tpl';
		}
        if($display !== true){
		    return $this->smarty->fetch ( $template);
        }
		$this->smarty->display ( $template );
	}
	
	function setArray(Array $arr) {
		if ($this->viewData) {
			$this->viewData = array_merge ( $this->viewData, $arr );
		} else {
			$this->viewData = $arr;
		}
	}
}
?>
