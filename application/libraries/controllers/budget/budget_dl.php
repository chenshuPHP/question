<?php

// 预算下载处理
class budget_dl extends budget_base {
	
	private $_validate_name = 'budget_dl_validate';		// 下载验证码名称
	private $_dl_auth_name = 'budget_dl_auth';			// 下载权限名称
	
	public function __construct(){
		parent::__construct();
		
		if( ! isset( $_SESSION ) ) session_start();
		
	}
	
	// 下载主页
	public function home(){
		
		$budget_id = $this->gr('id');
		
		// 如果没有输入过手机号码 就输出一个验证表单
		$tpl = 'budget/budget_dl.html';
		if( ! $this->_check_dl_auth() ){
			
			$this->tpl->assign('auth', 0);
			$this->tpl->assign('budget_id', $budget_id);
			
		} else {
			
			$this->tpl->assign('auth', 1);
			// 输出文件路径
			$this->load->model('budget/budget_model');
			$budget = $this->budget_model->get_budget($budget_id, array(
				'fields'=>'id, attach'
			));
			if( isset( $budget['attach_url'] ) && ! empty( $budget['attach_url'] ) ){
				$this->tpl->assign('budget', $budget);
			}
		}
		$this->tpl->display($tpl);
	}
	
	// 表单提交
	public function handler(){
		$info = $this->get_form_data();
		if( $info['name'] == '' ) exit('请输入称呼');
		if( ! preg_match('/^(1\d{10})|(\d{2,4}(\-\d+)+)|(\d{7,12})$/', $info['mobile']) ) exit('电话格式错误');
		if( ! $this->_check_validate($info['validate']) ){
			$this->_clear_validate();
			exit('验证码错误');
		}
		
		$this->load->model('budget/budget_model');
		
		try{
			// 添加下载记录
			$this->budget_model->download_record(array(
				'bid'=>$info['budget_id'],
				'name'=>$info['name'],
				'mobile'=>$info['mobile']
			));
			// 创建下载标识
			$this->_create_dl_auth($info);
			// 进入下载页面
			$this->alert('', $this->get_complete_url('budget/dl?id=' . $info['budget_id']));
		}catch(Exception $e){
			$this->alert( $e->getMessage() );
		}
		
	}
	
	private function _check_validate($code){
		if( ! isset( $_SESSION[$this->_validate_name] ) ) return false;
		if( empty( $code ) || empty( $_SESSION[$this->_validate_name] ) ) return false;
		return strtolower( $code ) == strtolower( $_SESSION[$this->_validate_name] );
	}
	
	private function _clear_validate(){
		unset( $_SESSION[$this->_validate_name] );
	}
	
	// 创建下载身份
	private function _create_dl_auth($info){
		$_SESSION[$this->_dl_auth_name] = array(
			'mobile'=>$info['mobile'],
			'name'=>$info['name']
		);
	}
	
	// 检测下载身份
	private function _check_dl_auth(){
		if( ! isset( $_SESSION[$this->_dl_auth_name] ) ) return false;
		if( empty( $_SESSION[$this->_dl_auth_name]['mobile'] ) || empty( $_SESSION[$this->_dl_auth_name]['name'] ) ) return false;
		return true;
	}
	
	// 显示图片验证码
	public function image_validate(){
		$this->load->library('kaocode');
		$this->kaocode->doimg();
		$_SESSION[$this->_validate_name] = $this->kaocode->getCode();
	}
	
	// 提供ajax检测验证码
	public function check_validate(){
		$code = $this->gf('code');
		$result = NULL;
		if( ! $this->_check_validate($code) ){
			$result = array('type'=>'error', 'message'=>'验证码错误');
		} else {
			$result = array('type'=>'success');
		}
		echo(json_encode($result));
	}
	
}



?>