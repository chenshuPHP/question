<?php

class admin_business_booking extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function manage(){
		
		$this->load->model('publish/reserve');
		$this->load->library('pagination');
		
		$cfg = array(
			'page'=>$this->gr('page'),
			'size'=>20
		);
		if( ! preg_match('/^[1-9]\d*$/', $cfg['page']) ) $cfg['page'] = 1;
		
		$sql = "select * from ( select id, fullname, mobile, tel, area, shenid, localid, budget, addtime, users, row_number() over(order by addtime desc) as num from zh_booking ) as temp where num between ". (($cfg['page']-1)*$cfg['size']+1) ." and " . $cfg['page'] * $cfg['size'];
		$sql_count = "select count(*) as icount from zh_booking";
		
		$result = $this->reserve->get_list($sql, $sql_count);
		
		$result['list'] = $this->reserve->deco_assign_reserve($result['list']);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->url_template_first = $this->manage_url . 'business/booking/manage';
		$this->pagination->url_template = $this->manage_url . 'business/booking/manage?page=<{page}>';
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $result['count']);
		$pagination = $this->pagination->toString(true);
		$this->tpl->assign('pagination', $pagination);
		
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->assign('module', 'list');
		$this->tpl->display('admin/business/booking_manage.html');
	}
	
	public function view(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		$this->load->model('publish/reserve');
		$rev = $this->reserve->get_rev($id);
		$rev = $this->reserve->deco_assign_reserve($rev);
		
		$this->tpl->assign('rev', $rev);
		$this->tpl->assign('module', 'view');
		$this->tpl->assign('r', $r);
		$this->tpl->display('admin/business/bki_view.html');
		
	}
	
	
}


?>