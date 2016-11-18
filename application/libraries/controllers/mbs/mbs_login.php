<?php

// 登录
class mbs_login extends mbs_base {

	public function __construct(){
		// 不检测用户登录
		parent::__construct(array(
			'check_login'=>false
		));
	}
	
	public function home(){
		$this->tpl->display('mbs/login.html');
	}

	//public function tem(){
	//	$this->tpl->display('mbs/login2.html');
	//}

	public function get_validate_code(){
		$this->load->library('kaocode');

		if( ! isset($_SESSION) ) session_start();

		$this->kaocode->doimg();
		$_SESSION['mbs_login_validate'] = $this->kaocode->getCode();
	}
	
	// 登录提交
	public function submit(){
		$object = array(
			'page_id'=>$this->gf('page_id'),
			'pswd'=>$this->gf('pswd'),
			'validate'=>$this->gf('validate')
		);

		$url = $this->get_mbs_complete_url('/login');

		if( $object['page_id'] == '' || $object['pswd'] == '' ){
			$this->alert('提交的数据不完整', $url);
			exit();
		}

		if( ! preg_match('/^\d+$/', $object['page_id']) ){
			$this->alert('微网页ID格式错误，必须是数字', $url);
			exit();
		}

		if( ! $this->_validate_check($object['validate']) ){
			$this->alert('验证码错误', $url);
			exit();
		}

		$this->load->model('mobile/mbs_model');

		$res = $this->mbs_model->login($object);

		if( $res['type'] == 'success' ){
			$this->alert('', $this->get_mbs_complete_url('/main'));
			exit();
		} else {
			$this->alert($res['message'], $url);
			exit();
		}

	}

	public function validate_check(){
		$validate = $this->gf('validate');
		echo( $this->_validate_check($validate) ? '1' : '0' );
	}

	private function _validate_check($code){
		if( ! isset($_SESSION) ) session_start();
		return strtolower(trim($code)) == strtolower($_SESSION['mbs_login_validate']);
	}


}


?>