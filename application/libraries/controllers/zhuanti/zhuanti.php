<?php

class zhuanti extends MY_Controller {
	
	public $zhuanti_name = '';	// 专题名称
	public $method_name = '';	// 专题子内子请求名称
	public $tpl_name = '';		// 专题模版名称, 模版内子请求时可能需要自定义
	public $zhuanti_info = NULL;
	
	function __construct(){
		parent::__construct();
	}
	
	function index($label = '', $method_name = ''){
		if( $label == '' ) show_404();
		$label = strtolower($label);
		$this->zhuanti_name = $label;
		
		// 支持子方法调用
		$this->method_name = $method_name;
		
		$this->load->model('zhuanti/ZhuantiModel', 'zhuanti_model');
		$object = $this->zhuanti_model->get_object_by_label($label);
		
		if( $object == false ) show_404();
		
		$this->zhuanti_info = $object;
		
		$resources_url = $this->config->item('resources_url');
		$zhuanti_resources_url = $resources_url . '/zhuanti/'. $label .'/';
		
		//if( method_exists($this, $label) == true ){
		//	$this->$label();
		//}
		
		$this->tpl->assign('object', $object);
		
		//echo('<!--');
		//var_dump($object);
		//echo('-->');
		
		$this->tpl->assign('zhuanti_resources_url', $zhuanti_resources_url);
		$this->tpl_name = 'zhuanti/'. $label .'/index.html';
		
		$this->_load_external_handler();
		
		if( ! empty($this->tpl_name) ){
			$this->tpl->display($this->tpl_name);
		}
		
	}
	
	/*
		专题外部处理
		2014-11-20
		kko4455@163.com
	*/
	private function _load_external_handler(){
		// 检测同名处理文件是否存在
		$prefix = 'zhuanti_';	// 前缀
		
		$directory = rtrim(dirname(__FILE__), '\\') . '\\';	// 外部处理文件所在目录
		
		$class_name = $prefix . $this->zhuanti_name;		// 外部处理文件的类名
		
		$common_class_name = $prefix . 'common';			// 如果没有同名的处理文件就加载这个通用的处理文件
		
		// 判断同名处理文件是否存在
		if( file_exists($directory . $class_name . '.php') ){
			include($directory . 'zhuanti_base.php');
			include($directory . $class_name . '.php');
			$method_name = 'handler';
			
			if( ! empty($this->method_name) ){
				$method_name = $this->method_name;
			}
			
			$object = new $class_name();					// 实例化同名处理对象
			if( method_exists($object, $method_name) ){		// 判断对象是否有 handler 方法
				//$object->tpl = $this->tpl;				// 传递Smarty对象到外部用于分配数据到模版
				$object->_clone( $this );					// 将本对象传递给外部处理文件，外部处理文件就可以调用本类中的方法
				$object->$method_name();					// 调用同名处理文件的 handler() 方法
				
				if( method_exists($object, 'terminate') ){
					$object->terminate();
				}
				
			} else {
				exit('method not exists');
			}
		} else {
			// 加载通用处理文件
			if( file_exists($directory . $common_class_name . '.php') ){
				include($directory . 'zhuanti_base.php');
				include($directory . $common_class_name . '.php');
				$object = new $common_class_name();
				if( method_exists($object, $this->zhuanti_name) ){	// 判断通用处理文件中是否有同名方法
					$method_name = $this->zhuanti_name;
					$object->_clone( $this );
					$object->$method_name();
				}
			}
		}
	}
	
	
}
















?>