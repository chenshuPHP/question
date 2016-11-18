<?php

// 设计团队 成员管理
class member_employee_employee extends member_employee_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'employee.employee');
	}
	
	public function manage(){
		
		$this->load->model('company/userteam', 'employee_model');
		$this->load->model('company/employee_category_model');
		$employees = $this->employee_model->gets("select id, job_id, true_name, username, face_image from [". $this->employee_model->table_name ."] where username = '". $this->base_user ."' and ". $this->employee_model->where ." order by sort_id asc");
		$list = $employees['list'];
		$list = $this->employee_category_model->assign_employee($list);
		
		$this->userinfo = $this->employee_model->count_assign( $this->userinfo );
		
		$this->tpl->assign('employees', $list);
		
		$this->tpl->assign('page_module', 'manage');
		$this->tpl->assign('count', $this->userinfo['employee_count']);
		
		$tpl = $this->get_tpl('employee/employee_manage.html');
		
		$this->tpl->display( $tpl );
		
	}
	
	public function add(){
		
		
		// 支持 ajax 2016-10-24
		$ajax = $this->gr('ajax');
		$error = '';
		$data = array();
		
		$id = $this->gr('id');
		
		if( ! preg_match('/^\d+$/', $id) && $id != '' )
		{
			$error = '参数格式错误';
		}
		
		if( $error == '' )
		{
			// 从业年限
			$this->load->model('company/userteam', 'employee_model');
			$cynx = $this->employee_model->get_course();
			
			// 职位
			$this->load->model('company/employee_category_model');
			$cats = $this->employee_category_model->gets("select id, job_name as name from user_team_job_type where username = '". $this->base_user ."' order by sort_id asc");
			
			$data['employee'] = FALSE;
			
		}
		
		if( $error == '' )
		{
			// 编辑模式
			if( ! empty($id) ){
				
				$employee = $this->employee_model->get(array(
					'username'		=> $this->base_user,
					'id'			=> $id,
					'fields'		=> 'id, username, true_name, geyan, detail, face_image, course, job_id'
				));
				
				if( ! $employee ) $error = '没找到需要修改的数据';
				
				if( $error == '' )
				{
					$this->tpl->assign('employee', $employee);
					$this->tpl->assign('rurl', $this->gr('r'));
					$this->tpl->assign('page_module', 'edit');
					
					$employee['src'] = $this->upload_complete_url( $employee['face_image'] );
					
					$data['employee'] 		= $employee;
					$data['rurl'] 			= $this->gr('r');
					$data['page_module'] 	= 'edit';
				}
				
			} else {
				
				$this->tpl->assign('cid', $this->gr('cid'));
				$this->tpl->assign('page_module', 'add');
				
				$data['cid']				= $this->gr('cid');
				$data['page_module']		= 'add';
				
			}
		}
		
		if( $error == '' )
		{
			$this->tpl->assign('cynx', $cynx);
			$this->tpl->assign('cats', $cats);
			$data['cynx']			= $cynx;
			$data['cats']			= $cats;
		}
		
		
		if( $ajax == 1 )
		{
			if( $error == '' )
			{
				json_echo( $data );
			}
			else
			{
				json_echo( array(
					'type'		=> 'error',
					'error'		=> $error
				) );
			}
		}
		else
		{
			if( $error == '' )
			{
				$this->tpl->display('member/employee/employee_add.html');
			}
			else
			{
				$this->alert( $error );
			}
		}
	}
	
	// 添加&修改处理
	public function handler(){
		
		$employee = array(
			'id'			=> $this->gf('id'),
			'new_face'		=> $this->gf('new_face'),
			'old_face'		=> $this->gf('old_face'),
			'name'			=> $this->gf('name'),
			'category'		=> $this->gf('category'),
			'cynx'			=> $this->gf('cynx'),			// 从业年限
			'geyan'			=> $this->gf('geyan'),		// 格言
			'intro'			=> $this->gf('intro'),		// 简介
			'username'		=> $this->base_user
		);
		
		$ajax = $this->gf('ajax');
		
		$error = '';
		
		$this->load->model('company/userteam', 'employee_model');
		
		if( empty($employee['id']) ){		// 添加模式
			try{
				
				$employee['id'] = $this->employee_model->add($employee);
				
				// 设置店铺更新日期
				$this->deco_model->company_update($this->base_user);
				
				// 增加口碑值
				$this->load->model('company/company_koubei_model');
				
				$this->company_koubei_model->employee($this->base_user, $employee['id'], array(
					'description'		=> '添加公司成员 - ' . $employee['name']
				));
				
				//$this->alert('添加成功', $this->get_complete_url('/employee/employee/manage'));
				
			}catch(Exception $e){
				
				$error = $e->getMessage();
				
				// $this->alert( $e->getMessage() );
			}
		} else {							// 编辑模式
			try{
				$this->employee_model->edit($employee);
				
				// 设置店铺更新日期
				$this->deco_model->company_update($this->base_user);
				
				$rurl = $this->gf('rurl');
				//$this->alert('修改成功', $rurl);
			}catch(Exception $e){
				$error = $e->getMessage();
				// $this->alert( $e->getMessage() );
			}
		}
		
		if( $ajax == 1 )
		{
			if( $error == '' )
			{
				json_echo( array(
					'type'		=> 'success',
					'employee'	=> $employee
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
		else
		{
			if( $error == '' )
			{
				$this->alert('修改成功', $rurl);
			}
			else
			{
				$this->alert( $error );
			}
		}
		
	}
	
	// 删除
	public function delete(){
		
		$id = $this->gr('id');
		$rurl = $this->gr('r');
		
		$ajax = $this->gr('ajax');
		
		$this->load->model('company/userteam', 'employee_model');
		
		$error = '';
		
		try{
			//$this->employee_model->delete(array(
			//	'id'=>$id,
			//	'username'=>$this->base_user
			//));
			
			// 2016-08-30 更新为删除到回收站
			$this->employee_model->recycle($id, array(
				'username'			=> $this->base_user
			));
			// 设置店铺更新日期
			$this->deco_model->company_update($this->base_user);
		}catch(Exception $e){
			$error = $e->getMessage();
		}
		
		if( $ajax == 1 )
		{
			if( $error == '' )
			{
				json_echo(array(
					'type'			=> 'success'
				));
			}
			else
			{
				json_echo(array(
					'type'			=> 'error',
					'error'			=> $error
				));
			}
		}
		else
		{
			if( $error == '' )
			{
				$this->alert('', $rurl);
			}
			else
			{
				$this->alert($error);
			}
		}
		
	}
	
	// 排序
	public function sorter(){
		
		$data = $_POST['data'];
		$username = $this->base_user;
		
		$this->load->model('company/userteam', 'employee_model');
		
		try{
			$this->employee_model->sorter(array(
				'data'=>$data,
				'username'=>$username
			));
			// 设置店铺更新日期
			$this->deco_model->company_update($this->base_user);
			echo('success');
		}catch(Exception $e){
			echo('排序失败 原因：' . $e->getMessage());
		}
	}
	
	
	
}


?>