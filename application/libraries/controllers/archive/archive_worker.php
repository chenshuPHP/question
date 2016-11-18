<?php
if( ! defined('BASEPATH') ) exit('不允许直接浏览的文件');
class archive_worker extends archive_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('hide_kf', 1);
	}
	
	// 装修工人列表
	public function home(){
		
		$type_id = $this->gr('type');
		
		$this->load->model('team/team_config_model');
		$this->load->model('team/team_member_model');
		
		$this->load->library('thumb');
		
		$current_type = $this->team_config_model->get_type($type_id);

		if( ! $current_type ) show_404();
		
		$types = $this->team_config_model->get_types();
		$this->team_member_model->member_count_assign_types($types);
		
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>20
		);

		// 读取工人列表

		$sql = "select * from ( select id, name, sex, seniority, birthday, row_number() over(order by addtime desc) as num from team_members where id in ( select member_id from team_member_relations where relation_type = 'type' and relation_id = '". $current_type['id'] ."' ) ) as temp where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );

		$sql_count = "select count(*) as icount from team_members where id in ( select member_id from team_member_relations where relation_type = 'type' and relation_id = '". $current_type['id'] ."' )";
		
		$result = $this->team_member_model->get_workers($sql, $sql_count);

		$members = $this->team_config_model->type_assign($result['list']);
		$members = $this->team_config_model->seniority_assign($members);
		
		// 分页
		$this->load->library('pagination');
		$this->pagination->pageSize = $args['size'];
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $result['count'];
		$urls = $this->config->item('url');
		$this->pagination->url_template_first = $urls['archive'] . '/worker?type=' . $type_id;
		$this->pagination->url_template = $urls['archive'] . '/worker?type=' . $type_id . '&page=<{page}>';
		$pagination = $this->pagination->toString(true);

		$this->tpl->assign('types', $types);
		$this->tpl->assign('current_type', $current_type);
		$this->tpl->assign('members', $members);
		$this->tpl->assign('title', $current_type['name']);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('args', $args);
		$this->tpl->display('archive/archive_worker_types.html');
		
	}
	
	// 装修工人的介绍单页
	public function detail(){

		$id = $this->gr('id');

		$this->load->model('team/team_config_model');
		$this->load->model('team/team_member_model');
		$this->load->model('team/team_model');
		
		$this->load->library('thumb');
		
		$worker = $this->team_member_model->get_member($id, '*');
		$worker = $this->team_config_model->seniority_assign($worker);
		$worker = $this->team_config_model->type_assign($worker);
		
		$worker['tel'] = str_replace(substr($worker['tel'], -4), '****', $worker['tel']);
		
		$teams = $this->team_model->get_worker_teams($worker['id'], 'id, name, captain, logo');		//获取工人所属的团队列表
		
		// 为施工团队附加工人数据
		$teams = $this->team_member_model->member_assign($teams, array(
			'top'=>25,
			'fields'=>'id, name, face',
			'captain_opt'=>true,
			'assign_types_opt'=>false
		));

		echo('<!--');
		var_dump($teams);
		echo('-->');


		
		// 案例图集（所属团队的图集）
		$this->tpl->assign('worker', $worker);
		$this->tpl->assign('teams', $teams);
		$this->tpl->assign('title', $worker['name']);
		$this->tpl->display('archive/archive_worker_detail.html');
	}
}
?>