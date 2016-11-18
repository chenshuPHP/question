<?php

class tuan_index extends tuan_base {
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		
		$this->tpl->caching = false;
		$this->tpl->cache_lifetime = 10*60;	// 10分钟
		$cache_dir = $this->tpl->cache_dir . 'mall/';	// 设置这个模版的缓存目录
		$this->tpl->cache_dir = $cache_dir;
		
		$tpl = 'tuan/home.html';
		
		if(! $this->tpl->isCached($tpl) ){

		$this->load->model('mall/mall_category_model');
		$this->load->model('mall/mall_brand_model');
		$this->load->model('mall/mall_product_model');
		$this->load->model('mall/mall_cmp_model');
		$this->load->library('thumb');
		
		$categories = $this->mall_category_model->get_status();
		
		// 罗列需要推荐显示的ID数组
		$show_cate_ids = array();
		$cates = array();
		
		for($i = 0; $i < 3; $i++){
			$show_cate_ids[] = $categories[$i]['id'];
		}
		
		foreach($categories as $item){
			if( in_array($item['id'], $show_cate_ids) ){
				
				$item['brand'] = $this->mall_brand_model->get_brands($item['id'], 0, array('size'=>18));
				
				// 加载产品
				//$where_sql = " where flag > 0 and smallclass = '". $item['id'] ."' and senduser in ( select username from company where delcode = 0 and hangye = '建材公司' and register = 2 ) and recommend = 1";
				//$sql = "select top 6 * from ( select id, title, imgpath as image, price, webprice, brandid, senduser, input, num = row_number() over(partition by senduser order by input desc) from sendbuy". $where_sql .") as temp where num <= 1 order by input desc";
				$sql = "select top 6 id, title, imgpath as image, price, webprice, brandid, senduser, input from sendbuy where flag > 0 and smallclass = '". $item['id'] ."' and recommend = 1 and senduser in ( select username from company where delcode = 0 and hangye = '建材公司' and register = 2 ) order by puttime asc";
				
				$prds = $this->mall_product_model->get_list($sql);
				$prds = $prds['list'];
				$i = 0;
				foreach($prds as $key=>$value){
					if( $i >= 4 ){
						$prds[$key]['thumb'] = $this->thumb->resize($value['image'], 140, 145);
					} else {
						$prds[$key]['thumb'] = $this->thumb->resize($value['image'], 137, 118);
					}
					$i++;
				}
				
				$prds = $this->mall_brand_model->stick_brand($prds);
				// $prds = $this->mall_cmp_model->company_assign($prds);
				$item['prds'] = $prds;
				
				// 加载公司
				$cmp_sql = "select * from ( select id, company, username, row_number() over( order by iput desc ) as num from company where username in ( select senduser from sendbuy where smallclass = '". $item['id'] ."' and flag > 0 ) and delcode = 0 and company <> '' and flag > 1 ) as temp where num <= 10";
				$cmps = $this->mall_cmp_model->get_list($cmp_sql);
				$cmps = $cmps['list'];
				
				$item['cmps'] = $cmps;
				
				$cates[] = $item;
			}
		}
		$cates[0]['t_img'] = '1541.jpg';
		$cates[1]['t_img'] = '_r2_c2.jpg';
		$cates[2]['t_img'] = '_r4_c2.jpg';
		
		// 额外的广告
		$cates[2]['advs'] = array();
		array_pop($cates[2]['prds']);
		$cates[2]['advs'][] = array(
			'name'=>'大金空调上海总代理',
			'img'=>'0934.jpg',
			'link'=>'http://mall.shzh.net/shop/itemlist?u=shmszl',
			'addtime'=>'2015-04-24'
		);
		
		
		
		$this->tpl->assign('cates', $cates);
		$this->tpl->assign('categories', $categories);
		$this->tpl->assign('title', '建材商城');
		$this->tpl->display($tpl);
		
		} else {
			$this->tpl->display($tpl);
			echo('<!-- cache -->');
		}
	
	}
	
}
?>