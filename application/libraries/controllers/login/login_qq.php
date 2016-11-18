<?php

// QQ 登录
class login_qq extends login_base {
	
	private $type = 'qq';
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('login/thirdparty_login_model');
		$this->thirdparty_login_model->set_type($this->type);
	}
	
	//public function home()
	//{
	//	$this->tpl->assign('get_authorization_code_url', $this->thirdparty_login_model->get_authorization_code_url());
	//	$this->tpl->display('login/qq.html');
	//}
	
	// 授权 回调处理
	public function callback()
	{
		
		$info = array(
			'authorization_code'			=> $this->gr('code'),
			'state'							=> $this->gr('state')
		);
		
		if( $info['state'] !== $this->thirdparty_login_model->get_state_code() )
		{
			exit('状态码错误');
		}
		
		try
		{
			// 获取 access_token
			$token_info = $this->thirdparty_login_model->get_access_token($info['authorization_code']);
		}
		catch(Exception $e)
		{
			exit( $e->getMessage() );
		}
		
		// 获取OpenID
		$token_info['type'] = $this->type;
		$token_info['openid'] = $this->thirdparty_login_model->get_openid($token_info['access_token']);
		
		// 保存信息(保存新的, 更新已有的)
		$this->thirdparty_login_model->save($token_info);
		
		// 创建SESSION, 在绑定页面需要用到
		// token_info = ['type', 'access_token', 'refresh_token', 'expires_in', 'openid'];
		$_SESSION['thirdparty_login_token'] = $token_info;	
		
		$this->load->helper('url');
		
		// 前往绑定页面
		redirect( $this->get_complete_url('/login/thirdparty/bind') );
		
	}
	
	
}

?>