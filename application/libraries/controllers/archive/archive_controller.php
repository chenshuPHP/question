<?php

if( ! defined("BASEPATH") ) exit("prohibit~~, controllers/archive/archive_controller.php");

class archive_controller extends MY_Controller {
	
	private $prefix = "archive_";
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _remap($class_name = "index", $args = array()){

		$class_name = $this->prefix . strtolower($class_name);
		$method = "home";
		if( count($args) > 0 ) $method = strtolower( array_shift($args) );

		$base_class_file = $this->prefix . "base.php";
		$class_file = $class_name . ".php";
		
		$dirname = dirname(__FILE__) . '\\';
		
		
		if( file_exists($dirname . $base_class_file) && file_exists($dirname . $class_file) ){
			include($base_class_file);
			include($class_file);
		} else {
			//echo( file_exists( dirname(__FILE__) . '\\' . $base_class_file ) );
			show_error('file not found! ' . $class_file, 404);
		}
		
		if( ! class_exists($class_name) ){
			show_error('class not found!', 404);
		}
		
		$class = new $class_name();
		
		
		if( method_exists($class, '_my_remap') ){
			if( count($args) > 0 ){
				$class->_my_remap($method, $args);
			} else {
				$class->_my_remap($method);
			}
		} else {
			
			if( count($args) > 0 ){
				$class->$method($args);
			} else {
				$class->$method();
			}
			
		}

	}


}


?>