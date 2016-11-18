<?php

class zhuanti_lawyer extends zhuanti_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function handler(){
		$this->active->tpl_name = 'zhuanti/lawyer/home.html';
	}
	
	
}

?>