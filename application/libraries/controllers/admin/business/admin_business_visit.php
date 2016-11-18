<?php

// 装修订单回访管理
class admin_business_visit extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 添加回访记录
	public function add(){
		
		$visit = array(
			'biz_id'=>$this->gf('biz'),
			'detail'=>$this->gf('content'),
			'next_date'=>$this->gf('next_date'),
			'addtime'=>date('Y-m-d H:i:s'),
			'admin'=>$this->admin_username
		);
		
		$this->load->model('biz/biz_visit_model');
		
		$result = array();
		
		try{
			$visit['id'] = $this->biz_visit_model->add($visit);
			$result['type'] = 'success';
			$visit['detail'] = $this->encode->htmldecode($visit['detail'], true);
			$result['data'] = $visit;
		}catch(Exception $e){
			$result['type'] = 'error';
			$result['error_message'] = $e->getMessage();
		}
		
		echo(json_encode($result));
		
	}
	
}

?>