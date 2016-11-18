<?php

if( ! defined('BASEPATH') ) exit('禁止直接浏览');

class router_controller extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _remap($class, $params = ''){
		
		
		$host = strtolower( $_SERVER['HTTP_HOST'] );
		$segments = explode('.', $host);
		$label = $segments[0];
		
		$target = false;	// 域名是否有对应的指向目标
		
		// 判断是否为分站
		$this->load->model('sc/site_city_model');
		if( ( $site_city = $this->site_city_model->get_item($label) ) != false ){
			$target = true;
			// 移交分站处理程序
			include('sc/sc_controller.php');
			$controller = new sc_controller();
			$params = array(
				'class'=>$class,
				'params'=>$params,
				'sc'=>$site_city
			);
			$controller->handler($params);
		}
		
		
		// 未找到指向目标，返回 404
		if( $target == false ) show_404();
		
	}
}


?>