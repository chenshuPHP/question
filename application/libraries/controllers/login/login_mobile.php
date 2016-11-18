<?php

// 手机登录
// 客户端需要以 ajax 方式交互
class login_mobile extends login_base {
	
	private $_validate_name = 'mobile_login_validate';		// 短信图形验证码标识
	private $_sms_type = 'login';							// 短信发送记录类型标识
	
	
	public function __construct()
	{
		parent::__construct();
		
		// 使 XHR POST 跨域请求支持
		header("Access-Control-Allow-Origin:*");
		
		if( ! isset( $_SESSION ) ) session_start();
		
		
		//$this->tpl->registerPlugin('modifier', 'mobile_login_url', array($this, 'get_mobile_login_url'));
	}
	
	// 创建手机短信发送验证码
	// 验证码创建
	public function validate()
	{
		$this->load->library('kaocode');
		$this->kaocode->doimg();
		$_SESSION[ $this->_validate_name ] = $this->kaocode->getCode();
	}
	
	// 获取服务器上当前的验证码
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
	
	// ajax 验证码检测 支持
	// 验证出错服务器会清除验证码, 客户端需要刷新验证码
	public function ajax_check_validate()
	{
		echo $this->_check_validate( $this->gf('validate') ) ? 1 : 0;
	}
	
	// 向用户手机发送验证码
	// 需要验证图形验证码准确性
	// 接收参数有 { 手机号码, 图形验证码 }
	public function send_mobile_message()
	{
		$info = array(
			'mobile'		=> $this->gf('mobile'),
			'validate'		=> $this->gf('validate'),
			
			// 这里的token是用来跳过验证码的
			// 为什么要跳过?
			// 移动端有自己的验证码验证程序, 这里如果是移动端服务器请求 就验证其token是否有权限
			
			'token'			=> $this->gf('token')
		);
		
		$errors = array();
		
		// 如果是服务器请求, 就免验证码验证
		if( $info['token'] != config_item('shzh_sync_token') )
		{
			if( ! $this->_check_validate( $info['validate'] ) )
			{
				$errors[] = '验证码错误';
			}
		}
		
		if( count( $errors ) == 0 )
		{
			if( ! preg_match('/^1[3-9]\d{9}$/', $info['mobile']) )
			{
				$errors[] = '手机号格式错误';
			}
		}
		
		if( count( $errors ) == 0 )
		{
			$this->load->model('sms_model');
			// 查询号码是否在短期内重复发送过
			if( ! $this->sms_model->check_resend_limit($info['mobile'], 60) )
			{
				$errors[] = '短期内已经发送过';
			}
		}
		
		if( count( $errors ) == 0 )
		{
			if( ! $this->sms_model->check_send_count( $info['mobile'] ) )
			{
				$errors[] = '超过当天短信发送上限';
			}
		}
		
		// 您的验证码是 568955。如有疑问请拨打 4007285580
		if( count( $errors ) == 0 )
		{
			$this->load->library('send_message');
			$mobile_validate_code = $this->sms_model->get_validate_code();
			$tel = config_item('tel');
			$content = '您的验证码是 '. $mobile_validate_code .'。如有疑问请拨打 ' . $tel['400'];
			
			$send_report = $this->send_message->send($content, $info['mobile']);
			
			// $send_report = TRUE;
			
			if( $send_report === TRUE )
			{
				$this->sms_model->add(array(
					'tid'				=> 0,
					'message'			=> $content,
					'category'			=> $this->_sms_type,
					'addtime'			=> date('Y-m-d H:i:s'),
					'mobile'			=> $info['mobile'],
					'validate_code'		=> $mobile_validate_code
				));
			}
			else
			{
				$errors[] = $send_report;
			}
		}
		
		// 发送成功
		if( count( $errors ) == 0 )
		{
			
			// 服务器端请求免去验证操作
			if( $info['token'] != config_item('shzh_sync_token') )
			{
				// 这里客户手机应该已经收到验证码了
				// 防止用户使用一个对的验证码重复发送不同手机号
				// 客户端倒计时结束后, 若要再次发送, 则需要刷新验证码
				// 发送成功仍然要清除验证码
				// 多客户端应该由客户自行操作
				$this->_clear_validate();
			}
			
			
			echo( json_encode(array(
				'type'		=> 'success'
			)) );
		}
		else
		{
			echo( json_encode(array(
				'type'		=> 'error',
				'error'		=> $errors
			)) );
		}
		
	}
	
	// 登录操作
	// 可以忽略图形验证码, 使用手机号的验证码
	// 验证码有效时间 10 分钟
	// 接收参数 {手机号码, 短信验证码}
	public function handler()
	{
		$info = array(
			'mobile'				=> $this->gf('mobile'),
			'mobile_validate'		=> $this->gf('mobile_validate')
		);
		
		$errors = array();
		
		if( empty( $info['mobile'] ) || empty( $info['mobile_validate'] ) )
		{
			$errors[] = '参数错误';
		}
		
		if( count( $errors ) == 0 )
		{
			$this->load->model('sms_model');
			$res = $this->sms_model->get_sms($info['mobile'], $this->_sms_type);
			if( ! $res )
			{
				$errors[] = '手机号不匹配';
			}
		}
			
		if( count( $errors ) == 0 )
		{
			if( $res['validate_code'] != $info['mobile_validate'] )
			{
				$errors[] = '短信验证码错误';
			}
		}
		
		if( count( $errors ) == 0 )
		{
			$datediff = strtotime( date('Y-m-d H:i:s') ) - strtotime( $res['addtime'] );
			
			if( $datediff > 600 )
			{
				$errors[] = '验证码已超时';
			}
		}
		
		$username = '';
		if( count( $errors ) == 0 )
		{
			
			$_type = 'mobile';
			$this->load->model('login/thirdparty_login_model');
			$this->thirdparty_login_model->set_type( $_type );
			
			// 创建凭据信息
			$token_info = array(
				'type'			=> $_type,
				'mobile'		=> $info['mobile'],
				'nickname'		=> ''
			);
			
			// 保存到数据库, 准备进行和装潢网已有用户进行绑定操作
			$this->thirdparty_login_model->save($token_info);
			
			// 创建Session跳转到绑定页面
			$_SESSION['thirdparty_login_token'] = array(
				'type'			=> $token_info['type'],
				'openid'		=> $token_info['mobile']
			);
		}
		
		if( count( $errors ) == 0 )
		{
			$urls = config_item('url');
			
			// 短信验证通过, 检测首次登陆 或者 已绑定以绑定账户登录
			// 生成 acs_token 给 client 方式绑定
			
			$acs_token = $this->thirdparty_login_model->acs_token($token_info);
			
			json_echo( array(
					'type' 						=> 'success',
					// 参考跳转地址
					'thirdparty_bind_url'		=> rtrim($urls['www'], '/') . '/login/thirdparty/bind?rurl=',
					'token'						=> $acs_token
			) );
		}
		else
		{
			json_echo( array(
				'type'			=> 'error',
				'errors'		=> $errors
			) );
		}
		
	}



}





















?>