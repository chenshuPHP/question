<?php

class admin_base extends MY_Controller {
	
	public $admin_username = '';
	public $manage_url = '';
	public $admin_url = '';
	public $admin_res_url = '';
	public $admin_level = '';
	public $admin_city_id = '99999';
	
	public function __construct(){
		
		parent::__construct();
		$this->manage_url = $this->config->item('base_url') . '/shzh_manage_v2/';
		$this->admin_url = $this->manage_url;
		
		// $this->admin_res_url = rtrim($this->config->item('base_url'), '/') . '/resources/admin/';
		
		$this->admin_res_url = rtrim($this->config->item('resources_url'), '/') . '/admin/';
		
		if( ! isset($this->encode) ) $this->load->library('encode');
		$this->tpl->assign('r', urlencode($_SERVER['REQUEST_URI']));
		
		$this->load->model('admin/admin_model_login');
		if( $this->admin_model_login->check_login() == false ){
			echo('登录超时，<a href="/shzh_manage/login.asp">请重新登录</a>');
			exit();
		}
		
		$this->admin_username = $this->admin_model_login->get_admin_username();		// 获取当前用户名
		$this->admin_level = $this->admin_model_login->get_admin_level();			// 用户权限等级
		$this->admin_city_id = $this->admin_model_login->get_admin_city_id();		// 用户所属分站管理权
		
		$this->tpl->assign('admin_level', $this->admin_level);
		$this->tpl->assign('admin_username', $this->admin_username);
		$this->tpl->assign('admin_city_id', $this->admin_city_id);
		
		$this->tpl->assign('admin_url', rtrim($this->manage_url, '/') . '/');
		$this->tpl->assign('admin_res_url', $this->admin_res_url);
		
		$this->tpl->registerPlugin('modifier', 'admin_url', array($this, 'get_complete_url'));
		
		
	}
	
	public function display($content = ''){
		$this->tpl->assign('content', $content);
		$this->tpl->display('admin/frame.html');
	}
	
	// 获取完整的管理 URL
	public function get_complete_url($url){
		return rtrim($this->manage_url , '/') . '/' . ltrim($url, '/');
	}
	
	public function get_tpl($tpl){
		return 'admin/' . ltrim($tpl, '/');
	}
	
}