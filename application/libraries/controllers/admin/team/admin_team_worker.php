<?php

// 施工团队，工人管理
class admin_team_worker extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 列表管理页面
	public function manage(){
		$this->load->model('team/team_config_model');
		$this->load->model('team/team_member_model');
		$this->load->library('pagination');
		$cfg = array(
			'size'=>20,
			'page'=>$this->gr('page')
		);
		if( ! preg_match('/^[1-9]\d*$/', $cfg['page']) ) $cfg['page'] = 1;
		
		$sql = "select * from ( select id, name, code, sheng, city, town, seniority, addtime, birthday, row_number() over(order by id desc) as num from team_members ) as temp where num between ". (($cfg['page'] - 1) * $cfg['size'] + 1) ." and " . $cfg['size'] * $cfg['page'];
		$sql_count = "select count(*) as icount from team_members";
		
		$result = $this->team_member_model->get_workers($sql, $sql_count);
		$result['list'] = $this->team_config_model->type_assign($result['list']);
		
		
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->url_template_first = $this->manage_url . 'team/worker/manage';
		$this->pagination->url_template = $this->manage_url . 'team/worker/manage?page=<{page}>';
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $result['count']);
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('module', 'worker');
		$this->tpl->assign('workers', $result['list']);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->display('admin/team/team_worker_manage.html');
	}
	
	// 工人详情页面
	// 1,工人基本信息修改   2,工人所属团队列表
	public function edit(){
		$id = $this->gr('id');
		$r = $this->gr('r');
		$this->load->model('team/team_member_model');
		$this->load->model('team/team_config_model');
		$this->load->model('team/team_model');
		
		$worker = $this->team_member_model->get_member($id, '*');
		$worker = $this->team_config_model->type_assign($worker);
		
		$teams = $this->team_model->get_worker_teams($worker['id']);	// 获取工人所属的团队列表
		
		$types = $this->team_config_model->types;				// 类型多选框
		$seniority = $this->team_config_model->seniority;		// 工龄单选项
		
		$this->tpl->assign('module', 'worker_edit');
		$this->tpl->assign('worker', $worker);
		$this->tpl->assign('teams', $teams);
		$this->tpl->assign('r', str_replace('&amp;', '&', $r));
		$this->tpl->assign('types', $types);
		$this->tpl->assign('seniority', $seniority);
		
		$this->tpl->display('admin/team/team_worker_edit.html');
	}
	
	// 装修工人资料修改表单提交处理
	public function edit_submit(){
		
		$this->load->model('team/team_member_model');
		
		$r = $this->gf('r');
		$r = str_replace('&amp;', '&', $r);
		
		$member = array(
			'id'=>$this->gf('work_id'),
			'name'=>$this->gf('name'),
			'sex'=>$this->gf('sex'),
			'code'=>$this->gf('code'),
			'sheng'=>$this->gf('User_Shen'),
			'city'=>$this->gf('User_City'),
			'town'=>$this->gf('User_Town'),
			'tel'=>$this->gf('tel'),
			'type'=>$this->gf('type'),
			'old_types'=>$this->gf('old_types'),
			'seniority'=>$this->gf('seniority'),
			'description'=>$this->gf('description'),
			'face'=>$this->gf('face')
		);
		$error = array();
		
		if( empty($member['name']) ){
			$error[] = '请输入姓名';
		}
		
		if( ! preg_match('/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/i', $member['code']) ){
			$error[] = '身份证号码格式不正确';
		} else {
			
			// 检测身份证
			//var_dump($member['code'] . '*' . $member['id']);
			if( ! $this->check_code_exists(false, $member['code'], $member['id']) ){
				$error[] = '已经存在的身份证号码';
			} else {
				$member['birthday'] = $this->encode->get_birthday($member['code']);
			}
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
			$this->team_member_model->edit($member);
			$this->alert('提交成功', $r);
		} else {
			$this->alert('提交失败\n' . implode('\n', $error), $r);
		}
	}
	
	// 检测是否是已经存在的身份证号码
	public function check_code_exists($print = true, $code = '', $worker_id = ''){
		
		if( empty($code) || empty($worker_id) ){
			$code = $this->gf('code');
			$worker_id = $this->gf('worker_id');
		}
		
		$this->load->model('team/team_member_model');
		$temp = $this->team_member_model->get_member2($code);
		$result = false;
		
		if( $temp == false ){	// 没有找到新的身份证数据
			$result = true;
		} else {
			if( $temp['id'] == $worker_id ){		// 身份证号码无修改
				$result = true;
			} else {
				$result = false;
			}
		}
		
		// 结果输出方式，直接输出/返回值
		if( $print == true ){
			if( $result == true ){
				echo('1');
			} else {
				echo('0');
			}
		} else {
			return $result;
		}
	}
	
	public function delete(){
		$id = $this->gr('id');
		$r = $this->gr('r');
		$this->load->model('team/team_member_model');
		try{
			$this->team_member_model->delete($id);		// 删除工人
			$this->alert('', $r);
		}catch(Exception $e){
			exit($e->getMessage());
		}
	}
	
	
	
}
?>