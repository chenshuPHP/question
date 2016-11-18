<?php

// 微网页报名数据查看 基类
class mbs_base extends my_controller {
	
	public $mbs_url;

	public $admin;

	public function __construct($config = array()){
		parent::__construct();
		
		$settings = array(
			'check_login'=>true
		);

		$settings = array_merge($settings, $config);

		$this->load->model('mobile/mbs_model');
		$this->mbs_url = $this->mbs_model->get_base_url();

		if( $settings['check_login'] == true ){
			if( ! $this->mbs_model->check_login() ){
				$this->alert('您未登录，无权限浏览此页面', $this->get_mbs_complete_url('/login'));
				exit();
			} else {
				$this->admin = $this->mbs_model->get_login_info();
				$this->load->model('mobile/mobile_url_model');
				$this->admin = $this->mobile_url_model->format_single_mall_page($this->admin);
				//var_dump($this->admin);
				$this->tpl->assign('admin', $this->admin);
			}
		}

		$this->tpl->assign('module', '');
		$this->tpl->assign('mbs_url', $this->mbs_url);
		$this->tpl->assign('hide_kf', '1');
		$this->tpl->assign('hide_bdshare', '1');
	}

	protected function get_mbs_complete_url($url){
		return rtrim($this->mbs_url, '/') . '/' . ltrim($url, '/');
	}
	


}

?>