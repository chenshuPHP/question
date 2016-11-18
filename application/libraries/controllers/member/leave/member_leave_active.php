<?php

// 留言操作 删除等
class member_leave_active extends member_leave_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
	// 删除留言
	public function delete()
	{
		
		$error = '';
		
		$id = $this->gf('id');
		
		if( ! preg_match('/^\d+$/', $id) ) $error = '参数错误';
		
		if( $error == '' )
		{
			$this->load->model('company/guest', 'company_leave_model');
			try
			{
				$this->company_leave_model->delete($id, array(
					'username'		=> $this->base_user
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
	
	
	// 回复
	public function reply()
	{
		$info = $this->get_form_data();	// pid, content
		$error = '';
		if( ! isset($info['pid']) || ! preg_match('/^\d+$/', $info['pid']) ) $error = '参数错误';
		if( ! isset($info['pid']) || $info['content'] == '' ) $error = '内容不能为空';
		
		if( $error == '' )
		{
			$this->load->model('company/guest', 'company_leave_model');
			try
			{
				$result = $this->company_leave_model->reply($info, array(
					'username'			=> $this->base_user
				));
				$result['addtime'] = date('Y-m-d H:i', strtotime($result['addtime']));	// 输出前端需要的格式
			}
			catch(Exception $e)
			{
				$error = $e->getMessage();
			}
		}
		
		if( $error == '' )
		{
			json_echo( array(
				'type'		=> 'success',
				'info'		=> $result
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