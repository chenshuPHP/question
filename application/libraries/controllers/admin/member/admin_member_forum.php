<?php

// 装修公司店铺底部匿名留言管理
class admin_member_forum extends admin_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function manage()
	{
		
		$tpl = $this->get_tpl( 'member/forum/manage.html' );
		
		$this->load->model('company/company_forum_model');
		$this->load->model('company/company', 'company_model');
		
		$args = array(
			'page'		=> $this->encode->get_page(),
			'size'		=> 20
		);
		
		$so = array(
			'recycle'		=> $this->gr('recycle'),
			'key'			=> $this->gr('key')
		);
		
		$params = array();
		$where = array();
		if( $so['key'] != '' )
		{
			$so['key'] = $this->encode->gbk_to_utf8($so['key']);
			$params[] = 'key=' . $so['key'];
			$where[] = "(sp_username in ( select username from company where company like '%". $so['key'] ."%' ) OR comment like '%". $so['key'] ."%')";
		}
		if( $so['recycle'] == 1 )
		{
			$params[] = 'recycle=1';
			$where[] = "recycle = 1";
		}
		else
		{
			$where[] = "recycle = 0";
		}
		
		$where[] = $this->company_forum_model->build_where();
		$where = implode(' and ', $where);
		
		$sql = "select * from ( select id, sp_username, comment, addtime, ip, num = row_number() over( order by id desc ) ".
		"from [". $this->company_forum_model->table_name ."] where ". $where ." ) as tmp ".
		"where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		
		$sql_count = "select count(*) as icount from [". $this->company_forum_model->table_name ."] where " . $where;
		
		$result = $this->company_forum_model->gets($sql, $sql_count);
		
		$this->company_model->assign($result['list'], array(
			'key_name'		=> 'sp_username',
			'format'		=> TRUE
		));
		
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->recordCount = $result['count'];
		
		if( count( $params ) != 0 )
		{
			$this->pagination->url_template = $this->get_complete_url('member/forum/manage?page=<{page}>&' . implode('&', $params));
			$this->pagination->url_template_first = $this->get_complete_url('member/forum/manage?' . implode('&', $params));
		}
		else
		{
			$this->pagination->url_template = $this->get_complete_url('member/forum/manage?page=<{page}>');
			$this->pagination->url_template_first = $this->get_complete_url('member/forum/manage');
		}
		
		
		var_dump2($args);
		var_dump2($so);
		var_dump2($result['list']);
		
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('pagination', $this->pagination->toString( TRUE ));
		$this->tpl->assign('args', $args);
		$this->tpl->assign('so', $so);
		$this->tpl->assign('module', 'forum.manage');
		
		if( $so['recycle'] == 1 )
			$this->tpl->assign('module', 'forum.recycle');
			
		$this->tpl->display( $tpl );
		
	}
	
	public function recycle()
	{
		
		$info = array(
			'id'			=> $this->gf('id'),
			'type'			=> $this->gf('type'),
		);
		
		$this->load->model('company/company_forum_model');
		$error = '';
		
		try
		{
			$this->company_forum_model->recycle($info['id'], array(
				'type'		=> $info['type']
			));		// 删除到回收站 & 还原
		}
		catch(Exception $e)
		{
			$error = $e->getMessage();
		}
		
		if( $error == '' )
		{
			json_echo( array(
				'type'		=> 'success'
			) );
		}
		else
		{
			json_echo( array(
				'type'		=> 'error',
				'error'		=> $error
			) );
		}
		
		
	}
	
	
}


?>