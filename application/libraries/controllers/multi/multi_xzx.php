<?php

/*
	先装修后付款
	2015-01-08
*/

class multi_xzx extends multi_base{
	
	public function __construct(){
		parent::__construct(array(
			'decos'=>0,
			'diaries'=>0
		));
	}
	
	
	public function home(){
		
		$this->tpl->display('multi\xzx.html');
		
	}
	
	
	
}
?>