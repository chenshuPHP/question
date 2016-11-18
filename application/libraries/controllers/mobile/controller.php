<?php

if(!defined('BASEPATH')) exit('禁止直接浏览');

// 路由
class controller extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _R($label = '', $params = array())
	{
		
		$prefix = 'mobile_';
		
		$path_info = pathinfo(__FILE__);
		$dir = rtrim($path_info['dirname'], '\\') . '\\';		// 当前文件夹绝对路径
		
		$mobile_base_dir = $dir;
		
		if( count( $params ) == 0 ) $params = array('index');
		
		if( is_dir($dir . $label) ){
			
			$dir = $dir . $label . '\\';
			
			$class_suffix = array_shift($params);
			if( empty($class_suffix) ){
				exit('controller name is undefined!');
			}
			
			include( $mobile_base_dir . 'mobile_base.php' );
			
			$base_class_name = $prefix . $label . '_' . 'base';
			$class_name = $prefix . $label . '_' . $class_suffix;
			
		} else {
			$base_class_name = 'mobile_base';
			$class_name = 'mobile_' . $label;
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
		
	}
	
	public function _SHOP( $params )
	{
		$this->route('shop', $params, 'mobile');
	}
	
	public function _remap($label = '', $params = array()){
		switch( strtolower( $label ) )
		{
			// 店铺域名需要独立处理, 因为包含用户名
			case 'shop':
				$this->_SHOP($params);
				break;
			default:
				$this->_R($label, $params);
				break;
		}
	}
	
}
