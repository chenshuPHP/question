<?php

// 企业促销 基类
class member_promotion_base extends member_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('module', 'promotion');
		$this->tpl->assign('billboard', $this->billboard(
			array(
				'text'		=> '如果您公司近期有优惠活动，可以在这里发布您的优惠信息'
			)
		));
	}
	
}

?>