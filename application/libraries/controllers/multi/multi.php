<?php

// 多元化服务

class multi extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _remap($class, $args = array()){
		$class = strtolower($class);
		$prefix = 'multi_';
		$base_class_name = $prefix . 'base';
		$class_name = $prefix . $class;
		$path = rtrim(dirname(__FILE__), '\\') . '\\';
		$base_class_file = $path . $base_class_name . '.php';
		$class_file = $path . $class_name . '.php';
		if( file_exists( $base_class_file ) ){
			include($base_class_file);
		} else {
			show_error('找不到基类文件 { classname:'. $base_class_name .' } ', 404);
		}
		if( file_exists($class_file) ){
			include($class_file);
		} else {
			show_error('找不到类文件 { classname:'. $class_name .' } ', 404);
		}
		
		$object = new $class_name();
		if( count($args) == 0 ){
			$method_name = 'home';
		} else {
			$method_name = array_shift($args);
		}
		
		if( method_exists($object, '__remap') ){
			//$original_method_name = $method_name;
			array_unshift($args, $method_name);
			$method_name = '__remap';
		}
		
		if( count($args) > 0 ){
			$object->$method_name($args);
		} else {
			$object->$method_name();
		}
	}
	
	
}
?>