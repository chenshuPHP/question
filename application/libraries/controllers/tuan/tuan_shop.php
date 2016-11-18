<?php

// 商铺系列频道控制器
class tuan_shop extends tuan_base {
	
	public $username = NULL;	// 店铺用户
	public $info = NULL;		// 店铺用户基本资料
	public $categories = NULL;
	
	public function __construct(){
		parent::__construct();
	}
	
	private function initialize(){
		$this->get_user();
		$this->get_company_info();
		$this->assign();
	}
	
	// 获取店铺用户名
	private function get_user($label = 'u'){
		
		if( ! empty($this->username) ) return;
		
		$value = $this->gr($label);
		if( empty($value) ){
			show_404();
			exit();
		}
		$this->username = strtolower( $value );
	}
	
	// 获取店铺基本资料
	private function get_company_info(){
		$this->load->model('mall/mall_cmp_model');
		$temp = $this->mall_cmp_model->get_company2($this->username, 'username, company, rejion, sex, email, tel, mobile, fax, address, flag, puttime, logo, website, lastlogin, qq, msn, shortname, user_shen, user_city, user_town');
		if( $temp == false ){
			show_error('找不到商家资料', 404);
			exit();
		}
		$this->info = &$temp;
		
	}
	
	// 公共数据分配到模版
	private function assign(){
		// 店铺自定义分类
		$this->load->model('mall/mall_usercat_model');
		$this->categories = $this->mall_usercat_model->get_structs($this->username);
		$this->tpl->assign('user_cats', $this->categories);
		
		$this->tpl->assign('cmp', $this->info);
		$this->tpl->assign('title', $this->info['company']);
	}
	
	// 店铺产品列表页面
	public function itemlist(){
		
		$this->initialize();

		$this->load->model('mall/mall_product_model');
		$this->load->library('pagination');
		$this->load->library('thumb');
		
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>18,
			'cat1'=>$this->gr('cat1'),
			'cat2'=>$this->gr('cat2')
		);
		
		// 用户自定义分类
		$cat1 = NULL;
		$where = " where senduser = '". $this->username ."' and flag > 0";
		
		$param_string = '?u=' . $this->username;
		
		if( !empty($args['cat1']) ){
			$where .= " and id in ( select pid from mall_pro_usercat where cid = '". $args['cat1'] ."' and t = 1 )";
			foreach($this->categories as $key=>$item){
				if( $item['id'] == $args['cat1'] ){
					$cat1 = $item;
					break;
				}
			}
			$param_string .= '&cat1=' . $args['cat1'];
		}
		$this->tpl->assign('cat1', $cat1);
		
		if( ! empty($args['cat2']) ){
			$where .= " and id in ( select pid from mall_pro_usercat where cid = '". $args['cat2'] ."' and t = 2 )";
			$param_string .= '&cat2=' . $args['cat2'];
		}
		
		$sql = "select * from ( select id, imgpath as image, title, price, webprice, senduser, row_number() over( order by puttime desc ) as num from sendbuy". $where ." ) as temp where num between ". (($args['page'] - 1) * $args['size'] + 1) ." and " . ( $args['size'] * $args['page'] );
		$sql_count = "select count(*) as icount from sendbuy" . $where;
		
		$result = $this->mall_product_model->get_list($sql, $sql_count);
		$list = $result['list'];
		$count = $result['count'];
		unset($result);
		
		foreach($list as $key=>$value){
			$list[$key]['thumb'] = $this->thumb->resize($value['image'], 234, 163);
		}
		
		$this->pagination->recordCount = $count;
		$this->pagination->pageSize = $args['size'];
		$this->pagination->currentPage = $args['page'];
		$this->pagination->url_template_first = $this->mall_url . 'shop/itemlist' . $param_string;
		$this->pagination->url_template = $this->mall_url . 'shop/itemlist' . $param_string . '&page=<{page}>';
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('args', $args);
		$this->tpl->assign('list', $list);
		$this->tpl->assign('pagination', $pagination);
		
