<?php

// 申请入会
class multi_ruhui extends multi_base {
	
	public function __construct(){
		parent::__construct(array(
			'decos'			=> 0,
			'diaries'		=> 0
		));
	}
	
	public function home(){
		$this->tpl->display('multi/ruhui.html');
	}
	
	public function handler(){
		$info = array(
			'deco_name'=>$this->gf('deco_name'),
			'name'=>$this->gf('name'),
			'addr'=>$this->gf('addr'),
			'mobile'=>$this->gf('mobile'),
			'cert'=>$this->gf('cert'),
			'deco_type'=>$this->gf('deco_type'),
			'validate'=>$this->gf('validate')
		);
		
		
		if( $info['deco_name'] == '' || $info['name'] == '' || $info['mobile'] == '' ){
			$this->alert('请将信息填写完整');
			exit();
		}
		
		if( ! $this->_validate_check( $info['validate'] ) ){
			$this->alert('验证码错误');
			exit();
		}
		
		if( ! empty($info['deco_type']) ){
			$info['type'] = implode(',', $info['deco_type']);
		} else {
			$info['type'] = '';
		}
		unset( $info['deco_type'] );
		unset( $info['validate'] );
		
		$this->load->model('multi/ruhui_model');
		
		try{
			$this->ruhui_model->add($info);
			$this->alert('提交成功', $this->multi_url . 'ruhui');
		}catch(Exception $e){
			$this->alert( $e->getMessage() );
			exit();
		}
	}
	
	// 验证码
	public function validate(){
		session_start();
		$this->load->library('kaocode');
		$this->kaocode->doimg();
		$_SESSION['multi_ruhui'] = $this->kaocode->getCode();
	}
	
	private function _validate_check($code){
		session_start();
		return strtolower($code) == strtolower($_SESSION['multi_ruhui']);
	}
	
	public function validate_check(){
		$code = $this->gf('code');
		if( $this->_validate_check($code) ){
			echo(1);
		} else {
			echo(0);
		}
	}
	
	// 接收来自协会的信息
	public function sync()
	{
		$info = $this->get_form_data();
		if( $info['shzh_sync_token'] != config_item('snzsxh_sync_token') )
		{
			exit( json_encode( array('type'=>'error', 'message'=>'token error' . config_item('snzsxh_sync_token') . '-' . $info['shzh_sync_token']) ) );
		}
		unset( $info['shzh_sync_token'] );

		// 记录是否短信发送
		$send_mobile_message = 0;
		if( isset( $info['send_mobile_message'] ) && $info['send_mobile_message'] == 1 )
		{
			$send_mobile_message = 1;
			unset( $info['send_mobile_message'] );
		}

		$result = array();
		
		$this->load->model('multi/ruhui_model');
		try
		{
			$id = $this->ruhui_model->add( $info );
			$result['type'] = 'success';
			// echo( json_encode( array('type'=>'success') ) );
		}
		catch(Exception $e)
		{
			$result['type'] = 'error';
			$result['message'] = $e->getMessage();
			// echo( json_encode( array('type'=>'error', 'message'=>$e->getMessage()) ) );
		}

		// 短信发送
		if( $send_mobile_message == 1 && $result['type'] == 'success' )
		{
			$this->load->library('send_message');
			$this->load->model('sms_model');
			if( preg_match('/^1[3-9]\d{9}$/', $info['mobile']) )
			{
				$content = '你好'. $info['name'] .'你已经申请提交入会和网络监督单位信息，请与跟单客服戴先生联系手机号13501886065【上海装潢网】';
				//$content = '你好'. $info['name'] .'你已经申请提交入会和网络监督单位信息，请跟跟单人装潢网客服联系戴先生手机号13501886065【上海装潢网】';
				$_arr = array(
					'tid'				=> $id,
					'message'			=> $content,
					'category'			=> 'ruhui',
					'addtime'			=> date('Y-m-d H:i:s'),
					'mobile'			=> $info['mobile']
				);
				
				// 安全检测
				// 每小时只发送一次
				// 每天最多3条
				if( $this->sms_model->check_resend_limit($_arr['mobile'], 3600) && $this->sms_model->check_send_count($_arr['mobile'], 86400, 3) )
				{
					if( $this->send_message->send($content, $info['mobile']) )
					{
						$this->sms_model->add( $_arr );
					}
				}


			}
			// unset( $info['send_mobile_message'] );
		}

		json_echo( $result );
		
	}
	
}






?>