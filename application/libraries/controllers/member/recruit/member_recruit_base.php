<?php

// 企业招聘 基类
class member_recruit_base extends member_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('module', 'recruit');
	}
	
	
	
}

?>