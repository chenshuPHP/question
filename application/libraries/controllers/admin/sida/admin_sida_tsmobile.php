<?php

// 投诉 短信提醒
class admin_sida_tsmobile extends admin_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function manage()
	{
		
		$tpl = $this->get_tpl( 'sida/tsmobile/manage.html' );
		
		$params = array(
			'id'		=> $this->gr('id'),
			'rurl'		=> $this->gr('r')
		);
		
		$this->load->model('sida/TousuModel', 'tousu_model');
		$this->load->model('sida/tousu_letter_model');
		$this->load->model('manager/manager_model');
		
		$tousu = $this->tousu_model->getSingle($params['id'], array(
			'fields'		=> 'id, username, title, puttime, tel, mobile, mianji, xingshi, zaojia, danwei, rejion, status, recycle, puttime',
			'format'		=> FALSE
		));
		
		$tousu = $this->tousu_model->formatSingle($tousu, array(
			'format_display_truename'		=> TRUE,
			'format_display_truetel'		=> TRUE
		));
		
		// 发送列表
		$letter = $this->tousu_letter_model->gets( array('tsid'=>$params['id']) );
		$letter = $this->manager_model->assign($letter, 'username, fullname', 'username');
		
		// var_dump2( $letter );
		
		$this->tpl->assign('letter', $letter);
		$this->tpl->assign('params', $params);
		$this->tpl->assign('tousu', $tousu);
		$this->tpl->display( $tpl );
		
	}
	
	public function send()
	{
		$info = $this->get_form_data();
		$this->load->model('sida/tousu_letter_model');
		$this->load->model('manager/manager_model');
		
		$info['addtime'] 		= date('Y-m-d H:i:s');			// 发送日期
		$info['username'] 		= $this->admin_username;		// 关联账户
		
		$result = $this->tousu_letter_model->send( $info );
		
		$_arr = array();
		
		if( $result['type'] == 'success' )
		{
			$_arr['type'] 			= 'success';
			$_arr['addtime'] 		= $info['addtime'];
			$_arr['username'] 		= $info['username'];
			$_arr = $this->manager_model->assign($_arr, 'username, fullname', 'username');
		}
		else
		{
			$_arr['type'] 			= 'error';
			$_arr['message']		= $result['message'];
		}
		
		echo( json_encode( $_arr ) );
		
	}
	
	
}

?>