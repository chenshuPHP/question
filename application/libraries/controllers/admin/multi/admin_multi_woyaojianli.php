<?php

// 入会申请管理
class admin_multi_woyaojianli extends admin_base {
	
	public function __construct(){
		parent::__construct();
		$this->load->model('multi/supervision_model', 'sv_model');
		$this->tpl->assign('module', 'woyaojianli');
	}
	
	public function manage(){
		
		$this->load->library('pagination');
		
		$args = array(
			'size'=>20,
			'page'=>$this->encode->get_page()
		);
		
		$sql = "select * from ( select id, rejion, tel, area, type, category_b, category_s, addtime, num = row_number() over( order by addtime desc ) from send_jianli ) as temp where num between ". (($args['page'] - 1) * $args['size'] + 1) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from send_jianli";
		
		$list = $this->sv_model->get_list($sql, $sql_count);
		
		// var_dump($list);
		
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $list['count'];
		$this->pagination->pageSize = $args['size'];
		
		$this->pagination->url_template = $this->get_complete_url('/multi/woyaojianli/manage?page=<{page}>');
		$this->pagination->url_template_first = $this->get_complete_url('/multi/woyaojianli/manage');
		
		$this->tpl->assign('pagination', $this->pagination->toString(true));
		$this->tpl->assign('list', $list['list']);
		$this->tpl->assign('module', 'jianli.manage');
		$this->tpl->assign('args', $args);
		$this->tpl->display( $this->get_tpl('multi/woyaojianli/manage.html') );
	}
	
	public function view(){
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		$info = $this->sv_model->get($id);
		
		$this->tpl->assign('types', $this->sv_model->get_types());
		$this->tpl->assign('info', $info);
		$this->tpl->assign('rurl', $r);
		$this->tpl->assign('module', 'jianli.view');
		$this->tpl->display( $this->get_tpl('multi/woyaojianli/view.html') );
	}
	
	
}

?>