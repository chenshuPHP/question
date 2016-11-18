<?php

// 建材商城产品管理控制器
// 2014-10-21
// kko4455@163.com

class admin_mall_product extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 产品列表页面管理
	public function manage(){
		
		$this->load->model('mall/mall_product_model');
		$this->load->model('mall/mall_cmp_model');
		$this->load->library('pagination');
		
		
		$filter = array();
		$filter['key'] = $this->gr('key');
		$filter['flag'] = $this->gr('flag');
		$filter['recommend'] = $this->gr('recommend');
		
		// 搜索关键词
		if( !empty($filter['key']) ){
			$filter['key'] = iconv("gbk", "utf-8", $filter['key']);
		}
		
		if( $filter['flag'] == '' ) $filter['flag'] = 1;
		
		$page = $this->encode->get_page();
		
		
		$cfg = array(
			'size'=>15,
			'page'=>$page,
			'sort'=>$this->gr('s')
		);
		
		$sql_order = "order by puttime desc";
		
		if( $cfg['sort'] == '' ) $cfg['sort'] = 'puttime';
		
		if( $cfg['sort'] == 'puttime' )
			$sql_order = "order by puttime desc";
		
		if( $cfg['sort'] == 'rank' )
			$sql_order = "order by topshow desc";
		
		$sql_where = " where flag = '". $filter['flag'] ."'";
		
		if( ! empty( $filter['key'] ) ){
			$sql_where .= " and ( title like '%". $filter['key'] ."%' or senduser in (select username from company where company like '%". $filter['key'] ."%') )";
		}
		if( $filter['recommend'] == 1 ){
			$sql_where .= " and recommend = 1";
		}
		
		$sql = "select * from ( select id, title, puttime, senduser, smallclass, prdclass, flag, topshow, recommend, row_number() over( ". $sql_order ." ) as num from sendbuy". $sql_where ." ) as temp where num between ". ( ($cfg['page']-1) * $cfg['size'] + 1 ) ." and " . ( $cfg['page'] * $cfg['size'] );
		
		$sql_count = "select count(*) as icount from sendbuy" . $sql_where;
		
		$result = $this->mall_product_model->get_list($sql, $sql_count);
		$result['list'] = $this->mall_cmp_model->company_assign($result['list']);
		
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->url_template_first = $this->manage_url . 'mall/product/manage?flag='. $filter['flag'] .'&key=' . $filter['key'] . '&s=' . $cfg['sort'] . '&recommend=' . $filter['recommend'];
		$this->pagination->url_template = $this->manage_url . 'mall/product/manage?page=<{page}>&flag='. $filter['flag'] .'&key=' . $filter['key'] . '&s=' . $cfg['sort'] . '&recommend=' . $filter['recommend'];
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $result['count']);
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->assign('filter', $filter);
		$this->tpl->assign('module', 'prd.manage');
		$this->tpl->display('admin/mall/prd_manage.html');
	}
	
	// 产品后台设置
	public function settings(){
		
		$id = $this->gr('id');
		
		$this->load->model('mall/mall_product_model');
		$this->load->model('mall/mall_cmp_model');
		
		$prd = $this->mall_product_model->get_product($id, 'id, title, senduser, topshow, recommend');
		$cmp = $this->mall_cmp_model->get_company2($prd['senduser']);
		
		$this->tpl->assign('module', 'prd.settings');
		$this->tpl->assign('prd', $prd);
		$this->tpl->assign('cmp', $cmp);
		
		// echo( $_REQUEST['r'] );
		
		$this->tpl->assign('rurl', $_REQUEST['r']);
		$this->tpl->display('admin/mall/prd_settings.html');
		
	}
	
	// 产品设置
	public function settings_handler(){
		
		$r = str_replace(';', '', $_POST['r']);
		//echo($r);
		//exit();
		
		$settings = array(
			'id'=>$this->gf('id'),
			'rank'=>$this->gf('rank'),
			'rmd'=>$this->gf('rmd')
		);
		
		if( ! preg_match('/^\d*$/', $settings['rank']) ){
			$this->alert('权重值必须是数字');
			exit();
		}
		
		$this->load->model('mall/mall_product_model');
		
		try{
			$this->mall_product_model->settings($settings);
			$this->alert('提交成功', $r);
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
		
		
	}
	
	
	// 批量产品相关操作
	public function active(){
		$ids = $this->gf('ids');
		$active = $this->gf('act');
		$this->load->model('mall/mall_product_model');
		try{
			switch($active){
				case 0:		// 不通过审核
					$this->mall_product_model->verify_close($ids);
					break;
				case 1:		// 通过审核
					$this->mall_product_model->verify_open($ids);
					break;
				case 10:	// 删除到回收站
					$this->mall_product_model->recycle($ids);
					break;
				case 11:	// 从回收站还原->未审核
					//$this->mall_product_model->recovery($ids);
					$this->mall_product_model->verify_close($ids);
					break;
				case 12:	// 从回收站还原->已经发布
					//$this->mall_product_model->recovery($ids);
					$this->mall_product_model->verify_open($ids);
					break;
				default:
			}
		}catch(Exception $e){
			echo($e->getMessage());
		}
	}
}

?>