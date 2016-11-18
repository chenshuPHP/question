<?php

// 施工团队

class admin_team_team extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function manage(){
		
		$this->load->model('team/team_model');
		$this->load->model('team/team_member_model');
		$this->load->model('manager/manager_model');
		$this->load->library('pagination');
		
		$cfg = array(
			'page'=>$this->gr('page'),
			'size'=>20
		);
		
		if( !preg_match('/^[1-9]\d*$/', $cfg['page']) ) $cfg['page'] = 1;
		
		$sql = "select * from ( select id, name, addtime, admin, captain, row_number() over( order by id desc ) as num from team_teams ) as tmp where num between ". ( ($cfg['page'] - 1) * $cfg['size'] + 1 ) ." and " . ( $cfg['page'] * $cfg['size'] );
		$sql_count = "select count(*) as icount from team_teams";
		
		$result = $this->team_model->get_teams($sql, $sql_count);
		$result['list'] = $this->manager_model->assign($result['list']);					// 将管理员信息加载到数据中 
		$result['list'] = $this->team_member_model->member_count_assign($result['list']);	// 将成员数量数据加载
		
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->url_template_first = $this->manage_url . 'team/team/manage';
		$this->pagination->url_template = $this->manage_url . 'team/team/manage?page=<{page}>';
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $result['count']);
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->assign('module', 'manage');
		$this->tpl->display('admin/team/team_manage.html');
	}
	
	// 新建团队
	public function add(){
		$this->tpl->assign('title', '新建团队');
		$this->tpl->assign('module', 'add');
		$this->tpl->display('admin/team/team_add.html');
	}
	
	// 创建企业团队表单提交
	public function add_handler(){
		
		$team = array(
			'name'=>$this->gf('name'),
			'description'=>$this->gf('description'),
			'cdate'=>$this->gf('cdate'),
			'admin'=>$this->admin_username,
			'addtime'=>date('Y-m-d H:i:s'),
			'logo'=>$this->gf('logo'),
			'wx_code'=>$this->gf('wx_code'),		// 微信
			'qr_code_image'=>$this->gf('qr_code')	// 二维码
		);
		
		if( empty($team['name']) ){
			exit('请输入团队名称');
		}
		
		$cdate_tmp = time() - (3600 * 24 * 365) * ($team['cdate']);
		$team['cdate'] = date('Y-m-d', $cdate_tmp);
		
		$this->load->model('team/team_model');
		
		try{
			$id = $this->team_model->add($team);
			$this->alert('创建成功', $this->manage_url .'team/team/manage');
		}catch(Exception $e){
			exit($e->getMessage());
		}
	}
	
	// 团队基本信息编辑
	public function edit(){
		$r = $this->gr('r');
		$id = $this->gr('tid');
		$this->load->model('team/team_model');
		$team = $this->team_model->get_team($id, '*');
		$this->tpl->assign('team', $team);
		$this->tpl->assign('r', $r);
		$this->tpl->assign('module', 'team_edit');
		$this->tpl->display('admin/team/team_edit.html');
	}
	
	// 施工团队信息编辑提交
	public function edit_handler(){
		$r = $this->gf('r');
		$team = array(
			'id'=>$this->gf('id'),
			'name'=>$this->gf('name'),
			'cdate'=>$this->gf('cdate'),
			'description'=>$this->gf('description'),
			'logo'=>$this->gf('logo'),
			'wx_code'=>$this->gf('wx_code'),		// 微信
			'qr_code_image'=>$this->gf('qr_code')	// 二维码
		);
		if( empty($team['name']) ){
			$this->alert('团队名称不能为空');
			exit();
		}
		
		$cdate_tmp = time() - (3600 * 24 * 365) * ($team['cdate']);
		$team['cdate'] = date('Y-m-d', $cdate_tmp);
		
		$this->load->model('team/team_model');
		
		try{
			$this->team_model->edit($team);
			$this->alert('修改成功', $r);
		}catch(Exception $e){
			$this->alert('修改失败' . $e->getMessage(), $r);
		}
	}
	
	// 删除施工队
	// 删除logo，删除二维码图片
	public function delete(){
		
		$team_id = $this->gr('tid');
		$r = $this->gr('r');
		
		$this->load->model('team/team_model');
		
		try {
			$this->team_model->delete($team_id);
			$this->alert('删除成功', $r);
		}catch(Exception $e){
			$this->alert($e->getMessage(), $r);
		}
		
	}
	
}

?>