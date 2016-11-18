<?php

class mobile_shop_home extends mobile_shop_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('module', 'home');
	}
	
	public function home()
	{
		$tpl = $this->get_tpl('shop/home/home.html');
		
		$this->load->model('company/zizhi', 'zizhi_model');
		$this->load->model('company/userteam', 'employee_model');
		$this->load->model('company/usercase', 'project_model');
		$this->load->model('company/employee_category_model');
		
		$deco = $this->deco_model->getCompany($this->username, array(
			'fields'		=> 'username, zhibaojin, koubei, koubei_total, tel, mobile, address, content, update_time'
		));
		$deco['content'] = $this->encode->removeHtmlAndSpace( $deco['content'] );
		$this->deco_model->conver( $deco );
		// 统计员工数量
		$deco = $this->employee_model->count_assign($deco);
		
		// 统计案例数量
		$deco = $this->project_model->case_count_assign_users($deco);
		
		// 项目
		$_sql = "select top 2 id, username, casename, fm_image, budget, area ".
		"from [". $this->project_model->table_name ."] where ".
		"fm_image <> '' and username = '". $this->username ."' and " . $this->project_model->where . " order by addtime desc";
		$projects = $this->project_model->get_case_list( $_sql );
		$projects = $projects['list'];
		$this->mobile_url_model->assign_sp_project_url( $projects );
		if( count( $projects ) > 0 )
		{
			foreach($projects as $key=>$value)
			{
				$value['thumb'] = '';
				if( $value['fm_image'] != '' ) $value['thumb'] = $this->thumb->crop($value['fm_image'], 300, 250);
				$projects[$key] = $value;
			}
		}
		
		// 员工
		$_where = $this->employee_model->where(array(
			"username = '". $this->username ."'"
		));
		$_sql = "select top 6 id, job_id, username, true_name, face_image ".
		"from [". $this->employee_model->table_name ."] ".
		"where " . $_where . " order by addtime desc";
		$employees = $this->employee_model->gets( $_sql );
		$employees = $employees['list'];
		$employees = $this->employee_category_model->assign_employee( $employees );
		$this->mobile_url_model->assign_sp_employee_url( $employees );
		if( count( $employees ) > 0 )
		{
			foreach($employees as $key=>$value)
			{
				$value['thumb'] = '';
				if( $value['face_image'] != '' ) $value['thumb'] = $this->thumb->crop($value['face_image'], 200, 200);
				$employees[$key] = $value;
			}
		}
		
		// 资质证书
		$honors = $this->zizhi_model->get_list(array(
			'username'		=> $this->username,
			'top'			=> 6,
			'fields'		=> 'id, imgpath as path, zizhiname as name, senduser'
		));
		$this->mobile_url_model->assign_sp_cert_url( $honors );
		//var_dump2( $deco );
		//var_dump2( $honors );
		//var_dump2( $projects );
		//var_dump2( $employees );
		
		$this->tpl->assign('deco', $deco);
		$this->tpl->assign('honors', $honors);
		$this->tpl->assign('projects', $projects);
		$this->tpl->assign('employees', $employees);
		
		$this->tpl->display( $tpl );
	}
	
}