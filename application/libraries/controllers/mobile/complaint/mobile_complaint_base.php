<?php

class mobile_complaint_base extends mobile_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('module', 'complaint');
	}
	
}

?>