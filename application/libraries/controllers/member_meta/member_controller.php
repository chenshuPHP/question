<?php

// 建材商会员中心
class member_controller extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _remap($class, $params = array()){
		
		$prefix = 'member_';
		$base_class_name = $prefix . 'base';
		$class_name = $prefix . strtolower($class);
		
		$directory = dirname(__FILE__) . '\\';
		
		if( file_exists($directory . $base_class_name . '.php') ){
			include($directory . $base_class_name . '.php');
		} else {
			exit( 'file ' . $directory . $base_class_name . '.php is not found' );
		}
		
		if( file_exists($directory . $class_name . '.php') ){
			include($directory . $class_name . '.php');
		} else {
			exit( 'file ' . $directory . $class_name . '.php is not found' );
		}
		
		$object = new $class_name();
		if( count($params) == 0 ) $params = array('home');
		$member = array_shift($params);
		if( count($params) == 0 ){
			$object->$member();
		} else {
			$object->$member($params);
		}
		
	}
	
	
}


?>