<?php
class zhuanti_base {
	
	protected $active = NULL;
	
	public function __construct(){}
	
	public function _clone($object){
		$this->active = $object;
	}
	
	protected function cancel_tpl(){
		$this->active->tpl_name = '';
	}
	
	
}
?>