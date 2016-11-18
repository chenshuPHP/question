<?php

// 文章管理 列表
class member_article_manage extends member_article_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		
		$tpl = $this->get_tpl('article/manage.html');
		
		$this->load->model('company/usernews', 'company_article_model');
	
		$args = array(
			'page'			=> $this->encode->get_page(),
			'size'			=> 20
		);
		
		$where = "username = '". $this->base_user ."' and recycle = 0";
		$sql = "select * from ( select id, title, username, addtime, base_showcount, showcount, num = row_number() over( order by addtime desc ) ".
		"from [". $this->company_article_model->table_name ."] where ". $where ." ) as tmp ".
		"where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from [". $this->company_article_model->table_name ."] where " . $where;
		$result = $this->company_article_model->gets($sql, $sql_count);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->url_template = $this->get_complete_url('/article/manage?page=<{page}>');
		$this->pagination->url_template_first = $this->get_complete_url('/article/manage');
		
		var_dump2( $result );
		
		
		$this->tpl->assign('count', $result['count']);
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('args', $args);
		$this->tpl->assign('pagination', $this->pagination->toString( TRUE ));
		
		
		$this->tpl->display( $tpl );
	}
	
}

?>