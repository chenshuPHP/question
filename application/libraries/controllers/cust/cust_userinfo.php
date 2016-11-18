<?php

// 用户信息编辑

class cust_userinfo extends cust_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function home()
	{
		$tpl = $this->get_tpl('home.html');
		$this->load->model('cust/cust_model');
		$info = $this->cust_model->get_user(array('username'=>$this->user['username']));
		$this->tpl->assign('user', $info);
		
		
		$this->tpl->assign('edit_success', $this->gr('edit_success'));		// 修改成功反馈标记
		
		$this->tpl->display($tpl);
	}
	
	public function edit()
	{
		$info = $this->get_form_data();
		
		if( empty( $info['rejion'] ) )
		{
			exit('昵称不能为空');
		}
		
		if( empty( $info['company_date'] ) && preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $info['company_date']) )
		{
			exit('生日格式错误');
		}
		
		$this->load->model('cust/cust_model');
		
		try
		{
			$this->cust_model->edit($this->user['username'], $info);
			$this->load->helper('url');
			redirect( $this->get_cust_url('/userinfo?edit_success=1') );
		}
		catch(Exception $e)
		{
			echo( $e->getMessage() );
		}
		
	}
	
	
	
}


?>