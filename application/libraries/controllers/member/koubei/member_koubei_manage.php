<?php

// 口碑值管理
class member_koubei_manage extends member_koubei_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		$tpl = $this->get_tpl('koubei/manage.html');
		
		$this->tpl->display( $tpl );
	}
	
	
	
	
}


?>