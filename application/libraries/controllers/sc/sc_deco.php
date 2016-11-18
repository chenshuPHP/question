<?php

// 分站装修公司频道
class sc_deco extends sc_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('hide_kf', true);
		$this->tpl->assign('module', 'deco');
	}
	
	public function _remap($args = array()){
		if( count($args) == 0 ){
			$this->home();
		} else {
			echo('找不到处理程序');
			echo('<!--');
			var_dump($args);
			echo('-->');
		}
	}
	
	public function home(){
		
		
		$cfg = array(
			'size'=>100,
			'page'=>$this->encode->get_page()
		);
		
		$this->load->model('company/company', 'deco_model');
		
		$where_sql = "delcode = 0 and hangye = '装潢公司' and user_city = '". $this->sc['alias'] ."'";
		
		$sql = "select * from ( select id, username, company, shortname, logo, topflag, topflag_desc, address, tel, mobile, koubei, num = row_number() over( order by topflag desc ) from company where ". $where_sql ." ) as temp where num between ". (($cfg['page']-1) * $cfg['size'] + 1) ." and " . ( $cfg['page'] * $cfg['size'] );
		$sql_count = "select count(*) as icount from company where " . $where_sql;
		$result = $this->deco_model->get_list($sql, $sql_count, true);
		$decos = $result['list'];
		$count = $result['count'];
		unset($result);
		
		// 装修公司数据统计
		// 文章统计
		$this->load->model('company/usernews', 'deco_news_model');
		$decos = $this->deco_news_model->count_assign($decos);
		
		// 案例统计
		$this->load->model('company/usercase', 'deco_case_model');
		$decos = $this->deco_case_model->case_count_assign_users($decos);
		
		// 员工统计
		$this->load->model('company/userteam', 'deco_team_model');
		$decos = $this->deco_team_model->count_assign($decos);
		
		// 证书数量统计
		$this->load->model('company/zizhi', 'deco_cert_model');
		$decos = $this->deco_cert_model->count_assign($decos);
		
		//echo('<!--');
		//var_dump($decos);
		//echo('-->');
		
		// 子区域
		$this->load->model('city_model');
		$towns = $this->city_model->get_childs($this->sc['id']);
		
		$this->tpl->assign('towns', $towns);
		$this->tpl->assign('decos', $decos);
		$this->tpl->assign('title', $this->sc['alias'] . '装修公司');
		$this->tpl->display('sc/deco/home.html');
	}
	
}

?>