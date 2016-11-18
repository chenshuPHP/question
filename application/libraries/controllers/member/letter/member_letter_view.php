<?php

// 站内信 详情
class member_letter_view extends member_letter_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		$tpl = $this->get_tpl('letter/view.html');
		
		$letter_id = $this->gr('id');
		
		$where = $this->member_letter_model->get_where(array(
			'isVIP'			=> $this->isVIP(),
			'username'		=> $this->base_user
		));
		
		$where = "id = '". $letter_id ."' and (". $where .")";
		
		$sql = "select id, title, type, detail, admin, addtime from [". $this->member_letter_model->table_name ."] where " . $where;
		$letter = $this->member_letter_model->get($sql);
		$this->member_letter_model->open_state_assign( $letter, $this->base_user );
		
		if( ! $letter )
		{
			show_404();
		}
		
		$letter['detail'] = $this->encode->htmldecode( $letter['detail'] );
		$this->tpl->assign('letter', $letter);
		$this->tpl->display( $tpl );
	}
	
	
	
}

?>