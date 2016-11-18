<?php

class article_process extends article_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		
		//$tpl = 'article/process/home.html'
		//$this->tpl->assign('title', '装修流程');
		//$this->tpl->display($tpl);
		
		$module = $this->gr('t');
		
		if( $module == '' ) $module = 'fuwu';
		
		$this->tpl->assign('module', $module);
		
		//$this->home('article/process/home2015.html');
		$this->tpl->assign('title', '装修流程');
		$this->tpl->display('article/process/home2015.html');
		
	}
	
	
}


?>