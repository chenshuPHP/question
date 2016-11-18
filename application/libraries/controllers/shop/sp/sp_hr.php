<?php

// 人才招聘 by 袁仙增
class sp_hr extends sp_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home( $args = array() )
	{
		$this->load->model('company/hr', 'hr_model');
		$list = $this->hr_model->getHrs($this->user);
		
		$this->tpl->assign('list', $list);
		$this->tpl->assign('module', 'hr');
		$this->tpl->display($this->get_tpl('hr.html'));
	}

}
?>