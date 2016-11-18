<?php

// 施工团队首页
class archive_index extends archive_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('hide_kf', '1');
	}

	public function home(){
		
		// 施工队首页数据
		$teams = file_get_contents('http://archive.shzh.net/team/collect');
		
		$this->load->library('thumb');
		$this->load->model('archive/archive_cas_model');
		$this->load->model('team/team_model');

		// 工人类型
		$this->load->model('team/team_config_model');
		$this->load->model('team/team_member_model');

		$worker_types = $this->team_config_model->get_types();

		// 统计没种类型下的工人数量
		$this->team_member_model->member_count_assign_types($worker_types);

		//echo('<!--');
		//var_dump($worker_types);
		//echo('-->');

		
		$cas = $this->archive_cas_model->get_list("select top 8 id, tid, name, community, huxing, area, budget, sdate from archive_cas where tid <> 0 order by addtime desc");
		$cas = $cas['list'];
		foreach($cas as $key=>$value){
			if( strpos($value['area'], '平') == false ){
				$value['area'] .= '平米';
			}
			$cas[$key] = $value;
		}
		
		$albums = $this->archive_cas_model->get_list("select top 4 id, tid, address, community, budget, fm from archive_cas where fm <> '' and tid <> 0 order by addtime desc");
		$albums = $albums['list'];
		
		foreach($albums as $key=>$value){
			$albums[$key]['thumb'] = $this->thumb->crop($value['fm'], 100, 70);
		}
		
		$albums = $this->team_model->team_assign(
			$albums, 
			array(
				'fields'=>'id, name',
				'item_key'=>'tid'
			)
		);

		$this->tpl->assign('worker_types', $worker_types);

		$this->tpl->assign('cas', $cas);
		$this->tpl->assign('teams', $teams);
		$this->tpl->assign('albums', $albums);
		$this->tpl->display('archive/home.html');
	}
	
	// 工地参观
	public function cas_visit(){
		$id = $this->gr('id');
		echo($id);
	}
	
	
}

?>