<?php

// 企业招聘 管理
class member_recruit_active extends member_recruit_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		
		$tpl = $this->get_tpl('recruit/active.html');
		
		$info = array(
			'id'		=> $this->gr('id'),
			'rurl'		=> $this->gr('r')
		);
		
		$recruit = FALSE;
		
		if( $info['id'] != '' )
		{
			$this->load->model('company/hr', 'company_recruit_model');
			$recruit = $this->company_recruit_model->get($info['id'], array(
				'format'		=> FALSE,
				'username'		=> $this->base_user
			));
		}
		
		//var_dump2( $recruit );
		//var_dump2( $info );
		$this->tpl->assign('recruit', $recruit);
		$this->tpl->assign('params', $info);
		$this->tpl->display( $tpl );
		
	}
	
	// 提交处理
	public function handler()
	{
		$info = $this->get_form_data();
		$error = '';
		if( $info['title'] == '' )
		{
			$error = '职位名称不能为空';
		}
		
		if( $error == '' )
		{
			if( $info['yaoqiu'] == '' ) $error = '职位要求不能为空';
		}
		
		$info['senduser'] = $this->base_user;
		
		if( $error == '' )
		{
			try
			{
				$this->load->model('company/hr', 'company_recruit_model');
				if( isset( $info['id'] ) && $info['id'] != '' )
				{
					$this->company_recruit_model->edit($info);
				}
				else
				{
					unset( $info['id'] );
					$id = $this->company_recruit_model->add($info);
					
					// 增加口碑值
					$this->load->model('company/company_koubei_model');
					$this->company_koubei_model->recruit($this->base_user, $id, array(
						'description'		=> '添加招聘信息 - ' . $info['title']
					));
					
					
				}
			}
			catch(Exception $e)
			{
				$error = $e->getMessage();
			}
		}
		
		if( $error == '' )
		{
			
			// 店铺更新日期
			$this->deco_model->company_update($this->base_user);
			
			json_echo(array(
				'type'		=> 'success'
			));
		}
		else
		{
			json_echo(array(
				'type'		=> 'error',
				'error'		=> $error
			));
		}
	}
	
	// 删除
	public function delete()
	{
		$id = $this->gf('id');
		$error = '';
		if( ! preg_match('/^\d+$/', $id) ) $error = '参数错误';
		if( $error == '' )
		{
			$this->load->model('company/hr', 'company_recruit_model');
			try
			{
				$this->company_recruit_model->delete($id, array(
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
			
			// 店铺更新日期
			$this->deco_model->company_update($this->base_user);
			
			json_echo(array(
				'type'	=> 'success'
			));
		}
		else
		{
			json_echo(array(
				'type'		=> 'error',
				'error'		=> $error
			));
		}
	}
	
}

?>