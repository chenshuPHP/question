<?php

// sv = supervision
// 监理入职程序
class multi_jianliruzhi extends multi_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'ruzhi');
		$this->load->model('multi/sv_ruzhi_model');	// 监理入职模型
	}
	
	public function home(){
		
		$this->tpl->assign('gl', $this->sv_ruzhi_model->gl);
		
		// 入职列表
		$list = $this->sv_ruzhi_model->get_list("select top 6 id, name, tel, sheng, city, town, gl, addtime from [send_sv_ruzhi] order by addtime desc");
		$list['list'] = $this->sv_ruzhi_model->gl_assign($list['list']);
		
		$this->tpl->assign('list', $list['list']);
		$this->tpl->display('multi/supervision/jianliruzhi.html');
	}
	
	public function get_validate(){
		
		session_start();
		$this->load->library('kaocode');
		
		$this->kaocode->doimg();
		
		$_SESSION['sv_ruzhi_validate'] = $this->kaocode->getCode();
		
	}
	
	public function handler(){
		
		$info = $this->get_form_data();
		
		if( empty($info['name']) || empty($info['tel']) ){
			$this->alert('称呼，电话不能为空');
			exit();
		}
		
		if( ! $this->_check_validate($info['validate']) ){
			$this->alert('验证码错误');
			exit();
		}
		
		try{
			$this->sv_ruzhi_model->add($info);			// 添加监理 入职
			$this->alert('提交成功', $this->multi_info_model->get_complete_url('jianliruzhi'));
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
		
	}
	
	private function _check_validate($code){
		session_start();
		return strtolower($code) == strtolower($_SESSION['sv_ruzhi_validate']);
	}
	
	// ajax 发送验证码验证请求，正确输出1 错误 输出0
	public function check_validate(){
		echo( $this->_check_validate( $this->gf('code') ) ? 1 : 0 );
	}
	
}

?>