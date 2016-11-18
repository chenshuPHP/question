<?php
class archive_tm extends archive_base {
	
	public $tid;
	public $team;
	
	public function __construct(){
		parent::__construct();
		$this->load->library('encode');
		$this->tpl->assign('hide_kf', '1');
	}
	
	private function set_tid($id){
		
		if( preg_match('/^\d+$/', $id) ){
			$this->tid = $id;
		} else {
			show_error('参数错误', 404, 'TID错误');
			exit;
		}
		
	}
	
	public function _common(){
		
		$this->load->library('thumb');
		
		$this->load->model('team/team_model');
		$this->load->model('team/team_member_model');
		$this->load->model('archive/archive_cas_model');
		
		$team = $this->team_model->get_team($this->tid, 'id, name, cdate, captain, logo, wx_code, qr_code_image, view_count');
		$team = $this->team_member_model->captain_assign($team);			// 得到施工队队长信息
		$team = $this->team_member_model->member_count_assign($team);		// 得到施工队人数统计
		
		// logo
		if( ! empty($team['logo']) ){
			$team['thumb'] = $this->thumb->resize($team['logo'], 209, 154);
		} else {
			if( isset($team['captain_info']) ){
				$team['thumb'] = $this->thumb->resize($team['captain_info']['face'], 209, 154);
			} else {
				$team['thumb'] = '';
			}
		}
		
		
		// 获取子成员
		//$team = $this->team_member_model->member_assign($team, array(
		//	'top'=>3
		//));
		
		//foreach($team['members'] as $key=>$value){
		//	$team['members'][$key]['thumb'] = $this->thumb->resize($value['face'], 78, 78);
		//}
				
		$this->team = $team;

		$this->team = $this->team_model->get_team_relations_url($team);

		$this->team = $this->archive_cas_model->count_assign($this->team);
		$this->team = $this->archive_cas_model->count_assign($this->team, 'ing', 'ing_cas_count');	// 正在施工中的工地统计
		
	}
	
	private function _assign(){
		$this->tpl->assign('team', $this->team);
	}
	
	// 团队首页2
	public function home(){
		$this->set_tid( $this->gr('id') );
		$this->_common();

		$team_description = $this->team_model->get_team($this->tid, 'id, name, description');
		
		$this->tpl->assign('team_description', $team_description['description']);
		
		$cas = $this->archive_cas_model->get_list("select top 6 id, tid, name, fm, community from archive_cas where tid = '". $this->tid ."' order by addtime desc");
		$this->tpl->assign('cas', $cas['list']);
		
		$sql = "select top 10 id, name, face from team_members where id in (select member_id from team_relations where team_id = '". $this->tid ."') order by id asc";
		$workers = $this->team_member_model->get_workers($sql);
		$workers = $workers['list'];
		$this->tpl->assign('workers', $workers);

		$cas_states = $this->archive_cas_model->get_list("select top 8 id, tid, name, community, huxing, area, budget, sdate, edate from archive_cas where tid = '". $this->tid ."' order by edate desc");
		$cas_states = $cas_states['list'];

		$this->tpl->assign('cas_states', $cas_states);
		
		$this->_assign();
		$this->tpl->display('archive/tm/home.html');
	}
	
	// 团队界面首页
	/*
	private function _home(){
		
		$this->set_tid( $this->gr('id') );
		$this->_common();
		
		// 图集
		$albums = $this->archive_cas_model->get_list("select top 4 id, tid, name, community, huxing, budget, area, fm from archive_cas where tid = '". $this->tid ."' and fm <> '' order by id desc");
		$albums = $albums['list'];
		
		foreach($albums as $key=>$value){
			$albums[$key]['thumb'] = $this->thumb->crop($value['fm'], 160 ,120);
		}
		
		// player album
		$this->load->model('archive/archive_album_model');
		$palbum = false;
		if( count($albums) > 0 ){
			$palbum = $albums[0];
			$palbum['stages'] = $this->archive_album_model->get_album_stages($palbum['id']);
			$palbum['stages'] = $this->archive_album_model->assign_images($palbum['stages']);
		}
		
		//var_dump($palbum);
		
		$cas = $this->archive_cas_model->get_list("select top 8 id, tid, name, community, huxing, area, budget, sdate, edate from archive_cas where tid = '". $this->tid ."' order by addtime desc");
		$cas = $cas['list'];
		
		$this->_assign();
		
		$this->tpl->assign('cas', $cas);
		$this->tpl->assign('palbum', $palbum);
		$this->tpl->assign('albums', $albums);
		$this->tpl->display('archive/tm/home.html');
		
	}
	*/
	
	public function member(){
		$this->set_tid( $this->gr('tid') );
		$this->_common();
		
		$this->load->model('team/team_config_model');
		// 获取子成员
		$members = $this->team_member_model->get_team_members($this->tid, '*');
		$members = $this->team_config_model->type_assign($members);
		$members = $this->team_config_model->seniority_assign($members);
		
		if( $members ){
			foreach($members as $key=>$value){
				$members[$key]['thumb'] = $this->thumb->resize($value['face'], 130, 130);
			}
		}
		
		$this->_assign();
		
		$this->tpl->assign('members', $members);
		$this->tpl->assign('module', 'members');
		
		$this->tpl->display('archive/tm/member.html');
	}
	
