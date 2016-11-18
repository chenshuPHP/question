<?php

// 我的口碑值
class member_koubei_mykoubei extends member_koubei_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		$tpl = $this->get_tpl('koubei/mykoubei.html');
		
		$this->load->model('company/company_koubei_model');
		
		$args = array(
			'page'			=> $this->encode->get_page(),
			'size'			=> 20
		);
		
		$so = array(
			'key'		=> $this->gr('key'),
			'type'		=> $this->gr('type')
		);
		
		$where = array("username = '". $this->base_user ."'");
		$params = array();
		if( $so['key'] != '' )
		{
			$so['key'] = $this->encode->gbk_to_utf8( $so['key'] );
			$params[] = "key=" . $so['key'];
			$where[] = "description like '%". $so['key'] ."%'";
		}
		if( $so['type'] != '' )
		{
			$params[] = "type=" . $so['type'];
			$where[] = "type = '". $so['type'] ."'";
		}
		
		$where = implode(' and ', $where);
		$sql = "select * from ( select id, username, val, type, description, addtime, recycle, num = row_number() over( order by id desc ) from [". $this->company_koubei_model->table_name ."] where ". $where ." ) as tmp where num between ". ( ( $args['page'] - 1 ) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from [". $this->company_koubei_model->table_name ."] where " . $where;
		
		$result = $this->company_koubei_model->gets( $sql, $sql_count, array(
			'format'		=> TRUE
		) );
		
		$deco = $this->deco_model->getCompany($this->base_user, array(
			'fields'		=> 'username, koubei, koubei_total'
		));
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->recordCount = $result['count'];
		if( count( $params ) == 0 )
		{
			$this->pagination->url_template = $this->get_complete_url('/koubei/mykoubei?page=<{page}>');
			$this->pagination->url_template_first = $this->get_complete_url('/koubei/mykoubei');
		}
		else
		{
			$this->pagination->url_template = $this->get_complete_url('/koubei/mykoubei?page=<{page}>' . implode('&', $params));
			$this->pagination->url_template_first = $this->get_complete_url('/koubei/mykoubei?' . implode('&', $params));
		}
		
		$this->tpl->assign('module', 'koubei.mykoubei');
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('count', $result['count']);
		$this->tpl->assign('types', $this->company_koubei_model->types);
		$this->tpl->assign('type', $this->company_koubei_model->get_type( $so['type'] ));
		$this->tpl->assign('so', $so);
		$this->tpl->assign('koubei', $deco['koubei']);
		$this->tpl->assign('pagination', $this->pagination->toString(TRUE));
		$this->tpl->display( $tpl );
		
	}
	
}

?>