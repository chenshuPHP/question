<?php

// 招标
class sjs_zhaobiao extends sjs_base {
	
	public $info = '';
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _remap($method_name){
		if( method_exists($this, $method_name) ){
			$this->$method_name();
		} else {
			$method_name = explode('-', $method_name);
			$name = array_shift($method_name);
			$this->$name($method_name); 
		}
	}
	
	private function _check_login(){
		$this->load->model('LoginModel', 'login_model');
		$this->info = $this->login_model->check_login();
	}
	
	// 招标列表
	public function home(){
		
		$this->load->model('sjs/sjs_zhaobiao_model');
		$this->load->model('sjs/sjs_zhaobiao_config_model');
		
		$sql = "select top 6 id, username, type, cate, pay_type, score_value, cash_value, fm, time_limit from sjs_zhaobiao where recycle = 0 and fm <> '' order by addtime desc";
		
		$result = $this->sjs_zhaobiao_model->gets($sql, '', array(
			'format'=>true
		));
		
		$result['list'] = $this->sjs_zhaobiao_config_model->assign_types($result['list']);
		$result['list'] = $this->sjs_zhaobiao_config_model->assign_pay_types($result['list']);
		
		$this->tpl->assign('list', $result['list']);
		$this->tpl->display( 'sjs/zhaobiao/home.html' );
		
	}
	
	//详情
	public function view($args = array()){
		
		$id = $args[0];
		
		$this->load->model('sjs/sjs_zhaobiao_model');
		$this->load->model('sjs/sjs_zhaobiao_config_model');
		$this->load->model('sjs/sjs_zhaobiao_image_model');
		$this->load->model('sjs/sjs_bid_model');
		
		$zhaobiao = $this->sjs_zhaobiao_model->get($id, array(
			'add_fields'=>'detail'
		));
		
		$zhaobiao = $this->sjs_zhaobiao_config_model->assign_map($zhaobiao, array('types', 'pay_types'));
		$zhaobiao = $this->sjs_zhaobiao_image_model->assign_images($zhaobiao);
		
		$zhaobiao['detail'] = $this->encode->htmldecode($zhaobiao['detail'], true);
		
		// 竞标信息
		$sql = "select id, zid, detail, addtime from sjs_zhaobiao_bid where zid = '". $id ."' order by addtime asc";
		$bids = $this->sjs_bid_model->gets($sql);
		$bids['list'] = $this->sjs_bid_model->assign_images($bids['list']);
		
		// var_dump($bids);
		
		$this->tpl->assign('zhaobiao', $zhaobiao);
		$this->tpl->assign('bids', $bids['list']);
		$this->tpl->display( 'sjs/zhaobiao/view.html' );
		
	}
	
	// 项目招标
	public function send(){
		
		$this->_check_login();
		
		if( $this->info == false ){
			
			$url = $this->get_complete_url('/login/?r=' . urlencode( $this->encode->get_current_url() ));
			$this->alert('', $url);
			exit();
			
		}
		
		$this->tpl->assign('info', $this->info);
		
		$this->load->model('sjs/sjs_zhaobiao_config_model');
		
		$this->tpl->assign('types', $this->sjs_zhaobiao_config_model->get_types());
		$this->tpl->display('sjs/zhaobiao/send.html');
		
	}
	
	// 招标提交
	public function send_handler(){
		
		$this->_check_login();
		
		$info = $this->get_form_data();
		$info['username'] = $this->info['username'];
		
		$this->load->model('sjs/sjs_zhaobiao_model');
		
		try{
			$id = $this->sjs_zhaobiao_model->add($info);
			$this->alert('提交成功，等待审核', $this->get_complete_url('/zhaobiao'));
		}catch(Exception $e){
			$this->alert( $e->getMessage() );
		}
		
	}
	
}






























?>