<?php

// 后台管理控制器类
class admin_controller extends MY_Controller {
	
	function __construct(){
		parent::__construct();
		//$this->load->model('admin/admin_model_login');
		//if( $this->admin_model_login->check_login() == false ){
		//	echo('登录超时，<a href="/shzh_manage/login.asp">请重新登录</a>');
		//	exit();
		//}
	}
	/*
	function _remap($class, $params = array()){
		
		$class_name = 'admin_' . $class;
		$mthod_name = $params[0];
		array_shift($params);
		
		// 加载文件
		require 'admin_base.php';
		require $class_name . '.php';
		
		// 调用方法
		$object = new $class_name;
		$object->admin_username = $this->admin_model_login->get_admin_username();
		$object->$mthod_name($params);
	}*/
	
	// 支持子目录的路由
	public function _remap($class, $params = array()){

		// admin 控制器目录
		$prefix  = 'admin_';
		$dictionary = rtrim(dirname(__FILE__), '\\') . '\\';
		$base_class_name = $prefix . 'base';
		
		if( count( $params ) == 0 )
		{
			$original_method = 'home';
		}
		else
		{
			$original_method = $params[0];
		}
		
		if( is_dir($dictionary) ){
			if( file_exists($dictionary . $base_class_name . '.php') ){
				include($dictionary . $base_class_name . '.php');
				//echo($dictionary . $base_class_name . '.php');
			} else {
				exit('base class file is not found!');
			}
		}

		
		
		$dic_file_exists = true;
		$object = NULL;
		
		// 如果有和类名相同的目录存在，那就先查询目录中是否有想要的类
		if( is_dir($dictionary . strtolower($class) . '\\') ){
			$original_method = array_shift($params);
			$class_name = $prefix . strtolower($class) . '_' . $original_method;

			if( file_exists( $dictionary . strtolower($class) . '\\' . $class_name . '.php' ) ){
				include($dictionary . strtolower($class) . '\\' . $class_name . '.php');

				$object = new $class_name();
				//$object->admin_username = $this->admin_model_login->get_admin_username();
				$method = array_shift($params);

				if( ! method_exists($object, $method) ){
					exit('method is not found, '. $class_name .'->'. $method .'();');
				}

				if( count($params) == 0 ){
					$object->$method();
				} else {
					$object->$method($params);
				}

			} else {
				$dic_file_exists = false;
			}
		} else {
			$dic_file_exists = false;
		}

		// 如果子目录不存在或者是子目录中找不到对应的类文件
		// 就加载跟目录中相同名字的文件
		if( $dic_file_exists == false ){
			$class_name = $prefix . strtolower($class);
			if( file_exists( $dictionary . $class_name . '.php' ) ){
				include($dictionary . $class_name . '.php');
				$object = new $class_name;
				
				//$object->admin_username = $this->admin_model_login->get_admin_username();
				
				if( count($params) == 0 ){
					$object->$original_method();
				} else {
					$object->$original_method($params);
				}
			} else {
				exit('class file is not found!');
			}
		}
		
	}
	
}

?>