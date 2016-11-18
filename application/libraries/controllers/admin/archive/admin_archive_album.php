<?php

// 装修档案的图集功能
class admin_archive_album extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function manage(){
		
		$this->load->model('archive/archive_album_model');
		$this->load->model('team/team_model');
		
		$cfg = array(
			'page'=>$this->gr('page'),
			'size'=>20
		);
		if( ! preg_match('/^[1-9]\d*$/', $cfg['page']) ) $cfg['page'] = 1;
		
		$sql = "select * from ( select id, name, team_id, addtime, row_number() over(order by id desc) as num from archive_album ) as temp where num between ". (($cfg['page'] - 1) * $cfg['size'] + 1) ." and " . $cfg['page'] * $cfg['size'];
		
		$sql_count = "select count(*) as icount from archive_album";
		
		$result = $this->archive_album_model->get_list($sql, $sql_count);
		
		$result['list'] = $this->team_model->team_assign($result['list']);
		$result['list'] = $this->archive_album_model->image_count_assign_albums($result['list']);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->url_template_first = $this->manage_url . 'archive/album/manage';
		$this->pagination->url_template = $this->manage_url . 'archive/album/manage?page=<{page}>';
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $result['count']);
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('albums', $result['list']);
		$this->tpl->assign('module', 'manage');
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->display('admin/archive/archive_album_manage.html');
	}
	
	public function create(){
		
		// 指定施工团队添加
		$tid = $this->gr('tid');
		$rurl = $this->gr('r');
		
		if( ! empty($tid) ){
			$this->load->model('team/team_model');
			$team = $this->team_model->get_team($tid);
			$this->tpl->assign('team', $team);
		}
		
		$this->tpl->assign('module', 'create');
		$this->tpl->assign('rurl', $rurl);
		$this->tpl->display('admin/archive/archive_album_create.html');
	}
	
	// 创建相册
	public function create_active(){
		$album = array(
			'name'=>$this->gf('album_name'),
			'budget'=>$this->gf('budget'),
			'area'=>$this->gf('area'),
			'address'=>$this->gf('address'),
			'huxing'=>$this->gf('huxing'),
			'desc'=>$this->gf('album_desc'),
			'sdate'=>$this->gf('sdate')
			,'edate'=>$this->gf('edate')
			,'baoxiu'=>$this->gf('baoxiu')
			,'team_id'=>$this->gf('team_id')
		);
		
		$this->load->model('archive/archive_album_model');
		
		try{
			$this->archive_album_model->create($album);
			$this->alert('添加成功', $this->manage_url . 'archive/album/manage');
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
	}
	
	// 修改界面
	public function edit(){
		$this->load->model('archive/archive_album_model');
		
		$album = $this->archive_album_model->get_album($this->gr('id'), 'id, name, budget, area, address, huxing, team_id, description, sdate, edate, baoxiu');
		
		$team = false;
		
		if( ! empty($album['team_id']) ){
			$this->load->model('team/team_model');
			$team = $this->team_model->get_team($album['team_id']);
		}
		
		$this->tpl->assign('album', $album);
		$this->tpl->assign('team', $team);
		$this->tpl->assign('module', 'edit');
		$this->tpl->assign('r', $this->gr('r'));
		$this->tpl->display('admin/archive/archive_album_edit.html');
	}
	
	// 删除 图集
	public function delete(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		$this->load->model('archive/archive_album_model');
		$album = $this->archive_album_model->get_album($id, 'id');
		$album = $this->archive_album_model->image_count_assign_albums($album);
		if( $album['image_count'] == 0 ){
			try{
				// 删除图集阶段+图集本身
				$this->archive_album_model->delete($id);
				$this->alert('', $r);
			}catch(Exception $e){
				$this->alert($e->getMessage());
			}
		} else {
			exit('图片数量不为0');
		}
		
	}
	
	
	// 通过编号获取施工队
	public function get_team_by_code(){
		$code = $this->gf('code');
		$this->load->model('team/team_model');
		$team = $this->team_model->get_team_by_code($code, 'id, name');
		echo( json_encode($team) );
	}
	
	// 修改提交
	public function edit_active(){
		
		$album = array(
			'id'=>$this->gf('id'),
			'name'=>$this->gf('album_name'),
			'budget'=>$this->gf('budget'),
			'area'=>$this->gf('area'),
			'address'=>$this->gf('address'),
			'huxing'=>$this->gf('huxing'),
			'desc'=>$this->gf('album_desc'),
			'sdate'=>$this->gf('sdate')
			,'edate'=>$this->gf('edate')
			,'baoxiu'=>$this->gf('baoxiu')
			,'team_id'=>$this->gf('team_id')
		);
		
		$this->load->model('archive/archive_album_model');
		
		try{
			$this->archive_album_model->edit($album);
			$this->alert('修改成功', $this->gf('r'));
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
	}
	
	
	// 图集详情管理
	public function detail(){
		
		$r =$this->gr('r');
		$args = array('id'=>$this->gr('id'));
		
		
		//$album = $this->archive_album_model->get_album($args['id']);
		
		$this->load->model('archive/archive_album_model');
		$this->load->model('archive/archive_cas_model');
		// 转移到施工工地而非原先的album
		$album = $this->archive_cas_model->get_cas($args['id'], 'id, name, huxing, budget, fm');
		
		$stages = $this->archive_album_model->get_album_stages($album['id']);
		$stages = $this->archive_album_model->assign_images($stages);
		
		$this->tpl->assign('stages', $stages);
		$this->tpl->assign('module', 'images');
		$this->tpl->assign('album', $album);
		$this->tpl->assign('r', $r);
		$this->tpl->display('admin/archive/archive_album_detail.html');
	}
	
	// 新增图集的阶段界面
	public function add(){
		$album_id = $this->gr('id');
		$this->load->model('archive/archive_album_model');
		$stages = $this->archive_album_model->get_default_stages();
		$this->tpl->assign('album_id', $album_id);
		$this->tpl->assign('stages', $stages);
		$this->tpl->assign('popup_page', '1');		// 是POPUP页面
		$this->tpl->display('admin/archive/archive_album_stage_add.html');
	}
	
	// 新增阶段表单提交
	public function add_submit(){
		$info = array(
			'album_id'=>$this->gf('album_id'),
			'stage_name'=>$this->gf('stage_name')
		);
		$this->load->model('archive/archive_album_model');
		try {
			$stage_id = $this->archive_album_model->add_stage($info);
			echo('<script type="text/javascript">alert("添加成功");parent.location.reload();</script>');
		}catch(Exception $e){
			$this->alert('错误：' . $e->getMessage());
		}
	}
	
	// 图片上传页面
	public function image_add(){
		$id = $this->gr('id');
		$this->tpl->assign('stage_id', $id);
		$this->tpl->assign('popup_page', '1');
		$this->tpl->display('admin/archive/archive_album_image_upload.html');
	}
	
	// 图片上传表单提交处理
	public function image_add_submit(){
		
		$info = array(
			'stage_id'=>$this->gf('stage_id'),
			'image'=>$this->gf('face'),
			'image_name'=>$this->gf('image_name'),
			'description'=>$this->gf('description'),
			'fm'=>$this->gf('fm')	// 是否设为封面
		);
		
		$this->load->model('archive/archive_album_model');
		
		try{
			$id = $this->archive_album_model->image_add($info);
			echo('<script type="text/javascript">parent.location.reload();</script>');
		}catch(Exception $e){
			throw new Exception('图片上传错误：' . $e->getMessage());
		}
		
	}
	
	// 图片修改界面
	public function image_edit(){
		$id = $this->gr('id');		// image id
		$this->load->model('archive/archive_album_model');
		$image = $this->archive_album_model->archive_album_model->get_image($id);
		
		$this->tpl->assign('fm', $this->archive_album_model->check_fm($image['image']));	// 检测本图片是否为案例封面
		$this->tpl->assign('image', $image);
		$this->tpl->display('admin/archive/archive_album_image_edit.html');		// 图片编辑界面模版
	}
	
	// 图片修改提交
	public function image_edit_submit(){
		
		$info = array(
			'id'=>$this->gf('image_id'),
			'image'=>$this->gf('face'),
			'old_path'=>$this->gf('old_path'),
			'image_name'=>$this->gf('image_name'),
			'description'=>$this->gf('description'),
			'fm'=>$this->gf('fm')
		);
		
		if( empty($info['image']) ) $info['image'] = $info['old_path'];
		
		$this->load->model('archive/archive_album_model');
		try{
			$this->archive_album_model->image_edit($info);
			if( $info['image'] != $info['old_path'] ){
				$this->load->library('fileact');
				$cfg = $this->config->item('upload_image_options');
				$path = $cfg[0]['path'] . str_replace('/', '\\', $info['old_path']);
				$this->fileact->delete_file($path);
			}
			echo('<script type="text/javascript">parent.location.reload();</script>');
		}catch(Exception $e){
			throw new Exception($e->getMessage());
		}
	}
	
	// 阶段内图片排序
	public function image_sort(){
		
		$info = $_POST['data'];
		
		$this->load->model('archive/archive_album_model');
		
		try{
			$this->archive_album_model->image_sort($info);	// 图片排序
			echo('1');
		}catch(Exception $e){
			throw new Exception($e->getMessage());
		}
		
	}
	
	// 删除图片
	public function image_delete(){
		$id = $this->gr('id');
		$this->load->model('archive/archive_album_model');
		try{
			$this->archive_album_model->image_delete($id);
			echo('1');
		}catch(Exception $e){
			echo($e->getMessage());
		}
	}
	
	// 阶段删除
	public function stage_delete(){
		$id = $this->gr('id');
		$this->load->model('archive/archive_album_model');
		try{
			$this->archive_album_model->stage_delete($id);
			echo('1');
		}catch(Exception $e){
			echo($e->getMessage());
		}
	}
	
	
}























?>