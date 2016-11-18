<?php

// 联系我们管理
class member_leave_manage extends member_leave_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		$tpl = $this->get_tpl( 'leave/manage.html' );
		
		// 获取留言总数
		$this->load->model('company/guest', 'company_leave_model');
		
		$args = array(
			'page'		=> $this->encode->get_page(),
			'size'		=> 20
		);
		
		$where = $this->company_leave_model->where( array(
			'username'		=> $this->base_user
		) );
		
		$sql = "select * from ( select id, addtime, ip, shopid, truename, mobile, email, content, ".
		"num = row_number() over( order by addtime desc ) from [". $this->company_leave_model->table_name ."] where ". $where ." ) ".
		"as tmp where num between ". ( ( $args['page'] - 1 ) * $args['size'] + 1 ) ." and " . ( $args['size'] * $args['page'] );
		$sql_count = "select count(*) as icount from [". $this->company_leave_model->table_name ."] where " . $where;
		$result = $this->company_leave_model->get_list($sql, $sql_count);
		$this->company_leave_model->reply_assign( $result['list'] );
		
		// 留言统计
		$leave_total = $result['count'];
		
		// 未回复统计
		$where .= " and id not in ( select pid from [". $this->company_leave_model->table_name ."] where shopid = '". $this->base_user ."' and pid <> 0 )";
		$un_reply_total = $this->company_leave_model->total( $where );
		
		$this->tpl->assign('total', $result['count']);
		$this->tpl->assign('un_reply_total', $un_reply_total);
		$this->tpl->assign('list', $result['list']);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->url_template = $this->get_complete_url('/leave/manage?page=<{page}>');
		$this->pagination->url_template_first = $this->get_complete_url('/leave/manage');
		$this->tpl->assign('pagination', $this->pagination->toString( TRUE ));
		
		var_dump2( $result );
		
		$this->tpl->display( $tpl );
	}
	
}

?>