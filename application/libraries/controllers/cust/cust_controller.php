<?php

class cust_controller extends MY_Controller {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function _remap($class, $params = array())
	{
		$this->route($class, $params, 'cust');
	}
	
}

?>