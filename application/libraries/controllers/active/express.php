<?php

if( ! defined('BASEPATH') ) exit('无法直接浏览');

// 2015-11-12
// 快速提交控件
class express extends MY_Controller {
	
	private $urls;
	
	public function __construct(){
		parent::__construct();
		$this->urls = $this->config->item('url');
	}
	
	private function display(){
		$this->tpl->display('active/express/home.html');
	}
	
	private function _validate_check($code){
		session_start();
		return strtolower($code) == strtolower( $_SESSION['code'] );
	}
	
	public function validate(){
		session_start();
		$this->load->library('kaocode');
		$this->kaocode->doimg();
		$_SESSION['code'] = $this->kaocode->getCode();
	}
	
	public function validate_check(){
		$code = $this->gf('code');
		echo( $this->_validate_check($code) ? 1 : 0 );
	}
	
	public function handler(){
		
		$info = $this->get_form_data();
		$info['addtime'] = date('Y-m-d H:i:s');
		
		if( ! $this->_validate_check($info['validate']) ){
			exit('验证码错误');
		}
		
		if( empty($info['true_name']) || empty($info['tel']) ){
			exit('称呼和电话不能为空');
		}
		
		unset($info['validate']);
		
		try{
			$this->load->model('publish/pubModel', 'pub_model');
			$this->pub_model->express_pipe_add($info);
			echo('success');
		}catch(Exception $e){
			exit( $e->getMessage() );
		}
		
		
	}
	
	public function baike(){
		
		$config = array(
			'url'=>$this->urls['article'] . 'home',
			'name'=>'装修百科首页',
			'txts'=>array('免费设计3套方案，供您选择', '3份专业报价，让您快速了解装修所需费用')
		);
		
		$this->tpl->assign('config', $config);
		
		$this->display();
		
	}
	
	public function baike_detail(){
		
		
		$config = array(
			'url'=>$this->gr('url'),
			'name'=>'正文页',
			'txts'=>array('免费设计3套方案，供您选择', '3份专业报价，让您快速了解装修费用')
		);
		
		$this->tpl->assign('config', $config);
		
		$this->display();

	}
	
	public function diary(){
		$config = array(
			'url'=>$this->gr('url'),
			'name'=>'监理日记页',
			'txts'=>array('免费设计3套方案，供您选择', '3份专业报价，让您快速了解装修所需费用'),
			'tp'=>'diary'
		);
		$this->tpl->assign('config', $config);
		$this->display();
	}

	public function tuku(){
		$config = array(
			'url'=>$this->gr('url'),
			'name'=>'装修图库详情',
			'txts'=>array('免费设计3套方案，供您选择', '3份专业报价，让您快速了解装修所需费用')
		);
		$this->tpl->assign('config', $config);
		$this->display();
	}



















}



?>