	public function cas(){
		
		$this->set_tid( $this->gr('tid') );
		$this->_common();
		
		$cas = $this->archive_cas_model->get_list("select id, tid, name, community, huxing, area, budget, sdate, edate from archive_cas where tid = '". $this->tid ."'");
		
		$this->_assign();
		
		$this->tpl->assign('cas', $cas['list']);
		$this->tpl->assign('module', 'cas');
		
		$this->tpl->display('archive/tm/cas.html');
		
	}
	
	public function cas_detail(){
		$id = $this->gr('id');
		if( preg_match('/^\d+$/', $id) == false ){
			show_error('参数错误', 404, 'ID错误');
			exit;
		}
		$this->load->model('archive/archive_cas_model');
		$this->load->model('archive/archive_album_model');
		$cas = $this->archive_cas_model->get_cas($id);
		if( ! $cas ){
			show_error('找不到案例', 404, '数据不存在');
			exit;
		}
		
		$cas = $this->archive_album_model->image_count_assign_albums($cas);
		
		$cas['detail'] = $this->encode->htmldecode($cas['detail']);
		
		$this->set_tid($cas['tid']);
		$this->_common();
		
		$this->tpl->assign('cas', $cas);
		
		//var_dump($cas);
		
		$this->_assign();
		$this->tpl->assign('module', 'cas');
		$this->tpl->display('archive/tm/cas_detail.html');
	}
	
	public function album(){
		
		$this->set_tid( $this->gr('tid') );
		$this->_common();
		
		$this->load->model('archive/archive_album_model');
		
		$cas = $this->archive_cas_model->get_list("select id, tid, name, community, huxing, fm from archive_cas where fm <> '' and tid = '". $this->tid ."'");
		$cas = $cas['list'];
		
		foreach($cas as $key=>$value){
			$cas[$key]['thumb'] = $this->thumb->crop($value['fm'], 215, 165);
		}
		$cas = $this->archive_album_model->image_count_assign_albums($cas);
		
		$this->_assign();
		
		$this->tpl->assign('cas', $cas);
		
		$this->tpl->assign('module', 'album');
		
		$this->tpl->display('archive/tm/album.html');
		
	}
	
	// 图集阶段展示页
	public function album_stage(){
		
		$cas_id = $this->gr('id');
		
		$this->load->model('archive/archive_cas_model');
		$this->load->model('archive/archive_album_model');
		
		$cas = $this->archive_cas_model->get_cas($cas_id, 'id, tid, name, sheng, city, town, address, community, huxing, area, budget, addtime, sdate, edate, baoxiu, fm');
		
		if( ! $cas ){
			show_error('找不到工地', 404, '参数：ID错误');
			exit;
		}
		
		$this->set_tid($cas['tid']);
		
		$this->_common();
		$this->_assign();
		
		$this->tpl->assign('module', 'album');
		
		$stages = $this->archive_album_model->get_album_stages($cas['id']);
		$stages = $this->archive_album_model->assign_images($stages);
		
		foreach($stages as $key=>$value){
			foreach($value['images'] as $k=>$v){
				$value['images'][$k]['thumb'] = $this->thumb->crop($v['image'], 160, 120);
			}
			$stages[$key] = $value;
		}
		
		
		
		$this->tpl->assign('cas', $cas);
		$this->tpl->assign('stages', $stages);
		$this->tpl->display('archive/tm/album_stage.html');
		
	}
	
	public function album_image(){
		
		$image_id = $this->gr('id');
		
		$this->load->model('archive/archive_cas_model');
		$this->load->model('archive/archive_album_model');
		
		
		$cas = $this->archive_cas_model->get_album_by_image($image_id, 'id, tid, name, sheng, city, town, address, community, huxing, area, budget, addtime, sdate, edate, baoxiu, fm');
		
		$this->set_tid($cas['tid']);
		
		$this->_common();
		$this->_assign();
		$this->tpl->assign('module', 'album');
		
		$stages = $this->archive_album_model->get_album_stages($cas['id']);
		$stages = $this->archive_album_model->assign_images($stages);
		
		$image = $this->archive_album_model->get_image($image_id);
		
		foreach($stages as $key=>$value){
			foreach($value['images'] as $k=>$v){
				$value['images'][$k]['thumb'] = $this->thumb->crop($v['image'], 75, 65);
			}
			$stages[$key] = $value;
		}
		
		$this->tpl->assign('image', $image);
		
		$this->tpl->assign('cas', $cas);
		$this->tpl->assign('stages', $stages);
		$this->tpl->display('archive/tm/album_image.html');
		
	}
	
	// 工地地图展示
	public function casmap(){
		$this->set_tid( $this->gr('tid') );
		$this->_common();
		
		$this->load->model('archive/archive_cas_model');
		// 获取工地信息
		
		$sql = "select id, tid, name, address, community, huxing, area, budget, fm, sdate, edate from [archive_cas] where tid = '". $this->tid ."' order by id desc";
		
		$cas = $this->archive_cas_model->get_list($sql);
		
		$this->tpl->assign('cas', $cas['list']);
		
		$this->_assign();
		$this->tpl->assign('module', 'casmap');
		$this->tpl->display('archive/tm/casmap.html');
	}
	
	
}





?>