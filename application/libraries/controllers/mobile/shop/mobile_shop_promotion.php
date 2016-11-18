<?php

// 优惠活动
class mobile_shop_promotion extends mobile_shop_base {

	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('module', 'promotion');
	}
	
	public function _remap($method, $args = array())
	{
		if( ! empty( $method ) )
		{
			$method = strtolower( $method );
		}
		else
		{
			show_404();
		}
		if( preg_match('/^detail\-\d+$/', $method) )
		{
			$this->detail( str_replace('detail-', '', $method) );
		}
		else
		{
			show_404();
		}
	}
	
	public function home()
	{
		$tpl = $this->get_tpl('shop/promotion/home.html');
		
		$this->load->model('company/promotion_model');
		
		$where = $this->promotion_model->where( array(
			"username = '". $this->username ."'"
		) );
		
		$sql = "select top 10 id, title, sdate, edate, username ".
		"from [". $this->promotion_model->table_name ."] where ". $where ." order by addtime desc";
		$promtion = $this->promotion_model->gets( $sql );
		$promtion = $promtion['list'];
		$this->mobile_url_model->assign_sp_promotion_url( $promtion );
		
		$this->tpl->assign('promotion', $promtion);
		$this->tpl->display( $tpl );
	}
	
	public function detail($id)
	{
		$tpl = $this->get_tpl('shop/promotion/detail.html');
		
		$this->load->model('company/promotion_model');
		
		$where = $this->promotion_model->where( array(
			"username = '". $this->username ."'",
			"id = '". $id ."'"
		) );
		
		$sql = "select id, title, sdate, edate, username, jianjie, imgpath, description ".
		"from [". $this->promotion_model->table_name ."] where ". $where;
		$promtion = $this->promotion_model->get( $sql );
		$promtion['jianjie'] = $this->encode->htmldecode( $promtion['jianjie'] );
		// $this->mobile_url_model->assign_sp_promotion_url( $promtion );
		
		
		// 获取 上下个活动
		$result = $this->promotion_model->getLastnext($promtion['id'],$promtion['username']);
		
		if($result['last'] == '')
		{
			$result['last']['title'] = '没有了';
			$result['last']['mlink'] = 'javascript:;';
		}else{
			$result['last']['username'] = $result['last']['UserName'];
			$this->mobile_url_model->assign_sp_promotion_url( $result['last'] );
		}
	
		if($result['next'] == '')
		{
			$result['next']['title'] = '没有了';
			$result['next']['mlink'] = 'javascript:;';
		}else{
			$result['next']['username'] = $result['next']['UserName'];
			$this->mobile_url_model->assign_sp_promotion_url( $result['next'] );
		}

		//var_dump2( $result );
		list($result['next'],$result['last']) = array($result['last'],$result['next']);
		 
		 
		$this->tpl->assign('lastnext', $result);
		$this->tpl->assign('promotion', $promtion);
		$this->tpl->display( $tpl );
	}
	
}



