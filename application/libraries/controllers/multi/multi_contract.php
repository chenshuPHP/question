<?php

// 装修合同
class multi_contract extends multi_base {
	
	private $_validate_name = 'contract_dl_validate';		// 下载验证码名称
	private $_dl_auth_name = 'contract_dl_auth';			// 下载权限名称
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		$this->load->library('encode');
		$this->load->model('multi/contract_model');
		$args = array(
			'version'=>$this->gr('v'),
			'page'=>$this->gr('p')
		);
		
		if( empty( $args['version'] ) ) $args['version'] = '2015';
		if( empty( $args['page'] ) ) $args['page'] = 1;
		
		$data = $this->contract_model->get_version_data($args['version']);
		
		$list = $this->contract_model->gets();
		
		$this->tpl->assign('data', $data);
		$this->tpl->assign('args', $args);
		
		$this->tpl->assign('list', $list);
		
		$this->tpl->display('multi/contract.html');
	}
	
	// 合同下载页面
	public function download(){
		
		session_start();
		
		$version = $this->gr('v');		// 下载的版本号
		$this->load->model('multi/contract_model');
		$data = $this->contract_model->get_version_data($version);
		if( ! $data ) exit('没有找到该版本的合同文件');
		// 下载滚动数据
		$this->load->model('download/res_download_model');
		$list = $this->res_download_model->custom_gets("select top 12 name, mobile, addtime from [budget_download] where addtime in ( select max(addtime) from budget_download group by mobile ) order by addtime desc");
		$list = $this->res_download_model->format2($list);
		$this->tpl->assign('list', $list);
		$this->tpl->assign('data', $data);
		// 载入下载选项配置
		// 2015-07-30
		$this->load->model('download/res_download_config_model');
		$this->tpl->assign('user_type', $this->res_download_config_model->user_type);
		$tpl = 'multi/contract_download.html';
		
		if( ! $this->_check_dl_auth() ){
			$this->tpl->assign('auth', 0);
		} else {
			$this->tpl->assign('auth', 1);
		}
		
		$this->tpl->display( $tpl );
	}
	
	
	private function _check_validate($code){
		session_start();
		if( empty( $code ) || empty( $_SESSION['download_mobile_validate'] ) ) return false;
		return strtolower( $code ) == strtolower( $_SESSION['download_mobile_validate'] );
	}
	
	private function _clear_validate(){
		if( ! isset( $_SESSION ) ) session_start();
		$_SESSION['download_mobile_validate'] = '';
		unset( $_SESSION['download_mobile_validate'] );
	}
	
	public function mobile_validate(){
		session_start();
		$this->load->library('kaocode');
		$this->kaocode->doImg();
		$_SESSION['download_mobile_validate'] = $this->kaocode->getCode();
	}
	
	// 提交处理
	public function download_handler(){
		
		$info = $this->get_form_data();
		if( empty( $info['name'] ) ) exit('名称不能为空');
		if( ! preg_match('/^(1\d{10})|(\d{2,4}(\-\d+)+)|(\d{7,12})$/', $info['mobile']) ) exit('电话格式错误');
		if( ! $this->_check_validate($info['validate']) ){
			$this->_clear_validate();
			exit('验证码错误');
		}
		
		$this->load->model('multi/contract_model');
		
		try{
			// 添加下载记录
			$this->contract_model->download_record(array(
				'tid'=>$info['version'],
				'name'=>$info['name'],
				'mobile'=>$info['mobile'],
				'user_type'=>$info['user_type']
			));
			// 创建下载标识
			$this->_create_dl_auth($info);
			// 进入下载页面
			$this->alert('', $this->get_complete_url('multi/contract/download?v=' . $info['version']));
		}catch(Exception $e){
			$this->alert( $e->getMessage() );
		}
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
	
	// 验证码 ajax
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