<?php

/*
	协会保障
	2015-01-09
*/

class multi_xhbz extends multi_base{
	
	public function __construct(){
		parent::__construct(array(
			'decos'=>0,
			'diaries'=>0
		));
	}
	
	
	public function home(){
		
		$this->tpl->display('multi\xhbz.html');
		
	}
	
	
	
}
?>