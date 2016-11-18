<?php

// 投诉撤销
class sida_tousu_revoke extends sida_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('sida/tousu_writeable_model');
	}
	
	// 撤诉申请
	public function handler()
	{
		
		$info = array(
			'tsid'				=> $this->gf('tsid'),
			'revoke_info'		=> $this->gf('revoke_info')
		);
		
		// 当前提交信息的用户名
		$username = $this->tousu_writeable_model->get_current_username();
		
		// 检测是否有撤销权限
		$check = $this->tousu_writeable_model->get_mobile_ive($username, $info['tsid']);
		
		$error = '';
		
		if( $check['writeable'] !== TRUE || $check['type'] != 'active' )
		{
			$error = '没有权限';
		}
		
		if( $error == '' )
		{
			try
			{
				$this->load->model('sida/tousu_revoke_model');
				$this->tousu_revoke_model->set_revoke($info['tsid'], array(
					'revoke_info'		=> $info['revoke_info'],
					'username'			=> $username
				));
			}
			catch(Exception $e)
			{
				$error = $e->getMessage();
			}
		}
		
		if( $error == '' )
		{
			json_echo( array(
				'type'		=> 'success'
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