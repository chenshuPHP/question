<?php

// 企业促销
class member_promotion_manage extends member_promotion_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		
		$tpl = $this->get_tpl('promotion/manage.html');
		
		$this->load->model('company/promotion_model');
		
		$args = array(
			'page'		=> $this->encode->get_page(),
			'size'		=> 10
		);
		
		$where = "username = '". $this->base_user ."' and " . $this->promotion_model->where;
		$sql = "select * from ( select id, title, sdate, edate, addtime, username, ImgPath as path, description, ".
		"num = row_number() over( order by addtime desc ) ".
		"from [". $this->promotion_model->table_name ."] where ". $where ." ) as tmp where ".
		"num between ". ( ( $args['page'] - 1 ) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from [". $this->promotion_model->table_name ."] where " . $where;
		
		$res = $this->promotion_model->gets( $sql, $sql_count, array(
			'format'		=> TRUE
		) );
		
		$this->load->library('pagination');
		$this->pagination->pageSize = $args['size'];
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $res['count'];
		$this->pagination->url_template = $this->get_complete_url('/promotion/manage?page=<{page}>');
		$this->pagination->url_template_first = $this->get_complete_url('/promotion/manage');
		
		$this->tpl->assign('pagination', $this->pagination->toString( TRUE ));
		$this->tpl->assign('list', $res['list']);
		$this->tpl->assign('args', $args);
		$this->tpl->assign('page_module', 'manage');
		$this->tpl->display( $tpl );
		
	}
	
}

?>