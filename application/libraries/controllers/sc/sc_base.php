<?php

// 分站基类
class sc_base extends MY_Controller {
	
	public $sc;
	
	public function __construct(){
		parent::__construct();
	}
	
	public function init($sc){
		$this->sc = $sc;
		$this->load->model('sc/site_city_model');
		$this->tpl->assign('sc', $this->sc);
		$urls = $this->config->item('url');
		$this->tpl->assign('sc_res_url', rtrim($urls['res'], '/') . '/sc/');
	}
	
}
 
?>