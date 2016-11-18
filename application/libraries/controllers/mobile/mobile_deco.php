<?php
class mobile_deco extends mobile_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function index(){
		
		$tpl = $this->get_tpl('deco/home.html');
		$this->tpl->caching = true;
		
		$this->tpl->cache_lifetime = 10;	// 缓存10s
		
		
		$sheng_id = $this->encode->get_request_encode('sheng_id');
		if( empty($sheng_id) ) $sheng_id = 9968;	// 上海市ID
		$shi_id = $this->encode->get_request_encode('shi_id');
		$page = $this->encode->get_request_encode('page');
		if( empty($page) || ! preg_match('/^[1-9]\d*$/', $page) ) $page = 1;
		
		$cache_dir = 'deco/list/' . $sheng_id . '/';
		
		if( $shi_id != '' ){
			$cache_dir .= $shi_id;
		} else {
			$cache_dir .= '000';
		}
		
		$this->tpl->cache_dir = $this->get_cache_dir($cache_dir);
		
		$cache_id = $page;
		
		if( ! $this->tpl->isCached($tpl, $cache_id) ){
			
		$this->load->library('encode');
		$this->load->library('thumb');
		$this->load->model('company/company', 'deco_model');
		$this->load->model('city_model');
		
		$this->load->model('company/usercase');
		
		
		$params = array('sheng_id'=>$sheng_id, 'shi_id'=>$shi_id, 'page'=>$page);
		// 城市相关数据设置
		$city_childs = $this->city_model->get_childs($sheng_id);
		array_push($city_childs, array('id'=>'', 'cname'=>'全部'));
		foreach($city_childs as $key=>$value){
			$city_childs[$key]['link'] = $this->mobile_url . 'deco?sheng_id=' . $sheng_id . '&shi_id=' . $value['id'];
		}
		$sheng_city = $this->city_model->get_item($sheng_id);
		$shi_city = $this->city_model->get_item($shi_id);
		$this->tpl->assign('shi_city', $shi_city);
		$this->tpl->assign('city_childs', $city_childs);
		// 公司数据信息
		$page_size = 30;
		
		
		//echo('<!--');
		//var_dump($shi_city);
		//echo('-->');
		
		
		/*
		$where_sheng = "user_shen = '". $sheng_city['cname'] ."'";
		if( $shi_city['cname'] != '' ){
			$where_shi = "user_town = '" . $shi_city['cname'] . "'";
		} else {
			$where_shi = '1=1';
		}
		*/
		$where_sheng = "user_shen = '". $params['sheng_id'] ."'";
		if( $shi_city['cname'] != '' ){
			$where_shi = "user_town = '" . $params['shi_id'] . "'";
		} else {
			$where_shi = '1=1';
		}
		
		
		$select_sql = "select top ". $page_size ." id, username, company, logo, user_shen, user_city, user_town, company_date, address, koubei, koubei_total, flag, mobile, tel from company where ". $where_sheng . " and " . $where_shi ." and delcode=0 and company <> '' and hangye='装潢公司' and id not in ( select top ". ( $params['page']-1 )*$page_size ." id from company where ". $where_sheng . " and " . $where_shi ." and company <> '' and hangye='装潢公司' and delcode=0 order by koubei desc ) order by koubei desc";
		
		$count_sql = "select count(*) as icount from company where delcode = 0 and company <> '' and hangye='装潢公司' and " . $where_shi;
		
		$result = $this->deco_model->get_list($select_sql, $count_sql, true);
		$list = $result['list'];
		
		$list = $this->usercase->case_count_assign_users($list);
		
		$list = $this->mobile_url_model->format_batch_deco($list);
		$count = $result['count'];
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $params['page'];
		$this->pagination->pageCount = $this->pagination->getPageCount($page_size, $count);
		$this->pagination->url_template = $this->mobile_url . 'deco?sheng_id='.$params['sheng_id'].'&shi_id='.$params['shi_id'].'&page=<{page}>';
		$this->pagination->url_template_first = $this->mobile_url . 'deco?sheng_id='.$params['sheng_id'].'&shi_id='.$params['shi_id'];
		$pagination = $this->pagination->tostring_simple(true);
		
		// logo 缩略图
		$this->thumb->setPathType(1);
		foreach($list as $key=>$value){
			if( !empty($value['logo']) ){
				$list[$key]['logo_thumb'] = $this->thumb->resize($value['logo'], 147, 108);
			} else {
				$list[$key]['logo_thumb'] = '';
			}
		}
		$this->tpl->assign('list', $list);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('params', $params);
		$this->tpl->assign('data_count', $count);
		$this->tpl->assign('title', '上海装修公司,上海装修公司排名,为您精选3000家上海装修公司排行榜');
		
		
		$this->tpl->display($tpl, $cache_id);
		
		} else {
			$this->tpl->display($tpl, $cache_id);
			echo('<!-- cached -->');
		}
		
	}
	
}
?>