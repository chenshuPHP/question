<?php

class reg extends MY_Controller {
	
	public function __construct(){parent::__construct();
		
	}
	
	public function deco(){
		
		$tpl = 'reg/ui_2016.html';
		
		// 客服代表
		$this->load->model('manager/manager_model');
		$admin_user = $this->gr('aid');
		$admin = $this->manager_model->get_manager($admin_user);
		$this->tpl->assign('admin', $admin);
		
		$this->load->model('login/thirdparty_login_model');
		$this->thirdparty_login_model->set_type('qq');
		$this->tpl->assign('qq_login_link', $this->thirdparty_login_model->get_authorization_code_url());
		$this->thirdparty_login_model->set_type('weibo');
		$this->tpl->assign('weibo_login_link', $this->thirdparty_login_model->get_authorize_url());
		
		
		$this->load->model('company/company_config');
		$this->tpl->assign('biz_types', $this->company_config->get_biz_type());
		
		$this->tpl->assign('title', '用户注册');
		$this->tpl->display($tpl);
		
	}
	
	// 装修公司注册界面
	private function _deco(){
		
		// 临时跳转到新注册
		// 2015-03-16
		
		//if( $this->gr('aid') == '' ){
		//	$this->alert('', '/register/deco');
		//} else {
			
			$this->load->model('manager/manager_model');
			
			$admin_user = $this->gr('aid');
			
			$admin = $this->manager_model->get_manager($admin_user);
			
			$this->tpl->assign('admin', $admin);
			
			$this->tpl->display('reg/deco.html');
		//}
		
	}
	
	// 装修公司注册提交处理
	public function deco_submit(){
		
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
		$object['admin'] = $this->encode->getFormEncode('admin');
		
		
		$object['biz_type'] = $this->gf('biz_type');		// 公司类型
		
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
			$this->tpl->display('reg/deco.html');
		} else {
			// 返回新插入的记录ID
			$res = $this->login->register( $object );
			echo('<script type="text/javascript">alert("注册成功！");location.href="/member/main.asp";</script>');
		}
	}
	
	
	// 设计师注册界面
	public function designer(){
		$this->load->model('sjs/info', 'sjs_info_model');
		$this->tpl->assign('adepts', $this->sjs_info_model->get_adepts());
		$this->tpl->display('reg/designer.html');
	}
	
	// 设计师注册表单提交
	public function designer_submit(){
		// == 注册信息获取
		$object = array();
		$object['username'] = $this->encode->getFormEncode('username');
		$object['password'] = $this->encode->getFormEncode('password');
		$object['password2'] = $this->encode->getFormEncode('password2');
		$object['email'] = $this->encode->getFormEncode('email');
		$object['shen'] = $this->encode->getFormEncode('User_Shen');
		$object['city'] = $this->encode->getFormEncode('User_City');
		$object['town'] = $this->encode->getFormEncode('User_Town');
		$object['adept'] = $this->encode->getFormEncode('adept');
		$this->_designer_register_handler($object);
	}
	
	// 设计师注册 - 简单注册
	// sjs.shzh.net/register
	public function sjs_register(){
		
		$info = array();
		$info['mobile'] = $this->gf('mobile');
		$info['username'] = $this->gf('username');
		$info['password'] = $this->gf('password');
		$info['password2'] = $info['password'];
		$info['shen'] = $this->gf('sheng');
		$info['city'] = $this->gf('city');
		$info['town'] = $this->gf('town');
		$info['adept'] = $this->gf('adept');
		//$info['email'] = '';
		
		//var_dump($info);
		$this->_designer_register_handler($info);
		
	}
	
	// 设计师注册处理
	private function _designer_register_handler($object){
		
		$this->load->model('LoginModel', 'login');
		
		// == 数据安全验证
		$errs = array();
		if( $object['username'] == '' ){
			$errs['username'] = '用户名不能为空';
		} else {
			if( ! preg_match('/^[a-zA-Z0-9_]+$/', $object['username']) ){
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
		
		if( isset( $object['email'] ) ){
			if( !preg_match('/^\w+([-+.]\w+)*@\w+([-.]\\w+)*\.\w+([-.]\w+)*$/', $object['email']) ){
				$errs['email'] = '邮箱格式不正确';
			}
		} else {
			$object['email'] = '';
		}
		
		if( $object['adept'] == '' ){
			$errs['adept'] = '您必须选择你擅长的专精';
		}
		
		if( count( $errs ) > 0 ){
			
			$this->alert(implode('\n', $errs));
			
		} else {
			// var_dump($object);
			// 返回新插入的记录ID
			try{
				
				// 设计师注册 2015-08-10
				$res_id = $this->login->register_design($object);
				$urls = $this->config->item('url');
				echo('<script type="text/javascript">alert("注册成功！");location.href="'. $urls['www'] .'/sjs/ucenter/info/edit";</script>');
				
			}catch(Exception $e){
				$this->alert( $e->getMessage() );
			}
		}
		
		
	}
	
	
	
	// 建材商 注册界面
	public function busi(){
		$this->tpl->display('reg/busi.html');
	}
	
	// 建材商注册表单提交
	public function busi_submit(){
		
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
		$object['hangye'] = $this->encode->getFormEncode('hangye');
		$object['tel'] = $this->encode->getFormEncode('tel');		// 电话 / 手机
		
		$errs = array();
		
		$this->load->model('LoginModel', 'login_model');
		
		if( $object['username'] == '' ){
			$errs['username'] = '用户名不能为空';
		} else {
			if( !preg_match('/^[a-zA-Z0-9_]+$/', $object['username']) ){
				$errs['username'] = '用户名格式不正确';
			}
			if( !isset($errs['username']) ){
				$username_exists = $this->login_model->check_username_exists($object['username']);
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
			$this->tpl->display('reg/busi.html');
		} else {
			// 返回新插入的记录ID
			$res = $this->login_model->register_busi( $object );
			echo('<script type="text/javascript">alert("注册成功！");location.href="/member/main.asp";</script>');
		}
	}

	// 个人注册
	public function persion_regist(){
		
		$info = $this->get_form_data();
		
		if( $info['xieyi'] != 1 ){
			$this->alert('请同意协议内容');
			exit();
		}
		
		if( ! preg_match('/^1\d{10}$/', $info['mobile']) ){
			$this->alert('手机号码格式错误');
			exit();
		}
		
		if( $info['username'] == '' || $info['password'] == '' ){
			$this->alert('请输入用户名和密码');
			exit();
		}
		
		$this->load->model('LoginModel', 'login_model');
		
		if( $this->login_model->check_username_exists($info['username']) ){
			$this->alert('用户名已经存在');
			exit();
		}
		
		try{
			
			$this->login_model->persion_regist($info);
			
			$urls = $this->config->item('url');
			
			$this->alert('注册成功', $urls['sjs']);
			
		}catch(Exception $e){
			throw new Exception( $e->getMessage() );
		}
		
		
	}



}






























?>