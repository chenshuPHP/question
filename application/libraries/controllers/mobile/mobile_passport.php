<?php

class mobile_passport extends mobile_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('page_name', '登录');
	}
	
	public function login()
	{
		$rurl = $this->gr('r');
		$tpl = $this->get_tpl('passport/login.html');
		$this->tpl->assign('rurl', $this->encode->rurlencode( $rurl ));
		$this->tpl->display( $tpl );
	}
	
	// 普通登录
	public function login_handler()
	{
		$info = $this->get_form_data();
		$error = '';
		if( ! isset( $info['username'] ) || ! isset( $info['password'] ) || $info['username'] == '' || $info['password'] == '' )
		{
			$error = '用户名和密码不能为空';
		}
		
		if( $error == '' )
		{
			$config = array(
				'url'		=> $this->get_complete_url('/login/login_ajax'),		// http://www.shzh.net/login/login_ajax
				'data'		=> array(
					'username'		=> $info['username'],
					'password'		=> $info['password'],
					'device'		=> 'mobile',
					'type'			=> 'token'
				)
			);
			
			$this->load->library('sync');
			
			$result = $this->sync->ajax( $config );
			
			if( $result['type'] == 'success' )
			{
				$this->_create_cookie( array(
					'username'		=> strtolower( $info['username'] ),
					'token'			=> $result['token']
				) );
				json_echo( array(
					'type'		=> 'success'
				) );
			}
			else
			{
				json_echo( $result );
			}			
		}
	}
	
	// 图片验证码需要独立验证 不可跨域
	// ======================================
	// 获取服务器上当前的验证码
	private $_validate_name = 'mobile_login_validate';		// 短信图形验证码标识
	private function get_validate_code()
	{
		if( ! isset( $_SESSION[ $this->_validate_name ] ) ) return '';
		return $_SESSION[ $this->_validate_name ];
	}
	
	// 清除验证码
	private function _clear_validate()
	{
		unset( $_SESSION[ $this->_validate_name ] );
	}
	
	// 检验验证码是否匹配
	// $failed_clear 验证失败是否清除服务器当前验证码
	private function _check_validate( $client_code = '', $failed_clear = TRUE )
	{
		$server_code = $this->get_validate_code();
		$check = TRUE;
		if( empty( $server_code ) || empty( $client_code ) ) $check = FALSE;
		if( $check ) $check = strtolower( $server_code ) == strtolower( $client_code );
		if( ! $check && $failed_clear == TRUE )
		{
			$this->_clear_validate();
		}
		return $check;
	}
	// ======================================
	


	// 检测手机发送验证码
	public function check_validate()
	{
		
		if( ! isset( $_SESSION ) ) session_start();
		
		$info = array(
			'mobile'		=> $this->gf('mobile'),
			'validate'		=> $this->gf('validate')
		);
		
		$error = '';
		
		if( ! $this->_check_validate( $info['validate'] ) )
		{
			$error = '验证码错误';
		}
		
		if( $error == '' )
		{
			$this->load->library('sync');
			
			$config = array(
				'url'		=> $this->get_complete_url('/login/mobile/send_mobile_message'),
				'data'		=> array(
					'mobile'		=> $info['mobile'],
					'token'			=> config_item('shzh_sync_token')
				)
			);
			
			$result = $this->sync->ajax( $config );
			
			$this->_clear_validate();
			
			if( $result['type'] != 'success' )
			{
				$error = implode(',', $result['error']);
			}
		}
		
		if( $error == '' )
		{
			json_echo( array(
				'type'		=> 'success'
			) );
		}
		else
		{
			json_echo( array(
				'type'		=> 'error',
				'error'		=> $error
			) );
		}
	}
	
	// 第三方绑定界面
	public function thirdparty_bind()
	{
		$tpl = $this->get_tpl( 'passport/thirdparty_bind_m.html' );
		
		//$type = $this->gr('type');
		//$openid = $this->gr('openid');
		
		$acs_token = $this->gr('token');
		$rurl = $this->gr('rurl');
		if( empty( $rurl ) ) $rurl = $this->get_mobile_url('/');
		
		$this->load->library('sync');
		
		$config = array(
			'url'		=> $this->get_complete_url('/login/thirdparty/bind'),
			// 'dataType'	=> 'string',
			'data'		=> array(
				'acs_token'	=> $acs_token,
				'token'		=> config_item('shzh_sync_token'),
			)
		);
			
		$result = $this->sync->ajax( $config );
		
		if( empty($result) ) exit('参数错误');
		
		//var_dump2( $result );
		
		if( ! empty( $result['token'] ) && ! empty( $result['username'] ) )
		{
			$this->_create_cookie(array(
				'username'		=> $result['username'],
				'token'			=> $result['token']
			));
			$this->load->helper('url');
			redirect( $rurl );
		}
		else
		{
			$this->tpl->assign('data', $result);
			$this->tpl->assign('rurl', $rurl);
			$this->tpl->assign('acs_token', $acs_token);
			$this->tpl->display($tpl);
		}
	}
	
	public function bind_handler()
	{
		$info = array(
			'password'		=> $this->gf('password'),
			'username'		=> $this->gf('username'),
			'acs_token'		=> $this->gf('acs_token')
		);
		
		$error = '';
		
		$this->load->library('sync');
		$config = array(
			'url'		=> $this->get_complete_url('/login/thirdparty/bind_handler'),
			//'dataType'	=> 'string',
			'data'		=> array(
				'acs_token'		=> $info['acs_token'],
				'username'		=> $info['username'],
				'password'		=> $info['password'],
				'token'			=> config_item('shzh_sync_token'),
			)
		);
			
		$result = $this->sync->ajax( $config );
		
		//var_dump2( $result );
		
		if( $result['type'] == 'success' )
		{
			$this->_create_cookie(array(
				'token'		=> $result['token'],
				'username'	=> $result['username']
			));
			json_echo(array(
				'type'		=> 'success'
			));
		}
		else
		{
			json_echo( $result );
		}
		
	}
	
	// 跳过绑定
	public function skip_bind()
	{
		$info = array(
			'acs_token'		=> $this->gf('acs_token'),
			'type'			=> $this->gf('type'),
			'name'			=> $this->gf('name'),
			'token'			=> config_item('shzh_sync_token'),
		);
		
		$error = '';
		
		if( $info['acs_token'] == '' ) $error = 'acs token 错误';
		
		if( $error == '' )
		{
			$this->load->library('sync');
			$config = array(
				'url'		=> $this->get_complete_url('/login/thirdparty/skip_bind'),
				// 'dataType'	=> 'string',
				'data'		=> array(
					'acs_token'				=> $info['acs_token'],
					'type'					=> $info['type'],
					'name'					=> $info['name'],
					'token'					=> $info['token'],
				)
			);
			$result = $this->sync->ajax( $config );
			if( $result['type'] == 'success' )
			{
				$this->_create_cookie(array(
					'token'		=> $result['token'],
					'username'	=> $result['username']
				));
			}
			elseif ( $result['type'] == 'error' )
			{
				$error = $result['error'];
			}
		}
		
		if( $error == '' )
		{
			json_echo( array(
				'type'		=> 'success'
			) );
		}
		else
		{
			json_echo(array(
				'type'		=> 'error',
				'error'		=> $error
			));
		}
		
	}

	// $info => [username, token]
	private function _create_cookie($info)
	{
		$urls = config_item('url');
		$domain = $urls['domain'];
		setcookie('USERNAME', $info['username'], time() + 3600*24*7, '/', $domain, false);
		setcookie('TOKEN', $info['token'], time() + 3600*24*7, '/', $domain, false);
	}
	
	private function _clear_cookie()
	{
		$urls = config_item('url');
		$domain = $urls['domain'];
		setcookie('USERNAME', '', time() - 1, '/', $domain, false);
		setcookie('TOKEN', '', time() - 1, '/', $domain, false);
	}
	
	public function logout()
	{
		$r = $this->gr('r');
		$this->_clear_cookie();
		if( ! empty( $r ) )
		{
			$this->load->helper('url');
			redirect( $r );
		}
		else
		{
			echo '您已经退出';
		}
	}
	






















}

?>