<?php

// 注册
// 2015-03-12
class register_controller extends MY_Controller {

	public function __construct(){
		parent::__construct();
	}

	public function _remap($class, $args = array()){
		$this->route($class, $args, 'register');
	}

}

?>