<?php

class sp_team extends sp_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'team');
	}
	
	// 团队主页
	public function home($args = array()){
		
		
		$cid = 0;
		if( count($args) > 0 ) $cid = $args[0];
		
		$this->load->model('company/employee_category_model');
		$this->load->model('company/userteam', 'employee_model');
		$this->load->model('company/usercase', 'project_model');
		
		
		$cats = $this->employee_category_model->gets("select id, job_name as name, username from [user_team_job_type] where username = '". $this->user ."' order by sort_id asc");
		
		$jids = array(0);
		$current_cat = array('name'=>'服务团队', 'id'=>0);
		foreach($cats as $cat){
			$jids[] = $cat['id'];
			if($cat['id'] == $cat ) $current_cat = $cat;
		}
		
		$fields = 'id, job_id, username, true_name as name, course, face_image as face, sort_id, geyan, detail';
		
		if( $cid == 0 ){
			$sql = "select top 30 ". $fields ." from [". $this->employee_model->table_name ."] where ".
			"username = '". $this->user ."' and job_id in (". implode(',', $jids) .") and ". $this->employee_model->where ." ".
			"order by charindex(',' + rtrim(cast(job_id as varchar(10))) + ',', ',". implode(',', $jids) .",'), sort_id asc";
		} else {
			$sql = "select ". $fields ." from [". $this->employee_model->table_name ."] ".
			"where job_id = '". $cid ."' and username = '". $this->user ."' and ". $this->employee_model->where ." order by sort_id asc";
		}
		
		$employees = $this->employee_model->gets($sql);
		$employees = $employees['list'];
		$employees = $this->employee_category_model->assign_employee($employees);
		$employees = $this->project_model->case_count_assign_employee($employees);
		
		$this->tpl->assign('cid', $cid);
		$this->tpl->assign('employees', $employees);
		$this->tpl->assign('cats', $cats);
		$this->tpl->assign('current_cat', $current_cat);
		$this->tpl->display( $this->get_tpl('team/home.html') );
		
	}
	
	
	// 个人详情页
	public function view($args){
		
		$this->load->model('company/employee_category_model');
		$this->load->model('company/userteam', 'employee_model');
		$this->load->model('company/usercase', 'project_model');
		
		$cats = $this->employee_category_model->gets("select id, job_name as name, username from user_team_job_type where username = '". $this->user ."' order by sort_id asc");
		

	
		
		$eid = $args[0];	// 人员ID
		
		if( ! is_numeric($eid) )
		{
			show_404();
			exit();
		}
		
		$employee = $this->employee_model->get(
			array(
				'id'=>$eid,
				'username'=>$this->user,
				'fields'=>'id, job_id, username, true_name as name, face_image as face, course, geyan, detail, addtime'
			)
		);
		
		if( ! $employee )
		{
			show_error('您访问了一个不存在的页面', 404, '404 notfound');
			exit();
		}
		
		$employee = $this->employee_category_model->assign_employee($employee);
		$employee['detail'] = $this->encode->htmldecode($employee['detail']);
		$employee = $this->project_model->project_assign_employees($employee, array(
			'fields'	=> 'id, username, casename as name, fm_image as fm, addtime'
		));
		
		//var_dump2( $employee );
		
		$this->tpl->assign('cid', $employee['job_id']);
		$this->tpl->assign('cats', $cats);
		$this->tpl->assign('employee', $employee);
		
		$this->tpl->display( $this->get_tpl('team/view.html') );
	}
	
}