		$this->tpl->assign('module', 'shop.product');
		$this->tpl->display('tuan/shop/itemlist.html');
	}
	
	// 商铺介绍
	public function des(){
		$this->initialize();
		
		$desc = $this->mall_cmp_model->get_company2($this->username, 'content');
		
		$desc = $this->encode->htmldecode($desc['content']);
		$desc = $this->encode->remove_outside_link($desc, array('shzh.net'));	// 移除外链
		
		$desc = str_replace('/upimg/', 'http://img1.shzh.net/', $desc);
		
		
		$this->tpl->assign('desc', $desc);
		$this->tpl->assign('module', 'shop.description');
		$this->tpl->display('tuan/shop/des.html');
	}
	
	// 资质证书
	public function cert(){
		
		$this->initialize();
		
		$this->load->model('mall/mall_usercert_model');
		$this->load->library('thumb');
		
		$sql = "select imgpath as image, senduser, zizhiname as name from zizhiimg where senduser = '". $this->username ."' order by sortid asc";
		
		$result = $this->mall_usercert_model->get_list($sql);
		$list = $result['list'];
		unset($result);
		foreach($list as $key=>$value){
			$list[$key]['thumb'] = $this->thumb->resize($value['image'], 170, 130);
		}
		
		//var_dump($list);
		$this->tpl->assign('list', $list);
		$this->tpl->assign('module', 'shop.certificate');
		$this->tpl->display('tuan/shop/certificate.html');
		
	}
	
	// 联系方式
	public function contact(){
		$this->initialize();
		$info = $this->mall_cmp_model->get_company2($this->username, "username, rejion, tel, mobile, company, address, logo, lastlogin, user_shen, user_city, user_town");
		$this->tpl->assign('info', $info);
		$this->tpl->assign('module', 'shop.contact');
		$this->tpl->display('tuan/shop/contact.html');
		
	}
	
	public function item($id){
		
		if( ! preg_match('/^\d+$/', $id) ){
			show_404();
			exit();
		}
		
		$this->load->model('mall/mall_product_model');
		$this->load->model('mall/mall_category_model');
		$this->load->model('mall/mall_brand_model');
		$this->load->library('thumb');
		
		// 产品
		$prd = $this->mall_product_model->get_product($id, 'id, title, content, imgpath as thumb, puttime, senduser, smallclass, prdclass, brandid, price, webprice, flag');
		$prd['thumbs'] = $this->mall_product_model->get_thumbs($id, array(
			'top'=>6
		));
		$prd['brand'] = $this->mall_brand_model->get_brand($prd['brandid'], array('bid'=>$prd['smallclass'], 'sid'=>$prd['prdclass']));
		
		// 移除外链
		$prd['content'] = $this->encode->remove_outside_link($prd['content']);
		
		if( ! $prd ){
			show_404();
			exit();
		}
		
		$cat = array();
		$cat[] = $this->mall_category_model->get_category($prd['smallclass']);
		$cat[] = $this->mall_category_model->get_category($prd['prdclass']);
		
		if( count($prd['thumbs']) > 0 ){
			foreach($prd['thumbs'] as $key=>$value){
				$prd['thumbs'][$key]['thumb2'] = $this->thumb->crop($value['thumb'], 46, 46);
			}
		}
		
		$this->username = $prd['senduser'];
		$this->initialize();
		
		$this->tpl->assign('prd', $prd);
		$this->tpl->assign('cat', $cat);
		$this->tpl->assign('module', 'shop.product_detail');
		// SEO
		$this->tpl->assign('title', $prd['title']);
		
		// kf 链接
		$kf = $this->config->item('kf');
		$this->tpl->assign('53kf_url', $kf['53kf_url']);
		
		$this->tpl->display('tuan/item.html');
		
	}
	
}


?>