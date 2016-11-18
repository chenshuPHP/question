<?php

// 访客留言管理
class member_leave_base extends member_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('module', 'leave');
	}
	
	
	
}

?>