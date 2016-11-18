<?php

// 施工团队工地（案例）
class admin_archive_cas extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function manage(){
		
		$cfg = array(
			'page'=>$this->encode->get_page(),
			'size'=>20
		);
		
		$this->load->model('team/team_model');
		$this->load->model('archive/archive_cas_model');
		$this->load->model('archive/archive_album_model');
		
		$sql = "select * from ( select id, tid, name, sheng, city, town, community, huxing, area, budget, addtime, sample, row_number() over(order by id desc) as num from archive_cas ) as temp where num between ". (($cfg['page'] - 1) * $cfg['size'] + 1) ." and " . $cfg['page'] * $cfg['size'];
		$sql_count = "select count(*) as icount from archive_cas";
		
		$result = $this->archive_cas_model->get_list($sql, $sql_count);
	
		// 附加施工队信息
		$result['list'] = $this->team_model->team_assign(
			$result['list'], array(
				'fields'=>'id, name',
				'item_key'=>'tid'
			)
		);
		
		// 附加图片数量
		$result['list'] = $this->archive_album_model->image_count_assign_albums($result['list']);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->pageSize = $cfg['size'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->url_template_first = $this->manage_url . 'archive/cas/manage';
		$this->pagination->url_template = $this->manage_url . 'archive/cas/manage?page=<{page}>';
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('count', $result['count']);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('module', 'manage');
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->display('admin/archive/cas/manage.html');
		
	}
	
	// 添加施工工地
	public function active(){
		
		$team_id = $this->gr('id');
		
		// 指定团队添加
		$tid = $this->gr('tid');
		
		if( ! empty($tid) ){
			$this->load->model('team/team_model');
			$team = $this->team_model->get_team($tid);
			$this->tpl->assign('team', $team);
			
		}
		
		$this->tpl->assign('module', 'add');
		
		// 编辑数据，初始化数据到表单
		if( ! empty($team_id) ){
			
			$this->load->model('archive/archive_cas_model');
			$this->load->model('team/team_model');
			
			$object = $this->archive_cas_model->get_cas($team_id);
			if( ! $object ){
				$this->alert('找不到要修改的工地信息');
				exit;
			}
			
			if( ! empty($object['tid']) ){
				$object['team'] = $this->team_model->get_team($object['tid'], 'id, name');
			} else {
				$object['team'] = false;
			}
			
			// 删除 介绍html
			
			$object['detail'] = $this->encode->removeHtml( $this->encode->htmldecode($object['detail']) );
			
			$this->tpl->assign('object', $object);
			$this->tpl->assign('module', 'edit');
		}
		
		$this->tpl->assign('rurl', $this->gr('r'));
		
		$this->tpl->assign('team_id', $team_id);
		$this->tpl->display('admin/archive/cas/active.html');
		
	}
	
	// 施工工地提交表单处理
	public function active_handler(){
		
		$object = array(
			'id'=>$this->gf('id'),
			'tid'=>$this->gf('team_id'),
			'name'=>$this->gf('name'),
			'sheng'=>$this->gf('User_Shen'),
			'city'=>$this->gf('User_City'),
			'town'=>$this->gf('User_Town'),
			'address'=>$this->gf('address'),
			'community'=>$this->gf('community'),
			'huxing'=>$this->gf('huxing'),
			'area'=>$this->gf('area'),
			'budget'=>$this->gf('budget'),
			'sdate'=>$this->gf('sdate'),	// 开工日期
			'edate'=>$this->gf('edate'),	// 完工日期
			'baoxiu'=>$this->gf('baoxiu'),	// 保修期
			'detail'=>$this->gf('edit_content'),
			'team_id'=>0,
			'sample'=>$this->gf('sample'),	// 是否装潢网线下体验馆
			'style'=>$this->gf('style'),	// 装修风格
			'wages'=>$this->gf('wages'),	// 人工费总价
			'video'=>$this->gf('video')		// 视频绑定地址
		);
		
		$this->load->model('team/team_model');
		$this->load->model('archive/archive_cas_model');
		
		if( ! empty($object['tid']) ){
			$team = $this->team_model->get_team_by_code($object['tid'], 'id');
			if( ! $team ){
				$this->alert('您提交的团队ID有误，请检查');
			} else {
				$object['team_id'] = $team['id'];
			}
		}
		
		$object['sample'] = ($object['sample'] == '') ? 0 : 1;
		
		try{
			
			// 添加模式
			if( empty($object['id']) ){
				$this->archive_cas_model->add($object);
				$this->alert('提交成功', $this->manage_url . '/archive/cas/manage');
			} else {
				$this->archive_cas_model->edit($object);
				$this->alert('修改成功', $this->gf('rurl'));
			}
			
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
	}
	
	// 删除
	public function delete(){
		$id = $this->gr('id');
		$r = $this->gr('r');
		try{
			//$this->load->model('archive/archive_cas_model');
			//$this->archive_cas_model->delete($id);
			$this->alert('删除功能暂未开通', $r);
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
	}

}



























?>