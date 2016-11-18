<?php

class archive_team extends MY_Controller {

	public function __construct(){
		parent::__construct();
	}
	
	
	// 首页内嵌施工队列表
	public function collect(){
		
		$args = array(
			'size'=>18,
			'page'=>$this->encode->get_page()
		);
		$this->load->library('thumb');		
		$this->load->model('team/team_model');
		$this->load->model('archive/archive_cas_model');


		
		$sql = "select * from ( select id, name, captain, logo, row_number() over(order by addtime desc) as num from team_teams ) as tmp where num between ". (($args['page'] - 1) * $args['size'] + 1) ." and " . $args['size'] * $args['page'];
		$sql_count = "select count(*) as icount from team_teams";
		$error = $this->team_model->get_teams($sql, $sql_count);
		
		$list = $error['list'];
		$count = $error['count'];
		unset($error);
		
		$urls = $this->config->item('url');
		
		$list = $this->team_model->bind_captain($list, 'id, name, face');
		$list = $this->archive_cas_model->count_assign($list);
		$list = $this->archive_cas_model->count_assign($list, 'ing', 'ing_cas_count');	// 正在施工中的工地统计
		
		foreach($list as $key=>$value){
			if( ! empty($value['logo']) ){
				$value['thumb'] = $value['logo'];
			} else {
				if( isset($value['captain']) && ! empty($value['captain']['face']) ){
					$value['thumb'] = $value['captain']['face'];
				}
			}
			if( ! empty($value['thumb']) ){
				$value['thumb'] = $this->thumb->resize($value['thumb'], 143, 140);
			} else {
				$value['thumb'] = '';
			}
			$list[$key] = $value;
		}
		
		$this->load->library('pagination');
		$this->pagination->pageSize = $args['size'];
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $count;
		$this->pagination->url_template_first = $urls['archive'] . '/team/collect';
		$this->pagination->url_template = $urls['archive'] . '/team/collect?page=<{page}>';
		$pagination = $this->pagination->toString(true);
		
		$tpl = 'archive/team_collect.html';
		$this->tpl->assign('list', $list);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->display($tpl);
		
		
	}
	
	// 工地参观
	public function cas_visit(){
		$id = $this->gr('id');
		$this->load->model('archive/archive_cas_model');
		$cas = $this->archive_cas_model->get_cas($id, 'id, tid, name, community, huxing, budget, area, fm');
		$this->tpl->assign('cas', $cas);
		$this->tpl->display('archive/cas_visit.html');
	}
	
	// 工地参观提交
	public function cas_visit_handler(){
		$visit = array(
			'name'=>$this->gf('name'),
			'mobile'=>$this->gf('mobile'),
			'address'=>$this->gf('address'),
			'tid'=>$this->gf('tid')
		);
		$error = array();
		if( $visit['name'] == '' || $visit['mobile'] == '' ){
			$error[] = '姓名和电话不能为空';
		}
		if( count($error) == 0 ){
			$this->load->model('archive/archive_visit_model');
			try{
				$this->archive_visit_model->add($visit);
				echo( json_encode($error) );
			}catch(Exception $e){
				$error[] = $e->getMessage();
				echo( json_encode($error) );
			}
		} else {
			echo( json_encode($error) );
		}
	}
	
	

}

?>