<?php

class login_weibo extends login_base {
	
	private $type = 'weibo';
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('login/thirdparty_login_model');
		$this->thirdparty_login_model->set_type($this->type);
	}
	
	public function home()
	{
		$this->tpl->assign('url', $this->thirdparty_login_model->get_authorize_url());
		$this->tpl->display('login/weibo.html');
	}
	
	public function callback()
	{
		$info = array(
			'authorize_code'=>$this->gr('code'),
			'state'=>$this->gr('state')
		);
		
		if( $info['state'] !== $this->thirdparty_login_model->get_state_code() )
		{
			exit('状态码错误');
		}
		
		try
		{
			// 获取 access_token
			$token_info = $this->thirdparty_login_model->get_access_token($info['authorize_code']);
		}
		catch(Exception $e)
		{
			exit( $e->getMessage() );
		}
		
		$token_info['type'] = $this->type;
		$token_info['openid'] = $token_info['uid'];
		$this->thirdparty_login_model->save($token_info);
		
		// 创建SESSION, 在绑定页面需要用到
		// token_info = ['type', 'access_token', 'expires_in', 'uid', 'openid'];
		$_SESSION['thirdparty_login_token'] = $token_info;
		
		
		$this->load->helper('url');
		
		// 前往绑定页面
		redirect( $this->get_complete_url('/login/thirdparty/bind') );
		
	}
	
}

?>