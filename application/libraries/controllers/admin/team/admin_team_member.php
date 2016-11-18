<?php
// 施工团队，成员操作控制器
class admin_team_member extends admin_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'member_manage');
	}
	
	// 施工团队成员管理
	public function manage(){
		$r = $this->gr('r');
		$team_id = $this->gr('tid');
		$this->load->model('team/team_model');
		$this->load->model('team/team_member_model');
		$this->load->model('team/team_config_model');
		$team = $this->team_model->get_team($team_id, 'id, name, captain');
		
		$members = $this->team_member_model->get_team_members($team_id);	// 获取某个团队下的所有成员
		if( $members ){
			$members = $this->team_config_model->seniority_assign($members);
			$members = $this->team_config_model->type_assign($members);
		}
		
		$this->tpl->assign('members', $members);
		$this->tpl->assign('team', $team);
		$this->tpl->assign('rurl', $r);
		
		$this->tpl->display('admin/team/team_member_manage.html');
	}
	
	// 施工团队 添加成员 界面
	public function add(){
		$r = $this->gr('r');
		$tid = $this->gr('tid');
		$this->load->model('team/team_config_model');
		$this->load->model('team/team_model');
		
		$types = $this->team_config_model->types;				// 类型多选框
		$seniority = $this->team_config_model->seniority;		// 工龄单选项
		$team = $this->team_model->get_team($tid);				// 当前的团队信息
		
		$this->tpl->assign('r', $r);
		$this->tpl->assign('types', $types);
		$this->tpl->assign('seniority', $seniority);
		$this->tpl->assign('team', $team);
		$this->tpl->display('admin/team/team_member_add.html');
	}
	
	// 添加团队成员 表单提交
	public function add_submit(){
		$r = $this->gf('r');
		$member = array(
			'tid'=>$this->gf('tid'),
			'name'=>$this->gf('name'),
			'sex'=>$this->gf('sex'),
			'code'=>$this->gf('code'),
			'sheng'=>$this->gf('User_Shen'),
			'city'=>$this->gf('User_City'),
			'town'=>$this->gf('User_Town'),
			'tel'=>$this->gf('tel'),
			'type'=>$this->gf('type'),
			'seniority'=>$this->gf('seniority'),
			'description'=>$this->gf('description'),
			'face'=>$this->gf('face'),
			'addtime'=>date('Y-m-d H:i:s'),
			'admin'=>$this->admin_username
		);
		$error = array();
		
		if( empty($member['name']) ){
			$error[] = '请输入姓名';
		}
		
		if( ! preg_match('/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/i', $member['code']) ){
			$error[] = '身份证号码格式不正确';
		} else {
			$member['birthday'] = $this->encode->get_birthday($member['code']);
		}
		
		if( empty($member['sheng']) || empty($member['city']) || empty($member['town']) ){
			$error[] = '请输入籍贯省/市/县(区)';
		}
		
		if( empty($member['tel']) ){
			$error[] = '请输入电话/手机';
		}
		
		if( count($member['type']) == 0 ){
			$error[] = '请选择工人种类';
		}
		
		if( count($error) == 0 ){
			$this->load->model('team/team_member_model');
			$id = $this->team_member_model->add($member);	// 添加一个新工人 OR 已经存在的工人将绑定到当前团队中
			$this->alert('提交成功', str_replace('&amp;','&',$r));
		} else {
			$this->alert('提交失败\n' . implode('\n', $error), $r);
		}
	}
	
	// 身份证号码是否存在，是否属于既定团队 检测
	public function code_exists(){
		$code = $this->gf('code');
		$tid = $this->gf('tid');
		$result = array();
		
		$this->load->model('team/team_member_model');
		
		$member = $this->team_member_model->get_member2($code, 'id');
		
		if( $member == false ){
			$result['code'] = 0;		// 不存在的用户
		} else {
			$check_in_team = $this->team_member_model->in_team($member['id'], $tid);
			if( $check_in_team == false ){
				$result['code'] = 1;	// 用户存在，但是不属于当前团队
				$result['member_id'] = $member['id'];
			} else {
				$result['code'] = 2;	// 用户存在，且已经在当前团队中了
			}
		}
		echo(json_encode($result));
	}
	
	// 将人员绑定到团队
	public function bind_to_team(){
		$member_id = $this->gf('member_id');
		$team_id = $this->gf('team_id');
		$this->load->model('team/team_member_model');
		try{
			$this->team_member_model->member_team_bind($member_id, $team_id);
			echo(1);
		}catch(Exception $e){
			echo($e->getMessage());
		}
	}
	
	// 在指定的团队中解绑
	public function unbind_to_team(){
		$member_id = $this->gf('member_id');
		$team_id = $this->gf('team_id');
		$this->load->model('team/team_member_model');
		try{
			$this->team_member_model->member_team_unbind($member_id, $team_id);
			echo(1);
		}catch(Exception $e){
			echo($e->getMessage());
		}
	}
	
	// 将人员提升为队长
	public function set_captain(){
		$member_id = $this->gf('member_id');
		$team_id = $this->gf('team_id');
		$this->load->model('team/team_model');
		try{
			$this->team_model->set_captain($member_id, $team_id);
			echo('1');
		}catch(Exception $e){
			echo($e->getMessage());
		}
	}
	
}
?>