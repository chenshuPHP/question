<?php

// 资讯中转站
class article_controller extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	
	public function _remap($class, $args = array()){
		
		$prefix = 'article_';
		$directroy = rtrim(dirname(__FILE__), '\\') . '\\';
		$base_class = $prefix . 'base';
		
		// 检测类名称中是否含有 -
		$class = strtolower($class);
		if( strpos($class, '-') != false ){
			$temp = explode('-', $class);
			$class = array_shift($temp);
			array_unshift($temp, 'home');
			$args[0] = implode('-', $temp);
		}
		
		$class_name = $prefix . $class;
		
		$method = 'home';
		
		if( file_exists($directroy . $base_class . '.php') ){
			include($directroy . $base_class . '.php');
		} else {
			show_error("没有找到基础类文件", 404);
		}
		
		if( file_exists( $directroy . $class_name . '.php' ) ){
			include($directroy . $class_name . '.php');
		} else {
			show_error("没有找到类文件{ " . $class_name . " }", 404);
		}
		
		$object = new $class_name();
		
		if( count($args) > 0 ){
			$args = explode('-', $args[0]);
			$method = strtolower( array_shift($args) );
		}
		
		if( ! method_exists($object, $method) ){
			show_error("没有找到处理函数{". $method ."}", 404);
		}
		
		if( count($args) > 0 ){
			$object->$method($args);
		} else {
			$object->$method();
		}
		
		exit();
	}
}

?>