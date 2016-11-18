<?php

class album_controller extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _remap($class, $args = array()){
		
		//var_dump($class);
		//var_dump($args);
		//exit();
		
		$separator = '-';
		$prefix = 'album_';
		$directory = rtrim(dirname(__FILE__), '\\') . '\\2015\\';
		
		
		$base_class = $prefix . 'base';
		$base_class_file = $base_class . '.php';
		
		if( file_exists($directory . $base_class_file) ){
			include($directory . $base_class_file);
		} else {
			exit('base class file not exist');
		}
		
		$class_name = '';
		$method_name = '';
		$params = array();
		
		if( count($args) == 0 ){
			$temp = $class;
			if( strpos($temp, $separator) == false ){
				$class_name = strtolower($temp);
			} else {
				$temp = explode($separator, $temp);
				$class_name = array_shift($temp);
				if( count($temp) != 0 ) $params = $temp;
			}
			$method_name = 'home';
		} else {
			$class_name = strtolower($class);
			$temp = $args[0];
			if( strpos($temp, $separator) == false ){
				$method_name = strtolower($temp);
			} else {
				$temp = explode($separator, $temp);
				$method_name = array_shift($temp);
				if( count($temp) != 0 ) $params = $temp;
			}
		}
		$class_name = $prefix . $class_name;
		$class_file = $class_name . '.php';
		
		if( file_exists( $directory . $class_file ) ){
			include($directory . $class_file);
		} else {
			//exit('class file '. $directory . $class_file .' not exist');
			show_404();
			exit();
		}
		
		$object = new $class_name();
		$object->$method_name($params);
		
	}
	
}


?>