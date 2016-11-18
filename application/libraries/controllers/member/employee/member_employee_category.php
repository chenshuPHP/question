<?php

// 公司团队 岗位设置
class member_employee_category extends member_employee_base {
	
	public $module_name = 'employee.category';
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', $this->module_name);
	}
	
	public function manage(){
		
		$this->load->model('company/employee_category_model');
		$this->load->model('company/userteam', 'employee_model');
		
		$cats = $this->employee_category_model->gets("select id, job_name as name from user_team_job_type where username = '". $this->base_user ."' order by sort_id asc");
		$cats = $this->employee_model->count_assign_cats($cats);
		
		if( $this->gr('ajax') == 1 )
		{
			json_echo( array(
				'list'				=> $cats,
				'page_module'		=> 'manage'
			) );
		}
		else
		{
			$this->tpl->assign('cats', $cats);
			$this->tpl->assign('page_module', 'manage');
			$this->tpl->display('member/employee/category_manage.html');
		}
		
	}
	
	
	// 添加 添加&修改
	public function add(){
		
		$id = $this->gr('id');
		$rurl = $this->gr('r');
		if( empty($id) ){	// 添加
			$this->tpl->assign('page_module', 'add');
		} else {			// 删除
			$this->load->model('company/employee_category_model');
			$cat = $this->employee_category_model->get(array(
				'id'=>$id,
				'username'=>$this->base_user
			));
			if( ! $cat ) exit('找不到名称，或者无权限');
			$this->tpl->assign('cat', $cat);
			$this->tpl->assign('page_module', 'edit');
		}
		$this->tpl->assign('rurl', $rurl);
		$this->tpl->display('member/employee/category_add.html');
		
	}
	
	// 添加&编辑 提交
	public function active(){
		
		$cat = array(
			'id'			=> $this->gf('id'),
			'name'			=> $this->gf('category'),
			'username'		=> $this->base_user
		);
		
		$rurl = $this->gf('r');
		$ajax = $this->gf('ajax');
		
		if( $this->gf('request_type') == 'ajax' )
		{
			$ajax = 1;
		}
		
		$error = '';
		
		if( empty($cat['name']) ){
			$error = '请输入职位';
		}
		
		$this->load->model('company/employee_category_model');
		
		try{
			
			if( empty( $cat['id'] ) ){
				$cat['id'] = $this->employee_category_model->add($cat);
			} else {
				$this->employee_category_model->edit($cat);
			}
			
			// 设置店铺更新日期
			$this->deco_model->company_update($this->base_user);
			
		}catch(Exception $e){
			$error = $e->getMessage();
		}
		
		if( $ajax == 1 )
		{
			if( $error == '' )
			{
				json_echo( array(
					'type'			=> 'success',
					'category'		=> array('name'=>$cat['name'], 'id'=>$cat['id'])
				) );
			}
			else
			{
				echo( json_encode(array(
					'type'			=> 'error',
					'message'		=> $error
				)) );
			}
		}
		else
		{
			if( $error == '' )
			{
				$this->alert('提交成功', $this->get_complete_url('/employee/category/manage'));
			}
			else
			{
				$this->alert('添加失败，请检查名称格式');
			}
		}
		
	}
	
	// 排序处理
	public function sort_handler(){
		
		$data = $_POST['data'];
		$this->load->model('company/employee_category_model');
		
		try{
			$this->employee_category_model->sorter(array(
				'username'=>$this->base_user,
				'sorts'=>$data
			));
			
			// 设置店铺更新日期
			$this->deco_model->company_update($this->base_user);
			
			echo('success');
		}catch(Exception $e){
			echo( $e->getMessage() );
		}
	}
	
	// 删除
	public function delete(){
		
		$id = $this->gr('id');
		$rurl = $this->gr('r');
		
		$ajax = $this->gr('ajax');
		
		$this->load->model('company/employee_category_model');
		$this->load->model('company/userteam', 'employee_model');
		$cat = $this->employee_category_model->get(array(
			'id'=>$id,
			'username'=>$this->base_user
		));
		
		$error = '';
		
		if( ! $cat ){
			$error = '没有找到要删除的分类';
		}
		
		if( $error == '' )
		{
			$cat = $this->employee_model->count_assign_cats($cat);
			if( $cat['employee_count'] > 0 ){
				$error = '分类中有成员存在，无法删除';
			}
		}
		
		if( $error == '' )
		{
			try{
				$this->employee_category_model->delete(array(
					'username'=>$this->base_user,
					'id'=>$id
				));
				
				// 设置店铺更新日期
				$this->deco_model->company_update($this->base_user);
			}catch(Exception $e){
				$error = $e->getMessage();
			}
		}
		
		if( $error == '' )
		{
			if( $ajax == 1 )
			{
				json_echo( array(
					'type'		=> 'success'
				) );
			}
			else
			{
				$this->alert('', $rurl);
			}
		}
		else
		{
			if( $ajax == 1 )
			{
				json_echo( array(
					'type'		=> 'error',
					'error'		=> $error
				) );
			}
			else
			{
				$this->alert( $error, $rurl );
			}
		}
		
		
	}
	
	
}























?>