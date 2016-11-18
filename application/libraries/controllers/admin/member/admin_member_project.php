<?php

// 会员案例管理
class admin_member_project extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function manage(){
		
		$this->tpl->assign('module', 'project.manage');
		
		$this->load->model('company/usercase', 'project_model');
		$this->load->model('company/company', 'deco_model');
		
		$args = array(
			'size'=>20,
			'page'=>$this->encode->get_page()
		);
		
		$search = array(
			'recycle'=>$this->gr('recycle'),
			'key'=>$this->gr('key')
		);
		
		$where_sql = "1=1";
		$params_string = array();
		
		if( $search['recycle'] == 1 ){
			$where_sql .= " and recycle = 1";
			$params_string[] = "recycle=1";
			$this->tpl->assign('module', 'project.manage.recycle');
		} else {
			$where_sql .= " and recycle = 0";
		}
		
		if( ! empty($search['key']) ){
			$search['key'] = $this->encode->gbk_to_utf8($search['key']);
			$where_sql .= " and ( casename like '%". $search['key'] ."%' or username in ( select username from company where company like '%". $search['key'] ."%' ) )";
			$params_string[] = "key=" . $search['key'];
		}
		
		$sql = "select * from ( select id, username, casename as name, addtime, build_type_1, build_type_2, style_name, num = row_number() over( order by addtime desc ) from [user_case] where ". $where_sql ." ) as tmp where num between ". (($args['page'] - 1) * $args['size'] + 1) ." and " . ( $args['page'] * $args['size'] );
		
		$sql_count = "select count(*) as icount from [user_case] where " . $where_sql;
		
		$result = $this->project_model->get_case_list($sql, $sql_count);
		$result['list'] = $this->deco_model->fill_collection($result['list'], array(
			'fields'=>array('username', 'company')
		), false);
		$result['list'] = $this->project_model->image_count_assign_case($result['list']);
		
		// var_dump($result['list']);
		
		$this->load->library('pagination');
		
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->pageSize = $args['size'];
		
		if( count( $search['key'] ) != 0 ){
			$this->pagination->url_template_first = $this->get_complete_url('/member/project/manage?' . implode('&', $params_string));
			$this->pagination->url_template = $this->get_complete_url('/member/project/manage?page=<{page}>&' . implode('&', $params_string));
		} else {
			$this->pagination->url_template_first = $this->get_complete_url('/member/project/manage');
			$this->pagination->url_template = $this->get_complete_url('/member/project/manage?page=<{page}>');
		}
		
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('list', $result['list']);
		
		$this->tpl->assign('args', $args);
		
		$this->tpl->assign('search', $search);
		$this->tpl->display( $this->get_tpl('member/project/project_manage.html') );
	}
	
	// 案例管理操作 包括 （ 回收站，审核通过等 ）
	public function active(){
		$info = $this->get_form_data();
		$this->load->model('company/usercase', 'project_model');
		
		switch($info['active']){
			case 'recycle':	// 移动到回收站
				try{
					$this->project_model->recycle($info['ids']);	// 移入回收站
					echo('success');
				}catch(Exception $e){
					echo($e->getMessage());
				}
				break;
			case 'passed':
				try{
					$this->project_model->recycle($info['ids'], 0);
					echo('success');
				}catch(Exception $e){
					echo( $e->getMessage() );
				}
			default:
		}
	}
	
}

?>