<?php

// 设计师预约

class uc_bespeak extends uc_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'bespeak');
	}
	
	public function manage(){
		
		$page = $this->gr('page');
		if( ! preg_match('/^[1-9]\d*$/', $page) ) $page = 1;
		
		$cfg = array(
			'page' => $page,
			'size' => 20
		);
		
		$this->load->model('sjs/sjs_yuyue', 'sjs_yuyue_model');
		
		$sql = "select * from (select id, user_shen, user_city, area, category_b, category_s, addtime, row_number() over( order by addtime desc ) as num from sjs_yuyue where blog_user = '". $this->info['username'] ."' ) as tmp where num between ". ( ($cfg['page'] - 1) * $cfg['size'] + 1 ) ." and " . ( $cfg['page'] * $cfg['size'] );
		$sql_count = "select count(*) as icount from sjs_yuyue where blog_user = '". $this->info['username'] ."'";
		
		$result = $this->sjs_yuyue_model->get_list($sql, $sql_count);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->url_template_first = '';
		$this->pagination->url_template = '';
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $result['count']);
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		
		$this->tpl->assign('list', $result['list']);
		
		$this->tpl->display('member_design/ucenter/uc_bespeak_manage.html');
		
	}
	
	public function view(){
		$id = $this->gr('id');
		$this->load->model('sjs/sjs_yuyue', 'sjs_yuyue_model');
		$bp = $this->sjs_yuyue_model->get_yuyue($id, $this->info['username']);
		
		// 更改预约信息状态 等操作这里省略
		
		$this->tpl->assign('object', $bp);
		$this->tpl->display('member_design/ucenter/uc_bespeak_view.html');
	}
	
	
}

?>