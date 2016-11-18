<?php

class admin_log_login extends admin_base {
	
	public function __cosntruct()
	{
		parent::__construct();
	}
	
	public function manage()
	{
		$tpl = $this->get_tpl('log/login_log.html');
		
		$this->load->model("admin/admin_login_log_model");
		
		$args = array(
			'page'		=> $this->encode->get_page(),
			'size'		=> 30
		);
		
		$sql = "select * from ( select id, username, ip, time, act, num = row_number() over( order by time desc ) from [admin_login_log] ) as tmp ".
		"where num between ". ( ( $args['page'] - 1 ) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from [admin_login_log]";
		
		$data = $this->admin_login_log_model->gets($sql, $sql_count);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $data['count'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->url_template = $this->get_complete_url( 'log/login/manage?page=<{page}>' );
		$this->pagination->url_template_first = $this->get_complete_url( 'log/login/manage' );
		$pagination = $this->pagination->toString(TRUE);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('data', $data['list']);
		$this->tpl->display( $tpl );
	}
	
}

?>