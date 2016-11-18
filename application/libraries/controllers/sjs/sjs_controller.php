<?php

// 设计师前端
// 2015-08-10
class sjs_controller extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('title', '设计天地');
		$this->tpl->assign('hide_kf', '1');
	}
	
	
	public function _remap($module, $params = array()){
		
		$module = strtolower($module);
		$dir = dirname(__FILE__) . '\\';
		
		// == BLOG ==
		if( $module == 'blog' ){
			
			// 因为域名的格式为, 所以 $params 最多只有2个元素, 第一个是 用户名，第二个是 请求字符串
			/*
				xxx/blog/username
				xxx/blog/username/art
				xxx/blog/username/art-view-10
				xxx/blog/username/art-list-5
			*/
			
			$prefix = 'blog_';
			
			$username = array_shift($params);
			
			if( count( $params ) != 0 ){
				$uri = $params[0];
			} else {
				$uri = 'home';
			}
			
			$uri = explode('-', $uri);
			$class_name = $prefix . array_shift( $uri );
			$base_class_name = $prefix . 'base';
			
			$base_file = $dir . 'blog\\'. $base_class_name .'.php';
			$class_file = $dir . 'blog\\'. $class_name .'.php';
			
			require($dir . 'sjs_base.php');
			
			if( ! file_exists( $base_file ) ){
				show_error('找不到基础文件 {'. $base_file .'}', 404, 'file not found');
			} else {
				include($base_file);
			}
			
			if( ! file_exists( $class_file ) ){
				show_error('找不到处理文件 {'. $class_file .'}', 404, 'file not found');
			} else {
				include($class_file);
			}
			
			$class = new $class_name();
			
			$class->blog_user = $username;
			$class->init();
			
			// 检测剩余 $uri 中的信息, 提取方法名 和 参数
			if( count($uri) != 0 ){
				$method_name = strtolower( array_shift( $uri ) );
			} else {
				$method_name = 'home';
			}
			
			if( count($uri) != 0 ){
				$class->$method_name($uri);
			} else {
				$class->$method_name();
			}
			
		} else {
			
			if( $module == 'index' ) $module = 'home';
			
			$class_name = 'sjs_' . $module;
			require($dir . 'sjs_base.php');
			require($dir . $class_name . '.php');
			$class = new $class_name();
			
			if( count($params) == 0 ){
				$class->home();
			} else {
				
				if( method_exists($class, $params[0]) ){
					$class->$params[0]();
				} else {
					$class->_remap($params[0]);
				}
				
			}
			
		}
		
	}

}

































?>