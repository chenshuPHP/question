<?php

class login_thirdparty extends login_base {
	
	public function __construct()
	{
		session_start();
		parent::__construct();
		// 使 XHR POST 跨域请求支持
		header("Access-Control-Allow-Origin:*");
	}
	
	// 直接登陆
	private function _login($username, $option = array())
	{
		
		$config = array(
			'rurl'			=> '',
			'client'		=> FALSE
		);
		
		// 兼容旧的参数
		if( is_string( $option ) )
		{
			$config['rurl'] = empty( $option ) ? $this->get_complete_url('/member/') : $option;
		}
		else
		{
			$config = array_merge($config, $option);
		}
		
		if( $config['client'] == FALSE )
		{
			$this->load->model('login/login_model');
			$this->login_model->login($username);	// 创建登录身份
			$this->load->helper('url');
			redirect( $config['rurl'] );
			return 1;
		}
		else
		{
			$this->load->model('LoginModel');
			$token = $this->LoginModel->create_token(array(
				'username'		=> $username,
				'device'		=> 'mobile'
			));
			return $token;
		}
		
	}
	
	public function bind()
	{
		
		// 判断浏览器登录还是客户端登陆
		// 浏览器登录写入 cookie 客户端登陆返回 token
		$_client = FALSE;
		$token = '';
		$bind_user = '';
		
		$error = '';
		
		if( ! isset( $_SESSION['thirdparty_login_token'] ) )
		{
			
			if( $this->gf('token') != config_item('shzh_sync_token') ) exit('token错误');
			
			// 兼容多客户端
			$acs_token = $this->gf('acs_token');
			
			$this->load->model('login/thirdparty_model');
			$_tmp = $this->thirdparty_model->acs_token_recovery( $acs_token );
			
			if( ! $_tmp )
			{
				exit('acs token 恢复失败');
			}
			else
			{
				$info = array(
					'type'		=> $_tmp['type'],
					'openid'	=> $_tmp['open_id']
				);
				$_client = TRUE;
			}
			
		}
		else
		{
			$info = $_SESSION['thirdparty_login_token'];
		}
		
		// if( ! isset( $info['openid'] ) || empty( $info['openid'] ) ) exit('绑定参数错误');
		
		// 跳过或绑定后跳转到的地址
		$rurl = $this->gr('rurl');
		if( empty( $rurl ) ) $rurl = $this->gf('rurl');
		$rurl = $this->encode->rurlencode( $rurl );
		
		
		$this->load->model('login/thirdparty_login_model');
		$this->thirdparty_login_model->set_type($info['type']);
		if( ! isset($info['openid']) ) $info['openid'] = $info['uid'];
		$bind_user = $this->thirdparty_login_model->get_bind_user($info['openid'], $info['type']);
		
		// 用户之前绑定过会员单位, 则直接登录
		if( $bind_user !== FALSE )
		{
			// 当 client 为 false时会自动跳转
			// 当client为TRUE时不会跳转, 而回返回 token 登录模式的 token
			$token = $this->_login($bind_user, array(
				'rurl'		=> $rurl,
				'client'	=> $_client
			));
		}
		
		// 进行一个会员绑定操作, 进入绑定界面
		$token_user_info = $this->thirdparty_login_model->get_user_info($info['openid']);
		$thirdparty = $this->thirdparty_login_model->get_thirdparty_info();
		
		if( $_client === FALSE )
		{
			$this->tpl->assign('thirdparty', $thirdparty);
			$this->tpl->assign('info', $token_user_info);
			$this->tpl->assign('rurl', $rurl);
			$this->tpl->display('login/thirdparty_bind.html');
		}
		else
		{
			$username = $bind_user ? $bind_user : '';
			json_echo( array(
				'token'				=> $token,
				'username'			=> $username,
				'thirdparty'		=> $thirdparty,
				'info'				=> $token_user_info,
				'rurl'				=> $rurl
			) );
		}
		
	}
	
