<?php

class multi_zxgw extends multi_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		$this->tpl->display('multi/zxgw.html');
	}
	
	
	
	
}

?>