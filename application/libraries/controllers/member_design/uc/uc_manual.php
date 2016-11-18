<?php

// 平台手册 - 介绍平台的规则等信息
class uc_manual extends uc_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		
		
		$this->tpl->display( $this->get_tpl('manual/home.html') );
		
	}
	
}

?>