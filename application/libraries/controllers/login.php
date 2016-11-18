<?php

class login extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->registerPlugin('modifier', 'mobile_login_url', array($this, 'get_mobile_login_url'));
	}
	
	public function _remap($class, $params = array())
	{
		$this->route($class, $params, 'login');
	}
	
	// 手机登录相关请求网址生成
	public function get_mobile_login_url($url = '')
	{
		$urls = config_item('url');
		return rtrim($urls['www'], '/') . '/login/mobile/' . ltrim($url, '/');
	}
	
	// 2016 登录界面
	// 2016-07-11 新增登录提交处理
	public function index( $tpl = '' ){
		
		if( empty($tpl) ) $tpl = 'login/home.html';
		
		
		// 提交处理
		$result = FALSE;
		
		if( $this->gf('username') !== '' )
		{
			$result = $this->basic_submit();
			if( $result['type'] === 'success' )
			{
				$rurl = $this->gf('rurl');
				if( empty( $rurl ) ) $rurl = '/member/main.asp';
				echo('<script type="text/javascript">location.href="'. $rurl .'";</script>');
				exit();
			}
			else
			{
				$this->tpl->assign('result', $result);
			}
		}
		
		$this->load->model('login/thirdparty_login_model');
		$this->tpl->assign('title', '用户登录');
		
		$this->thirdparty_login_model->set_type('qq');
		$this->tpl->assign('qq_login_link', $this->thirdparty_login_model->get_authorization_code_url());
		$this->thirdparty_login_model->set_type('weibo');
		$this->tpl->assign('weibo_login_link', $this->thirdparty_login_model->get_authorize_url());
		
		$this->tpl->assign('rurl', $this->gr('rurl'));
		
		$this->tpl->display($tpl);
	}
	
	// 2016-09-22 快捷登陆, 在其他页面的弹窗登录
	public function quick()
	{
		$this->index( 'login/quick.html' );
	}
	
	
	// 基本的登录操作
	function basic_submit(){
		
		$object = array();
		$object['user'] = $this->gf('username');
		$object['pass'] = $this->gf('password');
		
		$this->load->model('loginModel', 'login');
		$res = $this->login->login($object);
		
		$result = array();
		
		if( $res === true ){
			$result['type'] = 'success';
		} else {
			$result['type'] = 'error';
			$result['message'] = '用户名或密码输入错误';
		}
		return $result;
	}
	
	// ajax 登录
	public function login_ajax(){
		$this->load->library('encode');
		$object = array();
		$object['user'] = $this->encode->getFormEncode('username');
		$object['pass'] = $this->encode->getFormEncode('password');
		
		// 支持单点登录 返回 token
		$_type = $this->gf('type');
		
		$this->load->model('loginModel', 'login');
		
		if( $_type == '' )
		{
			$result = $this->login->login($object);
			// 支持 ajax json 数据返回
			$ajax = $this->gf('ajax');
			if( $result == true ){
				echo $ajax == 1 ? json_encode(array('type'=>'success')) : '{error:0}';
			} else {
				echo $ajax == 1 ? json_encode(array('type'=>'error', 'error'=>'用户名或密码错误')) : '{error:1, info:"用户名或密码错误"}';
			}
		}
		elseif ( $_type == 'token' )
		{
			$object['device'] = $this->gf('device');
			$token = $this->login->login($object, array(
				'token'		=> TRUE
			));
			if( $token === FALSE )
			{
				json_echo(array(
					'type'		=> 'error',
					'error'		=> '登录失败,请检查用户名密码'
				));
			}
			else
			{
				json_echo(array(
					'type'		=> 'success',
					'token'		=> $token
				));
			}
		}
		
	}
	
	// 检测用户名是否重复
	function check_username_exists(){
		$username = $_POST['username'];
		$this->load->model('LoginModel', 'login_model');
		$res = $this->login_model->check_username_exists( $username );
		
		if( $res === true ){
			echo('1');
		} else {
			echo('0');
		}
	}
	
	// 退出登录
	public function logout(){
		$this->load->model('LoginModel', 'login_model');
		$this->login_model->logout();
		
		$rurl = $this->gr('r');
		if( empty($rurl) ){
			$rurl = $_SERVER['HTTP_REFERER'];
		}
		
		echo('<script>location.href="'. $rurl .'";</script>');
		
	}
	
	
	/*
	// 用户注册界面 ( 装修公司 )
	function reg(){
		$this->tpl->display('login/reg.html');
	}
	
	// 设计师注册
	function reg_design(){
		$this->tpl->display('login/reg_design.html');
	}
	
	// 注册提交
	function basic_regist(){
		$this->load->library('encode');
		
		$object = array();
		$object['username'] = $this->encode->getFormEncode('username');
		$object['password'] = $this->encode->getFormEncode('password');
		$object['password2'] = $this->encode->getFormEncode('password2');
		$object['email'] = $this->encode->getFormEncode('email');
		$object['shen'] = $this->encode->getFormEncode('User_Shen');
		$object['city'] = $this->encode->getFormEncode('User_City');
		$object['town'] = $this->encode->getFormEncode('User_Town');
		$object['company'] = $this->encode->getFormEncode('company');
		$object['rejion'] = $this->encode->getFormEncode('rejion');
		$object['sex'] = $this->encode->getFormEncode('sex');
		$object['hangye'] = $this->encode->getFormEncode('hangye');
		
		$object['tel'] = $this->encode->getFormEncode('tel');		// 电话 / 手机
		
		$errs = array();
		
		$this->load->model('loginModel', 'login');
		
		if( $object['username'] == '' ){
			$errs['username'] = '用户名不能为空';
		} else {
			if( !preg_match('/^[a-zA-Z0-9_]+$/', $object['username']) ){
				$errs['username'] = '用户名格式不正确';
			}
			if( !isset($errs['username']) ){
				$username_exists = $this->login->check_username_exists($object['username']);
				if( $username_exists == true ){
					$errs['username'] = '用户名已经存在';
				}
			}
		}
		if( $object['password'] == '' ){
			$errs['password'] = '密码不能为空';
		}
		if( $object['password'] !== $object['password2'] ){
			$errs['password2'] = '两次密码输入不一致';
		}
		if( $object['tel'] == '' ){
			$errs['tel'] = '联系电话不能为空';
		}
		if( !preg_match('/^\w+([-+.]\w+)*@\w+([-.]\\w+)*\.\w+([-.]\w+)*$/', $object['email']) ){
			$errs['email'] = '邮箱格式不正确';
		}
		
		
		if( count( $errs ) > 0 ){
			$this->tpl->assign('errors', $errs);
			$this->tpl->assign('object', $object);
			$this->tpl->display('login/reg.html');
		} else {
			// 返回新插入的记录ID
			$res = $this->login->register( $object );
			echo('<script type="text/javascript">alert("注册成功！");location.href="/member/main.asp";</script>');
		}
	}
	
	// 设计师注册表单提交
	function design_register_submit(){
		$this->load->library('encode');
		
		$object = array();
		$object['username'] = $this->encode->getFormEncode('username');
		$object['password'] = $this->encode->getFormEncode('password');
		$object['password2'] = $this->encode->getFormEncode('password2');
		$object['email'] = $this->encode->getFormEncode('email');
		$object['shen'] = $this->encode->getFormEncode('User_Shen');
		$object['city'] = $this->encode->getFormEncode('User_City');
		$object['town'] = $this->encode->getFormEncode('User_Town');
		
		$errs = array();
		
		$this->load->model('loginModel', 'login');
		
		if( $object['username'] == '' ){
			$errs['username'] = '用户名不能为空';
		} else {
			if( !preg_match('/^[a-zA-Z0-9_]+$/', $object['username']) ){
				$errs['username'] = '用户名格式不正确';
			}
			if( !isset($errs['username']) ){
				$username_exists = $this->login->check_username_exists($object['username']);
				if( $username_exists == true ){
					$errs['username'] = '用户名已经存在';
				}
			}
		}
		if( $object['password'] == '' ){
			$errs['password'] = '密码不能为空';
		}
		if( $object['password'] !== $object['password2'] ){
			$errs['password2'] = '两次密码输入不一致';
		}
		if( !preg_match('/^\w+([-+.]\w+)*@\w+([-.]\\w+)*\.\w+([-.]\w+)*$/', $object['email']) ){
			$errs['email'] = '邮箱格式不正确';
		}
		
		if( count( $errs ) > 0 ){
			$this->tpl->assign('errors', $errs);
			$this->tpl->assign('object', $object);
			$this->tpl->display('login/reg_design.html');
		} else {
			
			// var_dump($object);
			// 返回新插入的记录ID
			$res_id = $this->login->register_design($object);
			echo('<script type="text/javascript">alert("注册成功！");location.href="/member_design/index.html";</script>');
		}
	}
	*/
}

?>