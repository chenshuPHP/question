<?php

class bespeak_deco extends bespeak_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	
	public function home(){
		
	}
	
	// 验证码
	public function validate(){
		session_start();
		$this->load->library('kaocode');
		$this->kaocode->doimg();
		$_SESSION['bespeak_deco_validate'] = $this->kaocode->getCode();
	}
	
	
	// 挑选购物车中的装修公司
	public function filter(){
		
		$this->load->library('thumb');
		
		if( ! isset($_COOKIE['bespeak']) ) show_error('没有选择公司', 404);
		
		$users = $_COOKIE['bespeak'];
		
		if( empty($users) ) show_error('没有选择公司', 404);
		$users = explode(',', $users);
		
		$this->load->model('company/company');
		
		$decos = $this->company->get_list("select id, username, company, address, logo, user_shen, user_city, user_town, flag from company where username in ('". implode("','", $users) ."') order by koubei desc", '', true);
		
		$decos = $decos['list'];
		
		$this->thumb->setPathType(1);
		
		foreach($decos as $key=>$value){
			if( ! empty($value['logo']) ){
				$decos[$key]['thumb'] = $this->thumb->resize($value['logo'], 85, 80);
			} else {
				$decos[$key]['thumb'] = '';
			}
		}
		
		$this->tpl->assign('decos', $decos);
		$this->tpl->assign('title', '自定义预约');
		$this->tpl->display('bespeak/deco/filter.html');
		
	}
	
	public function choose_handler(){
		$this->load->library('encode');
		$info = array(
			'decos'=>$this->encode->getFormEncode('deco_username'),
			'name'=>$this->encode->getFormEncode('name'),
			'tel'=>$this->encode->getFormEncode('tel'),
			'area'=>$this->encode->getFormEncode('area'),
			'budget'=>$this->encode->getFormEncode('budget'),
			'sheng'=>$this->encode->getFormEncode('User_Shen'),
			'city'=>$this->encode->getFormEncode('User_City'),
			'town'=>$this->encode->getFormEncode('User_Town'),
			'address'=>$this->encode->getFormEncode('address'),
			'xiaoqu'=>$this->encode->getFormEncode('xiaoqu'),
			'category_b'=>$this->encode->getFormEncode('category_b'),
			'category_s'=>$this->encode->getFormEncode('category_s'),
			'addtime'=>date('Y-m-d H:i:s'),
			'validate_code'=>$this->encode->getFormEncode('validate_code')
		);
		
		$errors = array();
		
		if( ! $this->_check_validate_code($info['validate_code']) ){
			$errors[] = '验证码错误';
		}
		
		if( empty($info['decos']) ){
			$errors[] = '您没有选择装修公司';
		}
		
		if(empty($info['name'])){
			$errors[] = '请输入称呼';
		}
		
		if( empty($info['tel']) ){
			$errors[] = '请输入联系电话/手机';
		}
		
		if( empty($info['sheng']) || empty($info['city']) || empty($info['town']) ){
			$errors[] = '请输入省/市/县(区)';
		}
		
		if( count($errors) == 0 ){
			$this->load->model('bespeak/bespeak_model');
			$info['uid'] = $this->bespeak_model->bespeak_deco_add($info);
			$this->tpl->assign('info', $info);
		} else {
			$this->tpl->assign('errors', $errors);
		}
		$this->tpl->display('bespeak/deco/customer_info.html');
	}
	
	public function _check_validate_code($validate){
		session_start();
		$validate_server = $_SESSION['bespeak_deco_validate'];
		$validate_client = $validate;
		if( strtolower($validate_server) == strtolower($validate_client) ){
			return true;
		} else {
			return false;
		}
	}
	
	public function check_validate_code(){
		$this->load->library('encode');
		$validate_client = $this->encode->getFormEncode('validate');
		if( $this->_check_validate_code($validate_client) == true ){
			echo('1');
		} else {
			echo('0');
		}
	}
	
	
}

?>