<?php

class admin_member_employee extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function manage(){
		
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>18
		);
		
		$filter = array(
			'recycle'=>$this->gr('recycle'),
			'key'=>$this->gr('key')
		);
		
		$this->load->model('company/userteam', 'employee_model');
		$this->load->model('company/employee_category_model');
		$this->load->model('company/company', 'deco_model');
		
		$where = "1=1";
		$params = array();
		
		if( $filter['recycle'] == 1 ){
			$where .= " and recycle = 1";
			$params[] = "recycle=1";
		} else {
			$where .= " and recycle = 0";
		}
		
		if( $filter['key'] != '' ){
			$key = iconv('gbk', 'utf-8', $filter['key']);
			$params[] = "key=" . $key;
			$filter['key'] = $key;
			$where .= " and ( true_name like '%". $key ."%' or username in ( select username from company where company like '%". $key ."%' ) )";
		}
		
		
		$sql = "select * from ( select id, job_id, username, true_name, course, addtime, face_image, num=row_number() over(order by addtime desc) from [user_team_member] where ". $where ." ) as temp where num between ". (($args['page'] - 1) * $args['size'] + 1) ." and " . ( $args['size'] * $args['page'] );
		$sql_count = "select count(*) as icount from [user_team_member] where " . $where;
		
		$result = $this->employee_model->gets($sql, $sql_count);
		$result['list'] = $this->employee_category_model->assign_employee($result['list'], array(
			'fields'=>'id, job_name'
		));
		$result['list'] = $this->deco_model->fill_collection($result['list'], array('fields'=>array('username', 'company', 'delcode')),false);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->pageSize = $args['size'];
		
		if( count($params) == 0 ){
			$this->pagination->url_template = $this->get_complete_url('member/employee/manage?page=<{page}>');
			$this->pagination->url_template_first = $this->get_complete_url('member/employee/manage');
		} else {
			$this->pagination->url_template = $this->get_complete_url('member/employee/manage?page=<{page}>&' . implode('&', $params));
			$this->pagination->url_template_first = $this->get_complete_url('member/employee/manage?' . implode('&', $params));
		}
		
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('args', $args);
		
		$this->tpl->assign('module', 'employee.manage');
		
		if( $filter['recycle'] == 1 ){
			$this->tpl->assign('module', 'employee.recycle');
		}
		$this->tpl->assign('filter', $filter);
		$this->tpl->display('admin/member/employee/manage.html');
	}
	
	public function active(){
		
		$info = $this->get_form_data();
		
		$method = $info['type'];
		
		$this->load->model('company/userteam', 'employee_model');
		
		try{
			$this->employee_model->$method($info['ids']);
			echo('success');
		}catch(Exception $e){
			echo( $e->getMessage() );
		}
	}
	
}

?>