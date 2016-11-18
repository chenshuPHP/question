<?php

// 添加 编辑 排序
class member_certificate_active extends member_certificate_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function upload()
	{}
	
	// 编辑
	public function index()
	{
		$info = $this->get_form_data();
		
		$error = '';
		
		if( $error == '' )
		{
			$this->load->model('company/zizhi', 'company_certificate_model');
			try
			{
				
				if( $info['id'] != '' )	
				{
					$this->company_certificate_model->edit( array(
						'id'				=> $info['id'],
						'name'				=> $info['name'],
						'path'				=> $info['path'],
						'username'			=> $this->base_user
					) );
				}
				else
				{
					$id = $this->company_certificate_model->add( array(
						'id'				=> $info['id'],
						'name'				=> $info['name'],
						'path'				=> $info['path'],
						'username'			=> $this->base_user
					) );
					
					// 增加口碑值
					$this->load->model('company/company_koubei_model');
					$this->company_koubei_model->certificate( $this->base_user, $id, array(
						'description'		=> '添加资质证书 - ' . $info['name']
					) );
					
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
			
			json_echo( array(
				'type'		=> 'success'
			) );
		}
		else
		{
			json_echo(array(
				'type'		=> 'error',
				'error'		=> $error
			));
		}
		
	}
	
	// 排序处理
	public function sort()
	{
		$data = $this->gf('data', array(
			'retain_key'		=> TRUE
		));
		
		$error = '';
		
		if( ! is_array( $data ) ) $error = '参数错误';
		
		if( $error == '' )
		{
			try
			{
				$this->load->model('company/zizhi', 'company_certificate_model');
				$this->company_certificate_model->sort($data, array(
					'username'			=> $this->base_user
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
			
			json_echo( array(
				'type'		=> 'success'
			) );
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
		
		try
		{
			$this->load->model('company/zizhi', 'company_certificate_model');
			$this->company_certificate_model->delete( $id, array(
				'username'		=> $this->base_user
			) );
		}
		catch(Exception $e)
		{
			$error = $e->getMessage();
		}
		
		if( $error == '' )
		{
			
			// 店铺更新日期
			$this->deco_model->company_update($this->base_user);
			
			json_echo( array(
				'type'		=> 'success'
			) );
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