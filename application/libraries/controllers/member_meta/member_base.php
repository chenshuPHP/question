<?php
// 建材管理基类
class member_base extends MY_Controller {
	
	protected $info = NULL;
	public $member_url = '';
	
	public function __construct(){
		parent::__construct();
		
		$this->_check_login();
		
		$this->tpl->assign('info', $this->info);
		
		//$urls = $this->config->item('url');
		$this->member_url = 'http://www.shzh.net/member_meta/';
		
		$this->tpl->assign('member_url', 'http://www.shzh.net/member_meta/');
		$this->tpl->assign('member_res_url', 'http://www.shzh.net/resources/member_meta/');
		
	}
	
	private function _check_login(){
		
		$this->load->model('loginModel', 'login_model');
		
		if( ( $this->info = $this->login_model->check_login() ) == false ){
			$this->alert('', $this->config->item('base_url') . '/login.html');
			exit();
		} else {
			if( $this->info['hangye'] != '建材公司' ){
				exit('登录已经过期，请<a href="'. $this->config->item('base_url') .'/login.html">重新登录</a>');
			}
		}
		
	}
	
}
?>