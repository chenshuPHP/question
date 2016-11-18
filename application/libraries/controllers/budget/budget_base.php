<?php

// 预算下载基类
// 2015-06-23
class budget_base extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('body_class', 'w1200');
	}
	
}

?>