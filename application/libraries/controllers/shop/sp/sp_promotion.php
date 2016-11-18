<?php

// 店铺活动促销 控制器
// 2016-09-18
// hdw
class sp_promotion extends sp_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('module', 'promotion');
	}
	
	// 店铺活动促销列表
	public function home()
	{
		$tpl = $this->get_tpl('promotion/home.html');
		
		$this->load->model('company/promotion_model');
		
		
		// 只输出最新的 20 个 活动
		$where = array($this->promotion_model->where);
		$where[] = "username = '". $this->user ."'";
		$sql = "select top 20 id, username, title, sdate, edate ".
		"from [". $this->promotion_model->table_name ."] ".
		"where ". implode(' and ', $where) ." order by addtime desc";
		
		$promotions = $this->promotion_model->gets($sql);
		$promotions = $promotions['list'];
		
		$this->tpl->assign('promotions', $promotions);
		$this->tpl->display( $tpl );
	}
	
	// 促销活动 详情
	public function view( $params = array() )
	{
		$tpl = $this->get_tpl('promotion/view.html');
		
		if( ! isset( $params[0] ) || ! is_numeric( $params[0] ) ) show_404();
		
		$id = $params[0];
		
		$this->load->model('company/promotion_model');
		$where = array( $this->promotion_model->where );
		$where[] = "id = '". $id ."' and username = '". $this->user ."'";
		$sql = "select id, title, sdate, edate, jianjie, description, imgpath as path ".
		"from [". $this->promotion_model->table_name ."] ".
		"where " . implode(' and ', $where);
		$promotion = $this->promotion_model->get($sql);
		
		if( ! $promotion ) show_404();
		
		$promotion['jianjie'] = $this->encode->htmldecode( $promotion['jianjie'], TRUE );
		
		
		$this->tpl->assign('promotion', $promotion);
		$this->tpl->assign('title', $promotion['title'] . '-' . $this->infomation['company']);
		$this->tpl->assign('description', $promotion['description']);
		$this->tpl->display( $tpl );
	}
	
}

?>