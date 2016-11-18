<?php

// 项目展示
class sp_project extends sp_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'project');
	}
	
	// 案例展示
	public function cases($args = array())
	{
		
		$args = array(
			'page'			=> $this->encode->get_page(),
			'size'			=> 24
		);
		
		$this->load->library('thumb');
		$this->load->model('company/usercase', 'project_model');
		$this->load->model('company/project_category_model');
		
		$where = "username = '". $this->user ."' and (edate < '". date('Y-m-d') ."' or edate is null)";
		$sql = "select * from ( select id, username, casename, province, jieduan, roomtype, ".
		"build_type_1, build_type_2, style_name as style, styletype, budget, area, gongqi, baoxiu, yezhu, ".
		"zhiye, renshu, address, addtime, update_time as updatetime, showcount, fm_image as fm, num = row_number() over( order by sort_id asc ) ".
		"from [". $this->project_model->table_name ."] where ". $where ." ) as tmp ".
		"where num between ". ( ($args['page'] - 1) * $args['size'] +1 ) ." and " . ( $args['page'] * $args['size'] );
		
		$sql_count = "select count(*) as icount from [". $this->project_model->table_name ."] where " . $where;
		$result = $this->project_model->get_case_list($sql, $sql_count);
		$result['list'] = $this->project_category_model->assign_to_project($result['list']);
	
		//var_dump2($result);
		
		$result['list'] = $this->project_model->image_count_assign_case( $result['list'] );
		
		/*
		$list = $this->usercase_model->getUserCases($this->user, array(
			'where'=>"(edate < '". date('Y-m-d') ."' or edate is null)"
		));
		$list = $this->usercase_model->image_count_assign_case($list);
		*/
		
		foreach($result['list'] as $key=>$val){
			$val['thumb'] = $this->thumb->crop($val['fm'], 310, 195);
			$result['list'][$key] = $val;
		}
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->url_template = $this->get_complete_url('/case.html?page=<{page}>');
		$this->pagination->url_template_first = $this->get_complete_url('/case.html');
		
		$this->tpl->assign('pagination', $this->pagination->toString(TRUE));
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('module', 'case');
	
		$this->tpl->display($this->get_tpl('case.html'));
		
	}
	
	// 在建工地的主页
	public function home($args = array()){
		
		$this->load->model('company/usercase', 'project_model');
		$this->load->model('company/userteam', 'employee_model');
		$this->load->model('company/project_category_model');
		
		$sql = "select id, username, casename as name, fm_image as fm, sheng, city, town, build_type_1 as t1, build_type_2 as t2, style_name as style, area, budget, sdate, edate from [user_case] where username = '". $this->user ."' and edate > '". date('Y-m-d') ."' order by sort_id asc";
		
		$projects = $this->project_model->get_case_list($sql);
		$list = $projects['list'];
		
		$list = $this->project_category_model->assign_to_project($list);
		
		$list = $this->project_model->image_count_assign_case($list);
		$list = $this->employee_model->employee_assign_project($list,
			array(
				'size'=>5,
				'fields'=>'id, job_id, username, true_name as name, face_image as face'
			)
		);
		
		$project_total = $this->project_model->case_count_assign_users(array('username'=>$this->user));
		$project_total = $project_total['case_count'];
		
		$this->load->library('thumb');
		
		// 缩略图
		foreach($list as $key=>$value){
			
			if( $value['fm'] != '' ){
				$value['thumb'] = $this->thumb->crop($value['fm'], 200, 200);
			} else {
				$value['thumb'] = '';
			}
			
			if( $value['employees'] )
			{
				foreach($value['employees'] as $k=>$v){
					if( $v['face'] != '' ){
						$value['employees'][$k]['thumb'] = $this->thumb->crop($v['face'], 40, 40);
					} else {
						$value['employees'][$k]['thumb'] = '';
					}
				}
			}
			$list[$key] = $value;
		}
		
		$this->tpl->assign('list', $list);
		$this->tpl->assign('ing_count', $projects['count']);
		$this->tpl->assign('project_total', $project_total);
		$this->tpl->display( $this->get_tpl('project/home.html') );
	}
	
	public function detail($args = array()){
		
		$id = $args[0];
		
		$this->load->library('thumb');
		$this->load->model('company/usercase', 'project_model');
		$this->load->model('company/project_category_model');
		$this->load->model('company/userteam', 'employee_model');
		
		$project = $this->project_model->getCase($id, array(
			'fields'=>'id, username, casename as name, sheng, city, town, build_type_1 as t1, build_type_2 as t2, style_name as style, budget, area, fm_image as fm, mobile, sdate, edate, roomtype, styletype, gongqi, province',
			'username'=>$this->user
		));
		
		if( ! $project )
		{
			show_error('找不到此案例', 404, '404 Page not found');
			exit();
		}
		
		$project = $this->project_model->image_count_assign_case( $project );	// 图片数量
		$project = $this->project_category_model->assign_to_project($project);
		
		$project = $this->employee_model->employee_assign_project($project,
			array(
				'fields'=>'id, job_id, username, true_name as name, face_image as face'
			)
		);
		
		if( $project['employees'] != '' )
		{
			foreach( $project['employees'] as $key=>$value )
			{
				$project['employees'][$key]['thumb'] = '';
				if( $value['face'] != '' )
				{
					$project['employees'][$key]['thumb'] = $this->thumb->crop($value['face'], 60, 60);
				}
			}
		}
		
		$project['stages'] = $this->project_model->get_stages(array(
			'cid'=>$project['id'],
			'fields'=>'id, caseid as cid, jieduan as name'
		));
		
		$project['stages'] = $this->project_model->image_assign_stages($project['stages'], array(
			'fields'			=> 'id, jieduanid as sid, imgpath as path, title as name, description',
			'format'			=> TRUE,
			'format_args'		=> array('username'=>$this->user, 'cid'=>$id)
		));
		
		if( $project['date_limit'] < 0 ){
			$this->tpl->assign('module', 'case');
		}
		
		$this->tpl->assign('project', $project);
		$this->tpl->display( isset( $args['tpl'] ) ? $args['tpl'] : $this->get_tpl('project/detail.html') );
		
	}
	
	// 图片浏览
	public function image($args = array()){
		
		$id = $args[0];
		$this->load->model('company/project_category_model');
		$this->load->model('company/usercase', 'project_model');
		
		$image = $this->project_model->get_image($id);
		$stage = $this->project_model->get_stage($image['sid']);
		$project = $this->project_model->getCase($stage['cid'], array(
			'fields'=>'id, username, casename as name, sheng, city, town, build_type_1 as t1, build_type_2 as t2, style_name as style, budget, roomtype, styletype, area, gongqi, fm_image as fm, address, sdate, edate',
			'username'=>$this->user
		));
		
		if( ! $project ){
			show_error('project not found', 404);
		}
		
		$project['stages'] = $this->project_model->get_stages(array(
			'cid'=>$project['id']
		));
		
		$project['stages'] = $this->project_model->image_assign_stages($project['stages']);
		
		$project = $this->project_category_model->assign_to_project($project);
		
		
		$images = array();
		foreach($project['stages'] as $stage){
			foreach($stage['images'] as $image){
				$images[] = $image;
			}
		}
		
		$current_index = 0;
		foreach($images as $key=>$image){
			if( $image['id'] == $id ){
				$current_index = $key;
			}
		}
		
		// 公司信息
		$deco = $this->deco_model->get_company($this->user, 'username, logo, company');
		
		//var_dump($deco);
		$this->tpl->assign('deco', $deco);
		
		// 参与人员
		$this->load->model('company/userteam', 'employee_model');
		$this->load->model('company/employee_category_model');
		$project = $this->employee_model->employee_assign_project($project, array(
			'fields'=>'id, username, job_id, true_name as name, face_image as face'
		));
		$project['employees'] = $this->employee_category_model->assign_employee($project['employees']);
		
		//var_dump($project);
		
		// 相关案例
		$pros = $this->project_model->getUserCases($this->user, array(
			'top'=>5,
			'fields'=>'id, username, casename as name, fm_image as fm',
			'noids'=>array($project['id'])
		));
		
		$pros = $this->project_model->image_count_assign_case($pros);
		
		//echo('<!--');
		//var_dump($pros);
		//echo('-->');
		
		$this->tpl->assign('current_index', $current_index);
		$this->tpl->assign('project', $project);
		$this->tpl->assign('images', $images);
		// $this->tpl->assign('hide_kf', '1');
		
		// 其他相关案例
		$this->tpl->assign('pros', $pros);
		
		$this->tpl->display( $this->get_tpl('project/image.html') );
		
		
	}
	
	
}

?>