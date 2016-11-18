<?php

if( ! defined('BASEPATH') ) exit('禁止直接浏览 tuan_controller');

class tuan_controller extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _remap($class, $args = array()){
		$ext = array('.htm', '.html');
		$class = strtolower($class);
		foreach($ext as $item){
			$class = str_replace($item, '', $class);
		}
		$this->route($class, $args, 'tuan');
	}
	
}

?>