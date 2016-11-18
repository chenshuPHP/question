<?php

class sjs_register extends sjs_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		
		if( $this->gr('t') == 'p' ){
			$this->persion();
			exit();
		}
		
		$this->load->model('sjs/sjs_config_model');
		$adepts = $this->sjs_config_model->get_adepts();
		$this->tpl->assign('adepts', $adepts);
		$this->tpl->display('sjs/register.html');
	}
	
	// 个人注册
	public function persion(){
		
		$this->tpl->display('sjs/persion_regist.html');
		
	}
	
	
	
	/*
	public function handler(){
		$info = $this->get_form_data();
		var_dump($info);
	}
	*/
	
	
	
	
}

?>