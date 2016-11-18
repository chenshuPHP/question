<?php if(!defined('BASEPATH')) exit('~/controllers/member/member_controller.php');

class member_controller extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _remap($label, $params = array()){
		
		
		$prefix = 'member_';
		
		$path_info = pathinfo(__FILE__);
		$dir = rtrim($path_info['dirname'], '\\') . '\\';		// 当前文件夹绝对路径
		
		$member_base_dir = $dir;
		
		if( is_dir($dir . $label) ){
			
			$dir = $dir . $label . '\\';
			
			$class_suffix = array_shift($params);
			if( empty($class_suffix) ){
				exit('controller name is undefined!');
			}
			
			include( $member_base_dir . 'member_base.php' );
			
			$base_class_name = $prefix . $label . '_' . 'base';
			$class_name = $prefix . $label . '_' . $class_suffix;
			
		} else {
			$base_class_name = 'member_base';
			$class_name = 'member_' . $label;
		}
		
		if( file_exists( $dir . $base_class_name . '.php' ) ){
			include($dir . $base_class_name . '.php');
		} else {
			exit('class file is not found! file:['. $base_class_name .']');
		}
		if( file_exists( $dir . $class_name . '.php' ) ){
			include($dir . $class_name . '.php');
		} else {
			exit('class file is not found! file:['. $class_name .']');
		}
		
		$object = new $class_name();
		$method = array_shift($params);
		if( empty($method) ){
			$object->index();
		} else {
			$object->$method($params);
		}
		
		/*
		$class_name = 'member_' . $class;
		$method = array_shift($params);
		
		if( empty($method) ) $method = 'index';
		
		$class_base_file = 'member_base.php';
		$class_file = $class_name . '.php';
		
		if( !file_exists($dir . $class_base_file) ){
			exit('base class file is not found!');
		} else {
			include($class_base_file);
		}
		if( !file_exists($dir . $class_file) ){
			exit('class file is not found! file:['. $class_file .']');
		} else {
			include($class_file);
		}
		$object = new $class_name();
		$object->$method( $params );
		*/
	}
	
}

?>