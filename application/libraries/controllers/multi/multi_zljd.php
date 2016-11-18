<?php

class multi_zljd extends multi_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		
		$this->tpl->display('multi/zljd.html');
	}
	
}

?>