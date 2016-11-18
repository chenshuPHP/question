<?php

// 快速预约类
// 支持验证码，不同页面验证码的名字也不同

class publish_express extends publish_base {

	public function __construct(){
		parent::__construct();
	}
	
	public function validate(){
		$name = strtolower( $this->gr('n') );
		session_start();
		$this->load->library('kaocode');
		$this->kaocode->doImg();
		$_SESSION[$name] = $this->kaocode->getCode();
	}
	
	private function _check_validate($code, $name){
		session_start();
		if( $code == '' || $_SESSION[$name] == '' ) return false;
		return strtolower( $code ) == strtolower( $_SESSION[$name] );
	}
	
	private function _clear_validate($name){
		if( ! isset( $_SESSION ) ) session_start();
		$_SESSION[$name] = '';
		unset( $_SESSION[$name] );
	}
	
	// ajax 验证码检测
	// 需要post两个参数，第一个为验证码值['validate']，第二个为验证码名称['n']
	public function check_validate(){
		$v = $this->gf('validate');
		$name = $this->gf('n');
		if( ! $this->_check_validate($v, $name) ){
			echo(0);
		} else {
			echo(1);
		}
	}
	
	public function home_appoint(){
		
		$info = $this->get_form_data();
		
		// vn 为验证码的名称
		$vn = $info['vn'];
		
		if( ! $this->_check_validate($info['validate'], $info['vn']) ){
			exit('验证码错误');
		}
		$urls = $this->config->item('url');
		$info['rel'] = '{"url":"'. $urls['www'] .'", "name":"首页五大保障"}';
		$info['addtime'] = date('Y-m-d H:i:s');
		
		unset($info['validate']);
		unset($info['vn']);
		
		if( empty($info['true_name']) || empty($info['tel']) ){
			exit('称呼和电话不能为空');
		}
		
		try{
			$this->load->model('publish/pubModel', 'pub_model');
			$this->pub_model->express_pipe_add($info);
			
			$this->_clear_validate($vn);	// 清除验证码
			
			echo('success');
		}catch(Exception $e){
			exit( $e->getMessage() );
		}
		
	}
	
}


?>