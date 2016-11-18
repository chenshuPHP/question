<?php

if( ! defined('BASEPATH') ) exit('无法访问');

// 城市相关
class city extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 获取城市脚本
	// 2015-06-08 城市三连动
	public function getscript(){
		
		$tpl = 'active/city/getscript.js.html';
		
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60 * 60 * 24 * 365;	// 缓存365天
		$this->tpl->cache_dir = $this->tpl->cache_dir . 'active/city/';
		
		if( ! $this->tpl->isCached($tpl) ){
			$this->load->model('city_model');
			$data = $this->city_model->tree();
			$this->tpl->assign('data', $data);
		}
		
		$this->tpl->display( $tpl );
		
		
	}
	
	public function update(){
		$this->load->model('city_model');
		$this->city_model->clear();
	}
	
}



?>