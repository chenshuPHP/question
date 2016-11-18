<?php

class video_controller extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _remap($class, $params){
		
		if( preg_match('/^v\d+$/', $class) ){
			$temp = $class;
			$class = 'view';
			$params = array('home-' . str_replace('v', '', $temp));
		}
		
		$this->route($class, $params, 'video');
	}
	
	
}

?>