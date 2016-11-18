<?php

class sjs_login extends sjs_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		
		
		$this->tpl->display('sjs/login.html');
		
	}
	
	
	
	
}

?>