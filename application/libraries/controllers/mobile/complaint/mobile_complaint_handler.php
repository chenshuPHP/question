<?php

class mobile_complaint_handler extends mobile_complaint_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		
		$complaint = array(
			'name'=>$this->gf('name'),
			'tel'=>$this->gf('tel'),
			'cmp'=>$this->gf('deco_name'),
			'mobile'=>$this->gf('deco_tel'),
			'type'=>$this->gf('category'),
			'content'=>$this->gf('detail'),
			'addtime'=>date('Y-m-d H:i:s')
		);
		
		$complaint['title'] = '投诉 '. $complaint['cmp'] .' ' . $complaint['type'];
		$complaint['address'] = '';
		$complaint['fangxing'] = '';
		$complaint['area'] = '';
		$complaint['bao'] = '';
		$complaint['budget'] = '';
		$complaint['fuzeren'] = '';
		$complaint['hetong'] = '';
		$complaint['tiaojie'] = '';
		
		
		if( $complaint['name'] == '' || $complaint['tel'] == '' || $complaint['cmp'] == '' || $complaint['mobile'] == '' ){
			exit('提交信息填写不完整');
		}
		
		$this->load->model('sida/TousuModel', 'complaint_model');
		
		try{
			$this->complaint_model->add($complaint);
			$this->alert('提交成功', $this->mobile_url . 'complaint');
		}catch(Exception $e){
			$this->alert('错误' . $e->getMessage());
		}
		
	}
	
	
}


?>