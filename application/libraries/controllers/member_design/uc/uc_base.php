<?php

// 设计师用户管理中心
class uc_base extends MY_Controller {
	
	public $info = NULL;
	public $ucenter_url = '';
	
	public function __construct(){
		parent::__construct();
		
		$this->load->model('LoginModel', 'login');
		$this->load->model('sjs/info', 'infomation');
		
		
		if( ($this->info = $this->login->check_login()) == false ){
			exit('登录已经过期，请<a href="/login.html">重新登录</a>');
		}
		
		$this->info = $this->infomation->formatSingle($this->info);
		
		$this->tpl->assign('info', $this->info);
		
		$urls = $this->config->item('url');
		$this->ucenter_url = rtrim($urls['sjs_ucenter'], '/');
		$this->tpl->assign('ucenter_url', $this->ucenter_url);
		
		$this->tpl->assign('title', '设计师-会员中心');
		$this->tpl->assign('hide_kf', '1');
		$this->tpl->assign('module', '-');
		
		//$this->tpl->assign('version', $this->gr('version'));
		
		$this->tpl->assign('uc_res_url', rtrim($urls['res'], '/') . '/member_design/uc/');
		
		$this->tpl->assign('sjs_res_url', $urls['res'] . 'sjs/');
		
	}
	
	public function get_complete_url($string){
		return rtrim($this->ucenter_url, '/') . '/' . ltrim($string, '/');
	}
	
	protected function get_tpl($tpl_name){
		return 'member_design/ucenter/' . ltrim($tpl_name, '/');
	}
	
}

?>