<?php

// 预算详细页
class budget_view extends budget_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home($args = array()){
		
		
		$id = $args[0];
		
		// 缓存
		
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60 * 60 * 24; 	// 单位 分钟, 缓存24小时
		$this->tpl->cache_dir = rtrim($this->tpl->cache_dir, '\\') . '\\budget\\detail\\';
		
		$tpl = 'budget/budget_detail.html';
		
		
		if( ! $this->tpl->isCached($tpl, $id) ){
			$this->load->model('budget/budget_model');
			$this->load->library('encode');
			$object = $this->budget_model->get_budget($id);
			$object['detail'] = $this->encode->htmldecode($object['detail']);
			$this->tpl->assign('object', $object);
			$this->load->model('budget/home_type_model');
			$configs = $this->home_type_model->get_all_types();
			$this->tpl->assign('configs', $configs);
			
			// 报价条件
			$this->load->model('budget/budget_config_model');
			$categories = $this->budget_config_model->roots();
			$categories = $this->budget_config_model->child_assign($categories);
			
			$this->tpl->assign('categories', $categories);
			$this->tpl->display($tpl, $id);
		} else {
			$this->tpl->display($tpl, $id);
			echo('<!-- cached -->');
		}
		
		
	}
	
}


?>