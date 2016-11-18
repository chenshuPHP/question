<?php

class deco_controller extends MY_Controller {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function _remap($class, $args)
	{
		$this->route($class, $args, 'deco');
	}
	
}

?>