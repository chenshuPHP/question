<?php

// 监理入职
class admin_multi_jianliruzhi extends admin_base {
	
	public function __construct(){
		parent::__construct();
		$this->load->model('multi/sv_ruzhi_model');
		$this->tpl->assign('module', 'jianliruzhi');
	}
	
	public function manage(){
		
		$this->load->library('pagination');
		
		$args = array(
			'size'=>20,
			'page'=>$this->encode->get_page()
		);
		
		$sql = "select * from ( select id, sheng, city, town, name, gl, tel, addtime, num = row_number() over( order by addtime desc ) from send_sv_ruzhi ) as temp where num between ". (($args['page'] - 1) * $args['size'] + 1) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from send_sv_ruzhi";
		
		$list = $this->sv_ruzhi_model->get_list($sql, $sql_count);
		//if( count($list) == 0 ) $list = false;
		
		$list['list'] = $this->sv_ruzhi_model->gl_assign($list['list']);
		//var_dump($list);
		
		
		
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $list['count'];
		$this->pagination->pageSize = $args['size'];
		
		$this->pagination->url_template = $this->get_complete_url('/multi/jianliruzhi/manage?page=<{page}>');
		$this->pagination->url_template_first = $this->get_complete_url('/multi/jianliruzhi/manage');
		
		$this->tpl->assign('pagination', $this->pagination->toString(true));
		$this->tpl->assign('list', $list['list']);
		$this->tpl->assign('module', 'ruzhi.manage');
		$this->tpl->assign('args', $args);
		$this->tpl->display( $this->get_tpl('multi/jianliruzhi/manage.html') );
	}
	
	public function view(){
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		$info = $this->sv_ruzhi_model->get($id);
		
		$this->tpl->assign('gl', $this->sv_ruzhi_model->gl);
		$this->tpl->assign('info', $info);
		$this->tpl->assign('rurl', $r);
		$this->tpl->assign('module', 'ruzhi.view');
		$this->tpl->display( $this->get_tpl('multi/jianliruzhi/view.html') );
	}
	
	
}

?>