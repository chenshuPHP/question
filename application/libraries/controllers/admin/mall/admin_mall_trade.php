<?php

// 订单管理控制器
class admin_mall_trade extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 订单列表
	public function order_manage(){
		
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>20
		);
		
		$this->load->model('mall/mall_order_model');
		
		$sql = "select * from ( select id, pid, count, name, mobile, sheng, city, town, address, snapshot, addtime, ip, row_number() over(order by addtime desc) as num from mall_order ) as temp where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from mall_order";
		
		$result = $this->mall_order_model->get_list($sql, $sql_count);
		
		$this->load->library('pagination');
		$this->pagination->recordCount = $result['count'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->currentPage = $args['page'];
		$this->pagination->url_template = $this->manage_url . 'mall/trade/order_manage?page=<{page}>';
		$this->pagination->url_template_first = $this->manage_url . 'mall/trade/order_manage';
		
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('module', 'order.manage');
		$this->tpl->display('admin/mall/trade_order_manage.html');
		
	}
	
	public function order_detail(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		$this->load->model('mall/mall_order_model');
		$order = $this->mall_order_model->get_order($id);
		
		$this->tpl->assign('order', $order);
		$this->tpl->assign('module', 'order.detail');
		$this->tpl->assign('rurl', $r);
		$this->tpl->display('admin/mall/trade_order_detail.html');
		
	}
	
	
}

?>