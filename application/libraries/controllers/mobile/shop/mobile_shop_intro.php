<?php

// 公司简介
class mobile_shop_intro extends mobile_shop_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('module', 'intro');
	}
	
	// 公司简介主页
	public function home()
	{
		$tpl = $this->get_tpl('/shop/intro/home.html');
		
		$this->load->model('company/company', 'deco_model');
		
		$deco = $this->deco_model->getCompany($this->username, array('fields'=>'company_pic, content'));
		$deco['content'] = $this->encode->htmldecode($deco['content'], TRUE);
		
		$this->tpl->assign('intro', $deco);
		$this->tpl->display( $tpl );
	}
	
}

?>