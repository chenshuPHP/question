<?php

class mobile_budget extends mobile_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function index(){
		
		//if( $this->gr('v') == 2016 ){
			$tpl = $this->get_tpl('budget/home.html');
			$this->tpl->caching = true;
		//} else {
		//	$tpl = 'mobile/budget/send.html';
		//	$this->tpl->caching = true;
		//}
		
		$this->tpl->cache_dir = $this->get_cache_dir('budget');
		
		if( ! $this->tpl->isCached( $tpl ) ){
			$this->load->model('publish/pubModel', 'pub_model');
			//$count1 = $this->pub_model->get_deco_count();
			$count2 = $this->pub_model->get_express_pipe_count();
			$this->tpl->assign('count', $count2);
			$this->tpl->assign('title', '预算下载');
			$this->tpl->display($tpl);
		} else {
			$this->tpl->display($tpl);
			echo('<!-- cached -->');
		}
		
	}
	
	public function send_submit(){
		$this->load->model('publish/pubModel', 'pub_model');
		$this->load->library('encode');
		$object = array();
		$object['shen'] = $this->encode->getFormEncode('User_Shen');
		$object['city'] = $this->encode->getFormEncode('User_City');
		$object['town'] = $this->encode->getFormEncode('User_Town');
		$object['area'] = $this->encode->getFormEncode('area');
		$state = $this->encode->getFormEncode('state');
		$object['category'] = $this->encode->getFormEncode('category');
		$object['true_name'] = $this->encode->getFormEncode('name');
		$object['tel'] = $this->encode->getFormEncode('tel');
		
		$object['rel'] = '{"url":"'. $this->mobile_url .'budget", "name":"触屏版-预算下载"}';
		
		$object['ps'] = '[{"key":"房屋状态", "value":"'. $state .'"}]';
		if( $object['true_name'] == '' || $object['tel'] == '' ){
			exit('称呼和密码不能为空');
		} else {
			$this->pub_model->express_pipe_add( $object );
			echo('<script type="text/javascript">alert("提交成功");location.href="'. $this->mobile_url .'budget";</script>');
		}
		
		
	}
	
}





















?>