	// 绑定操作提交
	public function bind_handler()
	{
		
		// info => [username, password, [token], [acs_token]]
		$info = $this->get_form_data();
		
		$client = FALSE;
		
		$error = '';
		
		$rurl = isset( $info['rurl'] ) ? $info['rurl'] : '';
		
		if( isset( $info['token'] ) && isset( $info['acs_token'] ) ) $client = TRUE;
		
		if( $client === TRUE )
		{
			if( $info['token'] != config_item('shzh_sync_token') ) $error = 'token 错误';
		}
		
		if( $error == '' )
		{
			$this->load->model('login/login_model');
			if( $this->login_model->check_pwd($info['username'], $info['password']) !== TRUE )
			{
				$error = '用户名密码错误';
			}
		}
		
		if( $error == '' )
		{
			if( $client === FALSE )
			{
				// 兼容旧的处理程序
				$this->load->model('login/thirdparty_login_model');
				$this->thirdparty_login_model->set_type($_SESSION['thirdparty_login_token']['type']);
				$this->thirdparty_login_model->bind($info['username'], $_SESSION['thirdparty_login_token']);
				$this->_login($info['username'], $rurl);
			}
			else
			{
				$this->load->model('login/thirdparty_model');
				$_tmp = $this->thirdparty_model->acs_token_recovery( $info['acs_token'] );
				
				if( ! $_tmp ) $error = 'acs token 恢复失败';
				
				if( $error == '' )
				{
					$_tmp = array(
						'type'		=> $_tmp['type'],
						'openid'	=> $_tmp['open_id']
					);
					
					$this->load->model('login/thirdparty_login_model');
					$this->thirdparty_login_model->set_type($_tmp['type']);
					$this->thirdparty_login_model->bind($info['username'], $_tmp);
					
					$token = $this->_login($info['username'], array(
						'rurl'		=> $rurl,
						'client'	=> $client
					));
					
				}
			}
		}
		
		if( $client == FALSE )
		{
			if( $error != '' ) echo( $error );
		}
		
		if( $client == TRUE )
		{
			if( $error != '' )
			{
				json_echo(array(
					'type'		=> 'error',
					'error'		=> $error
				));
			}
			else
			{
				json_echo(array(
					'token'		=> $token,
					'username'	=> $info['username'],
					'type'		=> 'success'
				));
			}
		}
		
		
	}
	
	public function skip_bind()
	{
		
		$error = '';
		$client = FALSE;
		$info = $this->get_form_data();		// [acs_token, token, name, type]
		if( isset( $info['acs_token'] ) && isset( $info['token'] ) )
		{
			$client = TRUE;
			if( $info['token'] != config_item('shzh_sync_token') ) $error = 'token 验证失败';
		}
		else
		{
			if( ! isset( $_SESSION['thirdparty_login_token'] ) )
			{
				$error = '参数错误';
			}
			else
			{
				$info = $_SESSION['thirdparty_login_token'];
			}
		}
		
		if( $error == '' )
		{
			$this->load->model('login/thirdparty_login_model');
			if( $client == FALSE )
			{
				$this->thirdparty_login_model->set_type($info['type']);
				try
				{
					$username = $this->thirdparty_login_model->skip_bind_handler($info);			// 跳过绑定操作, 返回新建的用户名
					$this->_login($username, $this->gr('rurl'));
				}
				catch(Exception $e)
				{
					$error = $e->getMessage();
				}
			}
			else
			{
				$this->load->model('login/thirdparty_model');
				$_tmp = $this->thirdparty_model->acs_token_recovery( $info['acs_token'] );
				if( ! $_tmp ) $error = 'acs token 恢复失败';
				if( $error == '' )
				{
					$this->thirdparty_login_model->set_type($_tmp['type']);
					$username = $this->thirdparty_login_model->skip_bind_handler(array(
						'type'	=> $_tmp['type'], 'openid' => $_tmp['open_id']
					), array(
						'type'				=> $info['type'],		// 跳过后的注册类型 ('person':个人, 'company':公司)
						'company_name'		=> $info['name']
					));
					$token = $this->_login($username, array(
						'client'	=> $client,
						'rurl'		=> ''
					));
				}
			}
		}
		
		if( $client == FALSE )
		{
			if( $error != '' ) exit( $error );
		}
		
		if( $client == TRUE )
		{
			if( $error == '' )
			{
				json_echo( array(
					'type'			=> 'success',
					'token'			=> $token,
					'username'		=> $username
				) );
			}
			else
			{
				json_echo( array(
					'type'			=> 'error',
					'error'			=> $error
				) );
			}
		}
		
		
	}
	
	
	
}























?>