<?php

class sjs_bid extends sjs_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	
	// 竞标
	public function home(){
		
		$id = $this->gr('id');
		$this->load->model('sjs/sjs_zhaobiao_model');
		$zhaobiao = $this->sjs_zhaobiao_model->get($id, array(
			'fields'=>'id'
		));
		
		$this->tpl->assign('rurl', $this->gr('r'));
		$this->tpl->assign('zhaobiao', $zhaobiao);
		$this->tpl->display('sjs/zhaobiao/bid.html');
		
	}
	
	// 竞标提交
	public function handler(){
		
		$info = $this->get_form_data();
		$this->load->model('sjs/sjs_bid_model');
		
		try{
			$this->sjs_bid_model->add($info);
			$this->alert('提交成功，审核中.', $info['rurl']);
		}catch(Exception $e){
			$this->alert( $e->getMessage() );
		}
		
	}


















}

?>