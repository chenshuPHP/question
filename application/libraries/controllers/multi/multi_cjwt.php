<?php

class multi_cjwt extends multi_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		$this->load->model('multi/cjwt_model');
		$data = $this->cjwt_model->get_data();
		$this->tpl->assign('data', $data);
		$this->tpl->display('multi/cjwt.html');
	}
	
	
	
	
}

?>