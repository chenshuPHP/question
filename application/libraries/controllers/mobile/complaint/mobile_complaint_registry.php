<?php

// 投诉提交
class mobile_complaint_registry extends mobile_complaint_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	// 界面
	public function index()
	{
		$this->tpl->display( $this->get_tpl('complaint/registry.html') );
	}
	
	// 提交处理
	public function submit()
	{
		
		$info = array(
			'name'		=> $this->gf('name'),
			'title'		=> $this->gf('title'),
			'content'	=> $this->gf('content'),
			'type'		=> $this->gf('type'),
			'tel'		=> $this->gf('tel'),
			'mobile'	=> $this->gf('mobile'),
			'address'	=> $this->gf('address'),
			'fangxing'	=> $this->gf('fangxing'),
			'area'		=> $this->gf('area'),
			'bao'		=> $this->gf('bao'),
			'budget'	=> $this->gf('budget'),
			'cmp'		=> $this->gf('cmp'),
			'fuzeren'	=> $this->gf('fuzeren'),
			'hetong'	=> $this->gf('hetong'),
			'tiaojie'	=> $this->gf('tiaojie'),
			'images'	=> $this->gf('images'),
			'source'   => 'shzh_mobile'
		);

		$error = '';
		if( $info['title'] == '' || $info['tel'] == '' || $info['mobile'] == '' || $info['cmp'] == '' )
		{
			$error = '资料不完整';
		}
		
		
		if( $error == '' )
		{
			$this->load->library('sync');
			$cfg = array(
				'url'		=> $this->get_complete_url('/sida/tousu_submit'),
				// 'dataType'	=> 'string',
				'data'		=> array(
					'token'			=> config_item('shzh_sync_token'),
					'complaint'		=> json_encode( $info )
				)
			);
			$result = $this->sync->ajax( $cfg );
			if( $result['type']	== 'error' ) $error = $result['error'];
		}
		
		if( $error == '' )
		{
			$complaint = array(
				'id'		=> $result['id'],
				'title'		=> $info['title']
			);
			$complaint = $this->mobile_url_model->format_single_complaint( $complaint );
			json_echo( array(
				'type'			=> 'success',
				'complaint'		=> $complaint
			) );
		}
		else
		{
			json_echo( array(
				'type'		=> 'error',
				'error'		=> $error
			) );
		}
		
	}
	
}


?>