<?php

// 会员中心 主页
class member_main extends member_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		$tpl = $this->get_tpl('main/home.html');
		$this->load->model('company/company_degree_model');
		$this->load->model('company/guest', 'deco_guest_model');
		
		// 站内信统计
		$this->load->model('company/member_letter_model');
		$new_letter_count = $this->member_letter_model->counter($this->base_user, array(
			'isVIP'				=> $this->isVIP(),
			'username'			=> $this->base_user,
			'open'				=> 0
		));
		
		$info = $this->deco_model->getCompany($this->base_user, array(
			'fields'	=> 'username, logo, company, koubei, koubei_total, iput'
		));
		$this->deco_guest_model->count_assign( $info );
		
		// 公司活动
		$this->load->model('company/promotion_model');
		$where = $this->promotion_model->where( array(
			"username = '". $this->base_user ."'"
		) );
		$sql = "select top 1 id, title, sdate, edate, description, imgpath as path, addtime, username ".
		"from [". $this->promotion_model->table_name ."] where " . $where . " order by addtime desc";
		$_result = $this->promotion_model->gets($sql);
		$promotion = FALSE;
		if( isset( $_result['list'][0] ) ) $promotion = $_result['list'][0];
		$this->tpl->assign('promotion', $promotion);
		
		//var_dump2( $promotion );
		
		// 最新案例
		$this->load->model('company/usercase', 'project_model');
		$this->userinfo = $this->project_model->project_assign_decos($this->userinfo, array(
			'size'		=> 2,
			'fields'	=> 'id, username, casename, fm_image, budget, area, addtime'
		));
		$projects = FALSE;
		if( isset( $this->userinfo['projects'] ) )
		{
			$projects = $this->userinfo['projects'];
			unset( $this->userinfo['projects'] );
			$projects = $this->project_model->image_count_assign_case( $projects );
		}
		//var_dump2( $projects );
		$this->tpl->assign('projects', $projects);
		
		// 最新成员
		$this->load->model('company/userteam', 'employee_model');
		$this->load->model('company/employee_category_model');
		
		$where = $this->employee_model->where( array(
			"username = '". $this->base_user ."'"
		) );
		
		$sql = "select top 1 id, job_id, username, true_name, addtime, face_image ".
		"from [". $this->employee_model->table_name ."] where ". $where ." order by addtime desc";
		$_result = $this->employee_model->gets($sql);
		$employee = FALSE;
		if( isset( $_result['list'][0] ) )
		{
			$employee = $_result['list'][0];
			$employee = $this->employee_category_model->assign_employee( $employee );
		}
		
		// var_dump2( $employee );
		$this->tpl->assign('employee', $employee);
		
		// 证书
		$this->load->model("company/zizhi", 'certificate_model');
		$where = $this->certificate_model->where(array(
			"senduser = '". $this->base_user ."'"
		));
		$sql = "select top 1 id, imgpath, senduser, zizhiname ".
		"from [". $this->certificate_model->table_name ."] where " . $where . " order by id desc";
		$certificate = FALSE;
		$_result = $this->certificate_model->gets($sql);
		if( isset( $_result['list'][0] ) ) $certificate = $_result['list'][0];
		//var_dump2( $certificate );
		$this->tpl->assign('certificate', $certificate);
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		$this->tpl->assign('deco', $info);
		$percent = $this->company_degree_model->degree( $this->base_user );	// 资料完善百分比
		$this->tpl->assign('percent', $percent);
		$this->tpl->assign('new_letter_count', $new_letter_count);
		$this->tpl->display( $tpl );
	}
	
}

?>