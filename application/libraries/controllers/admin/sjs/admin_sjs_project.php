<?php

// 设计师 - 项目（案例）管理
class admin_sjs_project extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 设计师案例管理
	public function manage(){
		
		$this->load->model('sjs/sjs_case', 'sjs_project_model');
		$this->load->model('sjs/sjs_user_model');
		
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>20
		);
		
		$filter = array(
			'recycle'=>$this->gf('recycle')
		);
		if( empty($filter['recycle']) ) $filter['recycle'] = $this->gr('recycle');
		
		$param = array();
		$where = '1=1';
		
		if( $filter['recycle'] == 1 ){
			$param[] = "recycle=1";
			$where .= " and recycle = 1";
			
			$this->tpl->assign('module', 'recycle');
			
		} else {
			
			$where .= " and ( recycle = 0 or recycle is null )";
			
			$this->tpl->assign('module', 'manage');
			
		}
		
		
		$sql = "select * from ( select id, username, case_name, addtime, fm, num = row_number() over(order by addtime desc) from [sjs_case] where ". $where ." ) as temp where num between ". (( $args['page'] - 1 ) * $args['size'] + 1) ." and " . ( $args['size'] * $args['page'] );
		$sql_count = "select count(*) as icount from [sjs_case] where " . $where;
		
		$result = $this->sjs_project_model->gets($sql, $sql_count);
		$result['list'] = $this->sjs_project_model->assign_image_count($result['list']);
		$result['list'] = $this->sjs_user_model->assign($result['list']);
		
		//echo('<!--');
		//var_dump($result);
		//echo('-->');

		$this->load->library('pagination');
		$this->pagination->pageSize = $args['size'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->currentPage = $args['page'];
		if( count($param) == 0 ){
			$this->pagination->url_template = $this->get_complete_url( '/sjs/project/manage?page=<{page}>' );
			$this->pagination->url_template_first = $this->get_complete_url( '/sjs/project/manage' );
		} else {
			$this->pagination->url_template = $this->get_complete_url( '/sjs/project/manage?page=<{page}>&' . implode("&", $param) );
			$this->pagination->url_template_first = $this->get_complete_url( '/sjs/project/manage?' . implode("&", $param) );
		}
		
		$this->tpl->assign('pagination', $this->pagination->toString(true));

		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('args', $args);
		$this->tpl->assign('filter', $filter);
		$this->tpl->display( $this->get_tpl('sjs/project/manage.html') );
		
	}
	
	// 删除到回收站
	public function handler(){
		$info = $this->get_form_data();
		$this->load->model('sjs/sjs_case', 'sjs_project_model');
		
		try{
			
			switch($info['active']) {
				case 'recycle':
					$this->sjs_project_model->recycle($info['ids']);
					echo('success');
					break;
				case 'recovery':
					$this->sjs_project_model->recovery($info['ids']);
					echo('success');
					break;
				default:
					echo('未知的处理请求');
					break;
			}
			
		}catch(Exception $e){
			echo( $e->getMessage() );
		}
		
	}
	
	
}
























?>