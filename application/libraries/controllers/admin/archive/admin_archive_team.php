<?php

// 档案的 参与者 
// 参与者定义：施工团队，设计师，造价员等等
class admin_archive_team extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 总体浏览
	public function manage(){
		
		$this->load->model('archive/archive_diary_model');
		$this->load->model('archive/archive_relation_model');
		$this->load->model('team/team_model');
		$this->load->model('team/team_member_model');
		
		$args = array(
			'id'=>$this->gr('id')
		);
		$r = $this->gr('r');
		$diary = $this->archive_diary_model->get_diary($args);	// 获取日记档案基本信息
		
		// 获取已经绑定的团队信息
		$teams = $this->team_model->archive_relation_model->get_team_relations($diary->id);
		
		$this->tpl->assign('diary', $diary);
		$this->tpl->assign('module', 'team_manage');
		$this->tpl->assign('teams', $teams);
		$this->tpl->assign('r', $r);
		$this->tpl->display('admin/archive/archive_team_manage.html');
	}
	
	// 进入添加施工团队界面
	public function add_item(){
		$r = $this->gr('r');
		$args = array(
			'id'=>$this->gr('id')
		);
		$this->load->model('archive/archive_diary_model');
		$diary = $this->archive_diary_model->get_diary($args);
		$this->tpl->assign('diary', $diary);
		$this->tpl->assign('module', 'team_manage');
		$this->tpl->assign('r', $r);
		$this->tpl->display('admin/archive/archive_team_add_team.html');
	}
	
	// 搜索施工团队数据
	public function team_search(){
		$id = $this->gf('tid');	// team id
		$result = array();
		if( ! preg_match('/^tm\-\d+$/i', $id) ){
			$result['type'] = 'error';
			$result['message'] = 'ID格式错误';
		}
		$id = str_replace('tm-', '', strtolower($id));
		$id = ltrim($id, '0');
		
		$this->load->model('team/team_model');
		$this->load->model('team/team_config_model');
		$this->load->model('team/team_member_model');
		$team = $this->team_model->get_team($id, 'id, name, captain');
		if( $team != false ){
			$members = $this->team_member_model->get_team_members($team['id'], 'id, name');
			$members = $this->team_config_model->type_assign($members);
		} else {
			$members = false;
		}
		
		$result['type'] = 'data';
		$result['team'] = $team;
		$result['members'] = $members;
		
		echo(json_encode($result));
	}
	
	// 施工团队和档案绑定
	public function team_add_submit(){
		$info = array(
			'diary_id'=>$this->gf('diary_id'),
			'team_id'=>$this->gf('team_id'),
			'members'=>$this->gf('members')
		);
		$this->load->model('archive/archive_relation_model');
		try{
			$id = $this->archive_relation_model->diary_team_bind($info);
			echo('success');
		}catch(Exception $e){
			echo($e->getMessage());
		}
	}
	
	// 修改已经绑定了的团队数据 界面
	public function edit(){
		$tid = $this->gr('tid');
		$did = $this->gr('did');
		$r = $this->gr('r');
		$this->load->model('archive/archive_relation_model');
		$this->load->model('archive/archive_diary_model');
		
		$this->load->model('team/team_model');
		$this->load->model('team/team_config_model');
		$this->load->model('team/team_member_model');
		
		$team_relation = $this->archive_relation_model->get_team_record($did, $tid);			// 获取关系
		
		$diary = $this->archive_diary_model->get_diary(array('id'=>$did));						// 获取装修档案基本信息
		$team = $this->team_model->get_team($tid, 'id, name, captain');
		$team['members'] = $this->team_member_model->get_team_members($team['id'], 'id, name');	// 获取团队总人数
		$team['members'] = $this->team_config_model->type_assign($team['members']);
		$members = explode(',', trim($team_relation['team_members'], ','));
		// 设置被选中的团队的成员名单
		foreach($team['members'] as $key=>$val){
			$val['checked'] = false;
			foreach($members as $k=>$v){
				if( $v == $val['id'] )
				$val['checked'] = true;
			}
			$team['members'][$key] = $val;
		}
		$this->tpl->assign('diary', $diary);
		$this->tpl->assign('team', $team);
		$this->tpl->assign('module', 'team_manage');
		$this->tpl->assign('r', $r);
		$this->tpl->display('admin/archive/archive_team_relation_edit.html');
	}
	
	// 移除团队的参与者(整个团队)
	public function remove(){
		$did = $this->gr('did');
		$tid = $this->gr('tid');
		$r = $this->gr('r');
		$this->load->model('archive/archive_relation_model');
		try{
			$this->archive_relation_model->delete_team($did, $tid);	// 移除指定参与的团队
			echo('success');
		}catch(Exception $e){
			echo($e->getMessage());
		}
		
		
	}


}

?>