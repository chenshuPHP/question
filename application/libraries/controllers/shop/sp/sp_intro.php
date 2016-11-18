<?php

// 公司简介
class sp_intro extends sp_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'project');
	}
	
	public function home( $args = array() )
	{
		
		$deco = $this->deco_model->get_company($this->user, 'username, content, company_pic, manager, rejion');
		
		$this->tpl->assign('content', $deco['content']);
		$this->tpl->assign('company_pic', $deco['company_pic']);
		$this->tpl->assign('manager', $deco['manager']);
		$this->tpl->assign('rejion', $deco['rejion']);
		$this->tpl->assign('module', 'intro');
		$this->tpl->display( $this->get_tpl('intro.html') );
	}

}
?>