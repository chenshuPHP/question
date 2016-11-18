<?php

// 口碑值管理
class member_koubei_base extends member_base {
	
	public function __construct()
	{
		parent::__construct();
		
		$this->tpl->assign('module', 'koubei');
		
	}
	
}


?>