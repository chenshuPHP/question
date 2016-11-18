<?php

// 分站控制器
// sc => site_city
// kko4455@163.com 2015-04-13
class sc_controller extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function handler($args){
		
		$prefix = 'sc_';
		$base_class_name = $prefix . 'base';
		$class_name = $prefix . strtolower( $args['class'] );
		
		$directory = dirname(__FILE__) . '\\';
		
		if( ! file_exists( $base_file = $directory . $base_class_name . '.php' ) ){
			show_error('base class file not found', 404);
		} else {
			include($base_file);
		}
		
		// 判断是否为目录
		if( ! is_dir( $directory . $class_name . '\\' ) ){
			if( ! file_exists( $class_file = $directory . $class_name . '.php' ) ){
				show_error('class file not found', 404);
			} else {
				include($class_file);
			}
		} else {
			
			$class = strtolower( array_shift($args['params']) );
			$new_class_name = $class_name . '_' . $class;
			
			if( ! file_exists( $class_file = $directory . $class_name . '\\' . $new_class_name . '.php' ) ){
				show_error('class file not found 2', 404);
			} else {
				include($new_class_name);
				$class_name = $new_class_name;
			}
			
		}
		
		$object = new $class_name();
		
		if( method_exists($object, '_remap') ){
			$method_name = '_remap';
		} else {
			$method_name = array_shift($args['params']);
		}
		
		$object->init($args['sc']);
		
		if( count($args['params']) > 0 ){
			$object->$method_name($args['params']);
		} else {
			$object->$method_name();
		}
		
		
	}
}



















?>