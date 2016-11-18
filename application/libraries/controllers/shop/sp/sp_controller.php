<?php

// 装修公司店铺 控制器
class sp_controller extends MY_controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function controller($args = array()){
		
		$username = $args[0];
		
		$parameters = implode('-', $args[1]);
		$parameters = explode('-', $parameters);
		//$parameters = explode('-', strtolower( $args[1][0] ));	// " - " 分隔方法名和参数的模式
		
		$dir = rtrim(dirname(__FILE__), '\\') . '\\';
		$prefix = 'sp_';
		$class_name = $prefix . $parameters[0];
		
		if( ! file_exists( $dir . $prefix . 'base.php' ) || ! file_exists( $dir . $class_name .'.php' )  ){
			show_error('404 file not found', 404);
			exit();
		}
		
		include($dir . $prefix . 'base.php');
		include($dir . $class_name .'.php');
		
		$class = new $class_name();
		
		if( isset( $parameters[1] ) && preg_match('/^[a-zA-Z_]+$/', $parameters[1]) == true ){
			$method = strtolower( $parameters[1] );
			array_shift($parameters);	// 移除 class 部分
			array_shift($parameters);	// 移除 method 部分
		} else {
			$method = 'home';
			array_shift($parameters);
		}
		
		$class->initialize($username);
		
		if( count($parameters) > 0 ){
			$class->$method($parameters);
		} else {
			$class->$method();
		}
		
	}
	
}



?>