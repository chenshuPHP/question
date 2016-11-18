<?php

class sjs_base extends MY_Controller {
	
	protected $urls = '';
	
	public function __construct(){
		parent::__construct();
		
		$this->urls = $this->config->item('url');
		
		$this->tpl->assign('sjs_res_url', rtrim($this->urls['res'], '/') . '/sjs/');
		$this->tpl->assign('hide_kf', '1');
		
	}
	
	public function get_complete_url($string){
		return rtrim($this->urls['sjs'], '/') . '/' . ltrim($string, '/');
	}
	
}

?>