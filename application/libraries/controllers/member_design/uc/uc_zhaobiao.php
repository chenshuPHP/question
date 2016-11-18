<?php

class uc_zhaobiao extends uc_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function manage(){
		
		$state = $this->gr('state');
		
		switch( strtolower( $state ) ){
			case 'ing':
				$this->_ing();
				break;
			case 'finish':
				$this->_finish();
				break;
		}
		
	}
	
	public function _ing(){
		$this->tpl->display( $this->get_tpl('zhaobiao/ing.html') );
	}
	
	public function _finish(){
		$this->tpl->display( $this->get_tpl('zhaobiao/finish.html') );
	}
	
}

?>