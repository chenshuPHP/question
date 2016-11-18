<?php

class member_letter_manage extends member_letter_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function index()
	{
		$tpl = $this->get_tpl( 'letter/manage.html' );
		
		$args = array(
			'page'		=> $this->encode->get_page(),
			'size'		=> 50
		);
		
		$so = array(
			'open'			=> $this->gr('open'),
			'cid'			=> $this->gr('cid')
		);
		
		$where_option = array(
			'isVIP'				=> $this->isVIP(),
			'username'			=> $this->base_user
		);
		
		if( $so['open'] === '0' )
		{
			$where_option['open'] = 0;
		}
		
		if( $so['cid'] != '' )
		{
			$where_option['cid'] = $so['cid'];
		}
		
		$where = $this->member_letter_model->get_where( $where_option );
		
		$sql = "select * from ( select id, title, type, addtime, num = row_number() over( order by addtime desc ) ".
		"from [". $this->member_letter_model->table_name ."] where ". $where ." ) as tmp ".
		"where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from [". $this->member_letter_model->table_name ."] where " . $where;
		
		$res = $this->member_letter_model->gets($sql, $sql_count);
		
		$this->member_letter_model->open_state_assign( $res['list'], $this->base_user );
		
		$this->tpl->assign('types', $this->member_letter_model->types());
		$this->tpl->assign('list', $res['list']);
		$this->tpl->assign('so', $so);
		$this->tpl->display( $tpl );
	}
	
	public function load()
	{
		
		$info = array(
			'isVIP'		=> $this->isVIP(),
			'num'		=> 3,					// 默认3条
			'open'		=> ''					// 未读状态
		);
		
		if( $this->gr('num') != '' ) $info['num'] = $this->gr('num');
		if( $this->gr('open') != '' ) $info['open'] = $this->gr('open');
		
		
		$where = $this->member_letter_model->get_where( array(
			'isVIP'		=> $info['isVIP'],
			'open'		=> $info['open'],
			'username'	=> $this->base_user
		) );
		
		$sql = "select top 3 id, title, type, addtime from [". $this->member_letter_model->table_name ."] where " . $where;
		$res = $this->member_letter_model->gets($sql);
		
		json_echo( $res['list'] );
		
	}
	
	
	
}


?>