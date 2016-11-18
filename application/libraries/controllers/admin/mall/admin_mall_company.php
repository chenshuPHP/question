<?php

// 建材商城商家管理
class admin_mall_company extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function manage(){
		
		$this->load->model('mall/mall_cmp_model');
		
		$cfg = array(
			'size'=>15,
			'page'=>$this->encode->get_page(),
			'sort'=>$this->gr('s')
		);
		
		// 搜索
		$filter = array(
			'key'=>$this->gr('key'),
			'flag'=>$this->gr('flag')
		);
		
		if( ! empty( $filter['key'] ) ){
			$filter['key'] = iconv("gbk", "utf-8", $filter['key']);
		}
		
		if( empty($cfg['sort']) ) $cfg['sort'] = 'puttime';
		
		$sql_order = "order by puttime desc";
		
		if( $cfg['sort'] == 'rank' ) $sql_order = "order by koubei desc";
		
		$sql_where = " where 1=1";
		$sql_where .= " and register = 2 and hangye = '建材公司'";
		
		if( ! empty($filter['key']) ){
			$sql_where .= " and company like '%". $filter['key'] ."%'";
		}
		
		if( ! empty($filter['flag']) == '1' ){
			$sql_where .= " and flag = 2";
		}
		
		
		$sql = "select * from ( select id, username, company, rejion, puttime, flag, koubei, row_number() over(". $sql_order .") as num from company". $sql_where ." ) as temp where num between ". ( ($cfg['page']-1) * $cfg['size'] + 1 ) ." and " . ( $cfg['page'] * $cfg['size'] );
		$sql_count = "select count(*) as icount from company" . $sql_where;
		$result = $this->mall_cmp_model->get_list($sql, $sql_count);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->url_template_first = $this->manage_url . 'mall/company/manage?s=' . $cfg['sort'] . '&key=' . $filter['key'] . '&flag=' . $filter['flag'];
		$this->pagination->url_template = $this->manage_url . 'mall/company/manage?page=<{page}>&s=' . $cfg['sort'] . '&key=' . $filter['key'] . '&flag=' . $filter['flag'];
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $result['count']);
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->assign('filter', $filter);
		$this->tpl->assign('module', 'cmp.manage');
		$this->tpl->display('admin/mall/cmp_manage.html');
		
	}
	
	// 建材商配置界面
	public function settings(){
		
		$id = $this->gr('id');
		
		$this->load->model('mall/mall_cmp_model');
		$this->load->model('mall/mall_category_model');
		
		$cmp = $this->mall_cmp_model->get_company($id, 'id, username, company, koubei, is_rz, ctype, msn, delcode, flag');
		
		$categories = $this->mall_category_model->get_roots();
		
		$this->tpl->assign('categories', $categories);
		
		$this->tpl->assign('cmp', $cmp);
		$this->tpl->assign('rurl', $this->encode->get_rurl());
		$this->tpl->assign('module', 'cmp.settings');
		$this->tpl->display('admin/mall/cmp_settings.html');
		
	}
	
	// 建材商配置提交处理
	public function settings_handler(){
		
		$id = $this->gf('id');
		
		$settings = array(
			'rank'=>$this->gf('rank'),		// 权重值
			'scope'=>$this->gf('scope'),	// 经营范围
			'authentication'=>$this->gf('authentication'),	// 线下认证合作会员
			'pwd'=>$this->gf('pwd'),
			'kf'=>$this->gf('kf'),
			'delete'=>$this->gf('delete'),
			'flag'=>$this->gf('recommend')
		);
		
		$this->load->model('mall/mall_cmp_model');
		try{
			$this->mall_cmp_model->settings($id, $settings);
			$this->alert('提交成功', $this->encode->get_rurl());
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
		
	}
	
	
}

?>