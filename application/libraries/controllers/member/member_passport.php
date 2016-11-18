<?php if(!defined('BASEPATH')) exit('~/controllers/member/member_passport.php');

class member_passport extends member_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 装修公司基本信息修改界面
	public function deco_info(){
		
		$this->load->model('company/company_config', 'deco_config_model');
		
		$object = $this->deco_model->getCompany( $this->login['username'] );
		
		$object['content'] = $this->encode->removeHtml( $object['content'] );
		
		$this->tpl->assign('object', $object);
		$this->tpl->assign('attrs', $this->deco_config_model->get_register_property());
		$this->tpl->assign('biz_type', $this->deco_config_model->get_biz_type());
		$this->tpl->assign('module', 'passport.info');
		$this->tpl->display( $this->get_tpl('passport/deco_info.html') );
	}
	
	// 装修公司基本信息修改 表单提交
	public function deco_info_submit(){
		$this->load->library('encode');
		$object = array();
		$object['username'] = $this->encode->getFormEncode('username');
		$object['cmp_name'] = $this->encode->getFormEncode('cmp_name');
		$object['cmp_short_name'] = $this->encode->getFormEncode('cmp_short_name');
		$object['cmp_set_date'] = $this->encode->getFormEncode('cmp_set_date');
		
		// 删除 公司性质 2015-05-20
		// $object['cmp_attr'] = $this->encode->getFormEncode('cmp_attr');
		
		// 添加协会编号 2015-05-20
		$object['sida_code'] = $this->encode->getFormEncode('sida_code');
		
		$object['logo_old'] = $this->encode->getFormEncode('logo_old');
		$object['logo_new'] = $this->encode->getFormEncode('logo_new');
		$object['face_old'] = $this->encode->getFormEncode('face_old');
		$object['face_new'] = $this->encode->getFormEncode('face_new');
		$object['rejion'] = $this->encode->getFormEncode('rejion');
		$object['sex'] = $this->encode->getFormEncode('sex');
		$object['rejion_job'] = $this->encode->getFormEncode('rejion_job');
		$object['email'] = $this->encode->getFormEncode('email');
		$object['mobile'] = $this->encode->getFormEncode('mobile');
		$object['tel'] = $this->encode->getFormEncode('tel');
		$object['fax'] = $this->encode->getFormEncode('fax');
		$object['qq'] = $this->encode->getFormEncode('qq');
		$object['website'] = $this->encode->getFormEncode('website');
		$object['user_sheng'] = $this->encode->getFormEncode('User_Shen');
		$object['user_city'] = $this->encode->getFormEncode('User_City');
		$object['user_town'] = $this->encode->getFormEncode('User_Town');
		$object['address'] = $this->encode->getFormEncode('address');
		
		// 2016-08-30 公司口号
		$object['slogen'] = $this->encode->getFormEncode('slogen');
		
		// 2016-09-26 业务类型
		$object['biz_type']		= $this->encode->getFormEncode('biz_type');
		
		// 2016-10-17 公司简介
		$object['content'] = $this->gf('detail');
		
		
		$this->load->model('company/company', 'deco_model');
		$this->deco_model->deco_info_edit($object);
		
		// 设置店铺更新日期
		$this->deco_model->company_update($this->base_user);
		
		// 增加口碑值
		$this->load->model('company/company_koubei_model');
		$this->company_koubei_model->infomation_degree( $this->base_user );
		
		echo('<script type="text/javascript">alert("资料修改完成");location.href="deco_info";</script>');
		
	}
	
	// 修改密码界面
	public function password_edit(){
		$this->tpl->assign('module', 'password_edit');
		$this->tpl->assign('r', urlencode($_SERVER['REQUEST_URI']));
		$this->tpl->assign('module', 'passport.password');
		$this->tpl->display('member/passport/password_edit.html');
	}
	
	// 修改密码提交处理
	public function password_edit_submit(){
		$info = array(
			'username' => $this->base_user,
			'original' => $this->encode->getFormEncode('original'),
			'password' => $this->encode->getFormEncode('password'),
			'password2' => $this->encode->getFormEncode('password2')
		);
		
		$r = $this->encode->getFormEncode('r');
		
		//var_dump($info);
		
		if( $info['original'] == '' ){
			exit('原始密码不能为空');
		}
		if( $info['password'] == '' ){
			exit('新密码不能为空');
		} else {
			if( $info['password'] != $info['password2'] ){
				exit('两次密码输入不一致');
			}
		}
		$this->load->model('company/company', 'deco_model');
		try{
			$this->deco_model->password_edit($info);
			
			// 店铺更新日期
			$this->deco_model->company_update($this->base_user);
			
			echo('<script type="text/javascript">alert("密码修改成功");location.href="'. $r .'";</script>');
		}catch(Exception $e){
			echo('<script type="text/javascript">alert("'. $e->getMessage() .'");location.href="'. $r .'";</script>');
		}
		
	}
	
	// 登出
	public function logout(){
		
		$this->load->model('loginModel', 'login_model');
		$this->login_model->logout();
		
		$urls = $this->config->item('url');
		$this->alert('', $urls['www'] . '/login.html');
	}
	
	
}

?>