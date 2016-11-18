<?php

class admin_login extends MY_Controller {
	
	public function __construct()
	{
		parent::__construct();
		
		$this->tpl->assign('admin_url');
		
	}
	
	public function home()
	{
		$tpl = 'admin/login.html';
		$this->tpl->display( $tpl );
	}
	
	// 登录
	public function handler()
	{
		$info = $this->get_form_data();
		if( strtolower( $info['codestr'] ) != $this->get_validate_code() )
		{
			exit('验证码错误 <a href="'. $this->get_complete_url('/shzh_manage_v2/login') .'">返回</a>');
		}
		
		$this->load->model('manager/manager_model');
		$admin = $this->manager_model->get_manager( $info['username'], 'username, password, manages, lockstate, city_id' );
		
		if( ! $admin )
		{
			exit('找不到用户 <a href="'. $this->get_complete_url('/shzh_manage_v2/login') .'">返回</a>');
		}
		
		if( md5($info['password']) != $admin['password'] )
		{
			exit('密码错误 <a href="'. $this->get_complete_url('/shzh_manage_v2/login') .'">返回</a>');
		}
		
		
		setcookie('MANAGE', 'LOGIN', 0, '/');
		setcookie('MANAGE_PWD', urldecode($admin['manages']), 0, '/');
		setcookie('MANAGE_USER', $admin['username'], 0, '/');
		setcookie('MANAGE_CITY_ID', $admin['city_id'], 0, '/');
		
		$this->load->model('admin/admin_login_log_model');
		
		$this->admin_login_log_model->add(array(
			'username'		=> $admin['username'],
			'time'			=> date('Y-m-d H:i:s'),
			'ip'			=> $this->encode->get_ip(),
			'act'			=> 'login'
		));
		$this->alert('', '/shzh_manage/manage.asp');
		
	}
	
	// 登出
	public function out()
	{
		setcookie('MANAGE', '', -1, '/');
		setcookie('MANAGE_PWD', '', -1, '/');
		setcookie('MANAGE_USER', '', -1, '/');
		setcookie('MANAGE_CITY_ID', '', -1, '/');
		
		// 登出记录
				
		$this->alert('', '/shzh_manage_v2/login');
	}
	
	public function validate()
	{
		$this->load->library('kaocode');
		
		$this->kaocode->doimg();
		
		session_start();
		$_SESSION['admin_login_validate'] = $this->kaocode->getCode();
		
	}
	
	
	public function get_validate_code()
	{
		session_start();
		if( ! isset( $_SESSION['admin_login_validate'] ) ) return '';
		return strtolower( $_SESSION['admin_login_validate'] );
	}
	
}

?>