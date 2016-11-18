<?php


// 站内信 基类
// 2016-09-05
// kko4455

class member_letter_base extends member_base {
	
	public function __construct(){
		
		parent::__construct();
		
		$this->load->model('company/member_letter_model');
		$this->assign_counter();
		$this->tpl->assign('module', 'letter');
	}
	
	// 获取并分配统计信息 信件统计
	public function assign_counter()
	{
		
		// 所有站内信统计
		$all_letter_count = $this->member_letter_model->counter($this->base_user, array(
			'isVIP'				=> $this->isVIP(),
			'username'			=> $this->base_user
		));
		
		// 未读信息统计
		$new_letter_count = $this->member_letter_model->counter($this->base_user, array(
			'isVIP'				=> $this->isVIP(),
			'username'			=> $this->base_user,
			'open'				=> 0
		));
		
		$this->tpl->assign('all_letter_count', $all_letter_count);
		$this->tpl->assign('new_letter_count', $new_letter_count);
	}
	
	
	
	
}


?>