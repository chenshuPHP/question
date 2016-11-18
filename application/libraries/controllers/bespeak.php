<?php

class bespeak extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _remap($class, $params = array()){
		
		$prefix = 'bespeak_';
		$directory = dirname(__FILE__);
		$directory = rtrim($directory, '\\') . '\\bespeak\\';
		$base_class_name = $prefix . 'base';
		$base_class_file = $directory . $base_class_name . '.php';
		$class_name = $prefix . strtolower($class);
		$class_file = $directory . $class_name . '.php';
		
		include($base_class_file);
		
		if( file_exists($class_file) ){
			include($class_file);
		} else {
			exit('class file is not found.');
		}
		
		$object = new $class_name();
		if( count($params) == 0 ){
			$method_name = 'home';
		} else {
			$method_name = strtolower(array_shift($params));
		}
		
		if( count($params) == 0 ){
			$object->$method_name();
		} else {
			$object->$method_name($params); 
		}
		
		exit();
		
	}
	
}

?>