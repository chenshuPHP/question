<?php

// 注册 相关功能
class register_active extends register_base {

	public function __construct(){
		parent::__construct();
	}
	
	// 短信验证码发送
	public function send_message(){
		
		exit();
		
		$mobile = $this->gf('mobile');

		$error = '';

		if( ! preg_match('/^1[3-8]\d{9}$/', $mobile) ){
			$error = '手机号码格式错误';
		} else {

			$this->load->model('sms_model');

			// 100 秒只可发送一次
			if( ! $this->sms_model->check_resend_limit($mobile, 100) ){
				$error = '100秒内只可发送一次，请稍等片刻';
			}
			
			// 每小时最多20条限制
			if( ! $this->sms_model->check_send_count($mobile) ){
				$error = '该手机号码1小时内无法再次发送短信了。';
			}
		}

		if( empty( $error ) ){
			$code = $this->sms_model->get_validate_code();
			$message = '【上海室内装饰行业协会】注册验证码：'. $code;
			$this->load->library('send_message');
			try{
				
				// 发送短信
				$this->send_message->send($mobile, urlencode( $message ));
				
				// 添加记录
				$this->sms_model->add(array(
					'tid'=>0,
					'message'=>$message,
					'category'=>'register',
					'addtime'=>date('Y-m-d H:i:s'),
					'mobile'=>$mobile,
					'validate_code'=>$code
				));

			}catch(Exception $e){
				$error = $e->getMessage();
			}
		}

		$result = array();

		if( empty($error) ){
			$result['type'] = 'success';
		} else {
			$result['type'] = 'error';
			$result['message'] = $error;
		}
		echo( json_encode($result) );
	}

	// 验证码检测
	public function check_mobile_code(){

		$mobile = $this->gf('mobile');
		$code = $this->gf('code');

		$this->load->model('sms_model');
		
		$sms = $this->sms_model->get_sms($mobile, 'register');

		// var_dump($sms);

		$result = array();
		$result['type'] = 'error';

		// 如果有验证码时效性限制，可在这里检测
		// 这里没有做检测

		if( ! $sms ){
			//$result['message'] = '找不到验证码发送记录，请重新发送';
			$result['message'] = '找不到验证码，请重新发送';
		} else {
			if( $sms['validate_code'] != $code ){
				$result['message'] = '验证码错误';
			} else {
				$result['type'] = 'success';
			}
		}

		echo(json_encode( $result ));
		
	}

	// 手机号码检测
	public function check_mobile(){

		$mobile = $this->gf('mobile');

		$result = array();
		$result['type'] = 'error';

		if( ! preg_match('/^1[3-8]\d{9}$/', $mobile) ){
			$result['message'] = '手机号码格式错误';
		} else {
			$this->load->model('company/company', 'deco_model');

			$deco = $this->deco_model->get_company($mobile, 'id');

			if( $deco != false ){
				$result['type'] = 'exists';
				$result['message'] = '已注册';
			} else {
				$result['type'] = 'success';
			}
		}
		echo( json_encode( $result ) );
	}


}

?>