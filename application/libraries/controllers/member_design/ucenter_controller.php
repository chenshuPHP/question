<?php

class ucenter_controller extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _remap($class, $args = array()){
		$this->current_file_path = __FILE__;
		$this->route($class, $args, 'uc');
	}
	
	
}

?>