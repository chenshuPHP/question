<?php

// 装修公司 员工资料
class employee_controller extends MY_Controller {
	
	public function _remap($method, $params = array()){
		
		if( method_exists($this, strtolower($method)) ){
			$this->$method();
		} else {
			show_404();
		}
		
	}
	
	private function get_tpl($tpl){
		return 'company/employee/' . ltrim($tpl, '/');
	}
	
	public function home(){
		
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>25
		);
		
		$this->load->model('company/userteam', 'employee_model');
		$this->load->model('company/company', 'deco_model');
		$this->load->model('company/employee_category_model');
		
		$where = "recycle = 0 and username in ( select username from company where delcode = 0 and hangye = '装潢公司' and register = 2 )";
		
		$sql = "select * from ( select id, job_id, username, true_name as name, course, face_image as face, num = row_number() over( order by addtime desc ) from [user_team_member] where ". $where ." ) as temp where num between ". ( $args['size'] * ($args['page'] - 1) + 1 ) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from [user_team_member] where " . $where;
		
		$result = $this->employee_model->gets($sql, $sql_count);
		$result['list'] = $this->deco_model->fill_collection($result['list'], array(
			'fields'=>array('company', 'username', 'shortname')
		), false);	// 绑定装修公司数据
		$result['list'] = $this->employee_category_model->assign_employee($result['list'], array(
			'fields'=>'job_name as name, id'
		));
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->recordCount = $result['count'];
		$urls = $this->config->item('url');
		$this->pagination->url_template = $urls['www'] . '/employee/home?page=<{page}>';
		$this->pagination->url_template_first = $urls['www'] . '/employee/home';
		
		$this->tpl->assign('pagination', $this->pagination->toString(true));
		$this->tpl->assign('list', $result['list']);
		$this->tpl->display( $this->get_tpl('home.html') );
	}
	
	
}

?>