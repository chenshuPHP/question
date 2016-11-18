<?php

// 装修公司注册
class register_deco extends register_base {

	public function __construct(){
		parent::__construct();
		$this->tpl->assign('hide_kf', '1');
		$this->tpl->assign('hide_bdshare', '1');
	}
	
	// 装修公司 注册
	public function home(){
		
		/* 2016-04-27 不再使用短信注册 
		if( ($aid = $this->gr('aid')) == '' ){
			$this->load->model('company/company_config');
			$this->tpl->assign('intention', $this->company_config->get_intention());	// 合作意向
			$this->tpl->display('reg/register_deco.html');
		} else {
			$this->alert('', '/reg/deco?aid=' . $aid);
		}
		*/
		
		$this->alert('', '/reg/deco');
		
	}
	
	// 注册信息提交处理
	public function handler(){
		$info = array(
			'mobile'=>$this->gf('mobile'),
			'mobile_validate_code'=>$this->gf('mobile_check_code'),
			'deco_name'=>$this->gf('deco_name'),
			'pswd'=>$this->gf('pswd'),
			'pswd2'=>$this->gf('pswd2'),
			'intention'=>$this->gf('intention'),
			'agreement'=>$this->gf('agreement')
		);
		
		if( ! preg_match('/^1[3-8]\d{9}$/', $info['mobile']) ){
			exit('手机号码格式错误');
		}
		
		if( ! preg_match( '/^\d{6}$/', $info['mobile_validate_code'] ) ){
			exit('验证码格式错误');
		}

		if( $this->encode->utf8_strlen( $info['pswd'] ) < 6 ){
			exit('密码至少6个字符长度');
		}

		if( $info['pswd'] != $info['pswd2'] ){
			exit('两次密码不一致');
		}
		
		if( count( $info['intention'] ) == 0 ){
			exit('请选择合作意向');
		} else {
			$info['intention'] = implode(',', $info['intention']);
		}

		if( $info['agreement'] != 1 ){
			exit('请同意上海装潢网《服务协议》');
		}
		
		// 验证手机号码是否已经注册
		$this->load->model('company/company', 'deco_model');
		$deco = $this->deco_model->get_company($info['mobile'], 'id');
		if( $deco != false ){
			exit('手机号码已经被注册');
		}

		// 验证短信验证码是否正确
		$this->load->model('sms_model');
		$sms = $this->sms_model->get_sms($info['mobile'], 'register');
		if( ! $sms ){
			exit('找不到短信验证码');
		} else {
			if( $sms['validate_code'] != $info['mobile_validate_code'] ){
				exit('验证码错误');
			}
		}
		
		// 注册信息写入
		$this->load->model('LoginModel', 'login_model');
		
		try{
			$this->login_model->register(array(
				'username'=>$info['mobile'],
				'password'=>$info['pswd'],
				'company'=>$info['deco_name'],
				'mobile'=>$info['mobile'],
				'rejion'=>'',
				'sex'=>'',
				'email'=>'',
				'hangye'=>'装潢公司',
				'register'=>2,
				'flag'=>1,
				'puttime'=>date('Y-m-d H:i:s'),
				'delcode'=>0,
				'shen'=>'',
				'city'=>'',
				'town'=>'',
				'tel'=>$info['mobile'],
				'intention'=>$info['intention']		// 合作意向
			));

			$this->alert('注册成功', '/member/main.asp');

		}catch(Exception $e){
			exit($e->getMessage());
		}

	}


}

?>