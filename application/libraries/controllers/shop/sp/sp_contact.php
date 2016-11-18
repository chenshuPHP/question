<?php

// 联系我们 by 袁仙增
class sp_contact extends sp_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home( $args = array() )
	{
		
		$tpl = $this->get_tpl('contact.html');
		if( $this->gr('debug') == 1 )
		{
			$tpl = $this->get_tpl('contact2.html');
		}	
		
		$logo = $this->infomation['logo'];
		$thumb = '';
		if( !empty($logo) ){
			$this->load->library('thumb');
			$this->thumb->setPathType(1);
			$thumb = $this->thumb->resize($logo, 60, 60);
		}
		$this->tpl->assign('guest_reply_thumb', $thumb);
		$this->load->model('company/comchild', 'comchild_model');
		$list = $this->comchild_model->getCom_child($this->user);
		
		if( $list )
		{
			foreach( $list as $k=>$v )
			{
				$list[$k] = array_change_key_case($list[$k]);
				$list[$k]['update_time'] = $this->infomation['update_time'];
				$this->deco_model->conver($list[$k], array('day'=>3));
			}
		}
		
		$this->tpl->assign('contact_list', $list);
		
		$this->tpl->assign('module', 'contact');
		$this->tpl->display( $tpl );
	}

}
?>