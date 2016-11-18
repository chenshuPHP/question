<?php

class admin_biz_track extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 派单跟踪记录
	public function manage(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		// ===========================
		// 可能url中有中文的问题，系统会在url参数名称前面加amp后面加分号
		// 如: /shzh_manage_v2/biz/biz/relation?name=杨浦区俞&ampcname;=祥雀&ampevent;=
		// 这里做下处理，具体原因未知
		$r = str_replace(';', '', $r);
		$r = str_replace('amp', '', $r);
		// ===========================
		
		$this->load->model('biz/biz_model');
		$this->load->model('biz/biz_state_model');
		
		$relation = $this->biz_model->get_relation($id);
		
		$relation = $this->biz_model->biz_assign_distribut($relation);
		$relation = $this->biz_model->company_assign_distributs($relation);
		
		$tracks = $this->biz_state_model->get_tracks($relation['id']);
		
		if( is_array($tracks) && count($tracks) == 0 )
			$tracks = false;
		
		$states = $this->biz_state_model->get_states();
		
		$this->tpl->assign('relation', $relation);
		$this->tpl->assign('tracks', $tracks);
		$this->tpl->assign('states', $states);
		$this->tpl->assign('module', 'track');
		$this->tpl->assign('r', $r);
		$this->tpl->display("admin/business/biz_track_manage.html");
		
	}
	
	// 跟踪记录后台管理员提交
	public function add(){
		
		$info = array(
			'did'=>$this->gf('did'),
			'even'=>$this->gf('even'),
			'content'=>$this->gf('content'),
			'username'=>$this->admin_username,
			'agree_date'=>$this->gf('agree_date'),
			'rebate'=>$this->gf('rebate')
		);
		
		$this->load->model('biz/biz_state_model');
		$this->load->model('biz/biz_model');
		
		$result = array();
		try{
			
			$this->biz_state_model->biz_state_change($info['did'], $info['even'], $info['username'], $info['content'], 'admin');
			
			//$this->biz_model->set_distribut_rebate($info);	// 设置签单日期和是否返点完成
			
			$result['type'] = 'success';
		}catch(Exception $e){
			$result['type'] = 'error';
			$result['message'] = $e->getMessage();
		}
		
		echo(json_encode($result));
		
	}
	
	
}

?>