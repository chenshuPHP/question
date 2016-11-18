<?php

class mobile_shop_employee extends mobile_shop_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('module', 'employee');
	}
	
	// 当调用 detail-100 这样的URL时使用
	public function _remap($method_name, $args = array())
	{
		if( preg_match('/^detail\-\d+$/', strtolower( $method_name )) )
		{
			$_tmp = explode('-', $method_name);
			$this->detail( $_tmp[1] );
		}
	}
	
	public function home()
	{
		$tpl = $this->get_tpl('shop/employee/list.html');
		
		$args = array(
			'page'		=> $this->encode->get_page(),
			'size'		=> 8
		);
		
		$so = array(
			'jid'		=> $this->gr('jid')
		);
		
		$params = array();
		
		$this->load->model('company/userteam', 'employee_model');
		$this->load->model('company/employee_category_model');
		
		$sets = array(
			"username = '". $this->username ."'"
		);
		
		$cur = FALSE;
		if( $so['jid'] != '' )
		{
			$cur = $this->employee_category_model->get(array(
				'id'		=> $so['jid'],
				'username'	=> $this->username
			));
			if( $cur ) $this->mobile_url_model->assign_sp_employee_cat_url( $cur );
			$sets[] = "job_id = '". $so['jid'] ."'";
			$params[] = "jid=" . $so['jid'];
		}
		
		$where = $this->employee_model->where( $sets );
		
		
		$sql = "select * from ( select id, job_id, username, true_name, face_image, num = row_number() over( order by sort_id asc ) ".
		"from [". $this->employee_model->table_name ."] where ". $where ." ) as tmp ".
		"where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from [". $this->employee_model->table_name ."] where " . $where;
		
		$res = $this->employee_model->gets($sql, $sql_count);
		
		if( $res )
		{
			foreach( $res['list'] as $k=>$v )
			{
				$v['thumb'] = '';
				if( $v['face_image'] != '' ) $v['thumb'] = $this->thumb->crop($v['face_image'], 200, 200);
				$res['list'][$k] = $v;
			}
			$res['list'] = $this->employee_category_model->assign_employee( $res['list'] );
			$this->mobile_url_model->assign_sp_employee_url( $res['list'] );
		}
		
		$_sql = "select id, job_name, username from [". $this->employee_category_model->table_name ."] where username = '". $this->username ."' order by sort_id asc";
		$cats = $this->employee_category_model->gets($_sql);
		
		if( $cats )
		{
			$this->mobile_url_model->assign_sp_employee_cat_url( $cats );
		}
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $res['count'];
		$this->pagination->pageSize = $args['size'];
		if( count( $params ) == 0 )
		{
			$this->pagination->url_template = $this->get_shop_complete_url('/employee?page=<{page}>');
			$this->pagination->url_template_first = $this->get_shop_complete_url('/employee');
		}
		else
		{
			$this->pagination->url_template = $this->get_shop_complete_url('/employee?page=<{page}>&' . implode('&', $params));
			$this->pagination->url_template_first = $this->get_shop_complete_url('/employee?' . implode('&', $params));
		}
		
		$pagination = $this->pagination->tostring_simple( array(
			'select'		=> TRUE
		) );
		
		$this->tpl->assign("employee", $res['list']);
		$this->tpl->assign("job", $cats);
		$this->tpl->assign("cur", $cur);
		$this->tpl->assign("pagination", $pagination);
		
		// var_dump2( $pagination );
		
		$this->tpl->display( $tpl );
	}
	
	public function detail( $id )
	{
		$tpl = $this->get_tpl('shop/employee/detail.html');
		$this->load->model('company/userteam', 'employee_model');
		$this->load->model('company/usercase', 'project_model');
		$this->load->model('company/project_category_model');
		$this->load->model('company/employee_category_model');
		
		$employee = $this->employee_model->get(array(
			'id'			=> $id,
			'fields'		=> 'id, job_id, true_name, username, motto, geyan, detail, face_image',
			'username'		=> $this->username
		));
		
		if( ! $employee ) show_404();
		
		$employee = $this->employee_category_model->assign_employee( $employee );
		$employee = $this->project_model->project_assign_employees( $employee, array(
			'fields'		=> 'id, username, casename as name, fm_image as fm, build_type_1, build_type_2, style_name, budget, area'
		) );
		$employee['projects'] = $this->project_category_model->assign_to_project( $employee['projects'] );
		$employee['projects'] = $this->project_model->image_count_assign_case($employee['projects']);
		$employee = $this->project_model->case_count_assign_employee( $employee );
		$employee['thumb'] = $this->thumb->crop($employee['face_image'], 200, 200);
		$this->mobile_url_model->assign_sp_employee_url( $employee );
		$this->mobile_url_model->assign_sp_project_url( $employee['projects'] );
		$this->mobile_url_model->assign_sp_employee_cat_url( $employee['cat'] );
		
		// var_dump2( $employee );
		
		$this->tpl->assign('employee', $employee);
		$this->tpl->display( $tpl );
	}
	
	
}



























?>