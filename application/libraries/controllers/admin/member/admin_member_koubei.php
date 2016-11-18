<?php

// 会员口碑值管理
class admin_member_koubei extends admin_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function sync()
	{
		$this->load->library('mdb');
		$this->load->model('company/company_koubei_model');
		$_tmp = $this->mdb->query("select DISTINCT username from user_koubei");
		$_arr = array();
		foreach($_tmp as $item)
		{
			$this->company_koubei_model->update_user_koubei( $item['username'] );
		}
		exit('done');
	}
	
	public function manage()
	{
		
		$tpl = $this->get_tpl('member/koubei/manage.html');
		
		$args = array(
			'page'			=> $this->encode->get_page(),
			'size'			=> 20
		);
		
		$so = array(
			'key'		=> $this->gr('key'),
			'type'		=> $this->gr('type')
		);
		
		$params = array();
		$where = array();
		if( $so['key'] != '' )
		{
			$so['key'] = $this->encode->gbk_to_utf8($so['key']);
			$params[] = "key=" . $so['key'];
			$where[] = "( username in ( select username from company where company like '%". $so['key'] ."%' ) ) OR ( username = '". $so['key'] ."' )";
		}
		if( $so['type'] != '' )
		{
			$params[] = "type=" . $so['type'];
			$where[] = "type = '". $so['type'] ."'";
		}
		
		$this->load->model('company/company_koubei_model');
		$this->load->model('company/company', 'deco_model');
		
		$where[] = "username in ( select username from company where delcode = 0 )";
		$where = implode(' and ', $where);
		
		$sql = "select * from ( select id, username, val, type, description, addtime, admin, recycle, num = row_number() over( order by addtime desc ) ".
		"from [". $this->company_koubei_model->table_name ."] where ". $where ." ) as tmp ".
		"where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from [". $this->company_koubei_model->table_name ."] where " . $where;
		
		$result = $this->company_koubei_model->gets($sql, $sql_count);
		
		$this->deco_model->assign($result['list'], array(
			'format'		=> TRUE
		));
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->recordCount = $result['count'];
		if( count( $params ) == 0 )
		{
			$this->pagination->url_template = $this->get_complete_url('member/koubei/manage?page=<{page}>');
			$this->pagination->url_template_first = $this->get_complete_url('member/koubei/manage');
		}
		else
		{
			$this->pagination->url_template = $this->get_complete_url('member/koubei/manage?page=<{page}>&' . implode('&', $params));
			$this->pagination->url_template_first = $this->get_complete_url('member/koubei/manage?' . implode('&', $params));
		}
		
		$this->tpl->assign('pagination', $this->pagination->toString( TRUE ));
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('so', $so);
		$this->tpl->assign('args', $args);
		$this->tpl->assign('types', $this->company_koubei_model->types);
		$this->tpl->display( $tpl );
		
	}
	
	
}

?>