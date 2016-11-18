<?php

// 商品列表检索页面
class tuan_itemlist extends tuan_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	private function _sort_types(){
		return array(
			array('key'=>'price', 'value'=>'价格'),
			array('key'=>'time', 'value'=>'最新')
		);
	}
	
	public function home(){
		
		// 参数集
		$args = array();
		$args['bid'] = $this->gr('bid');		// 一级分类
		$args['sid'] = $this->gr('sid');		// 二级分类
		$args['brd'] = $this->gr('brd');		// 品牌ID
		$args['sort'] = $this->gr('sort');
		
		
		//额外属性
		$args['atr'] = $this->gr('atr');
		
		if( ! empty($args['atr']) ){
			if( ! preg_match('/^\d+(,\d+)*$/', $args['atr']) ){
				show_error('atr格式错误', 404);
				exit();
			} else {
				$atr = explode(',', $args['atr']);
			}
		} else {
			$atr = array();
		}
		
		
		$config = array(
			'page'=>$this->encode->get_page('page'),
			'size'=>24
		);
		
		// 参数合法化检查
		if( preg_match('/^\d+$/', $args['bid']) == false ){
			show_404();
			exit();
		}
		
		if( ! empty($args['sid']) ){
			if( ! preg_match('/^\d+$/', $args['sid']) ){
				show_404();
				exit();
			}
		}
		
		if( ! empty($args['brd']) ){
			if( ! preg_match('/^\d+$/', $args['brd']) ){
				show_404();
				exit();
			}
		}
		
		// 载入必要的模型
		$this->load->model('mall/mall_category_model');
		$this->load->model('mall/mall_brand_model');
		$this->load->model('mall/mall_product_model');
		$this->load->model('mall/mall_cmp_model');
		$this->load->library('pagination');
		$this->load->library('thumb');
		
		$categories = $this->mall_category_model->get_status(array('disabled_opt' => true));
		$cat1 = $this->mall_category_model->get_category($args['bid']);
		
		if( empty( $args['sid'] ) ){
			$cat2 = $this->mall_category_model->get_default_child($args['bid']);
			$args['sid'] = $cat2['id'];
		} else {
			$cat2 = $this->mall_category_model->get_category($args['sid']);
		}
		
		$brds = $this->mall_brand_model->get_brands($args['bid'], $args['sid']);
		$brds[] = array(
			'id'=>'',
			'brand'=>'全部',
			'link'=>$this->mall_url . 'itemlist?' . $this->_parse_url(array('args'=>$args, 'remove'=>'brd'))
		);
		$brd = $this->mall_brand_model->get_brand($args['brd']);
		
		foreach($brds as $key=>$value){
			$brds[$key]['link'] = $this->_parse_url(
				array(
					'base_url'=>rtrim($this->mall_url, '/') . '/itemlist',
					'args'=>$args,
					'add'=>array('brd'=>$value['id'])
				)
			);
		}
		
		// 分类属性拉取
		$this->load->model('mall/mall_attr_model');
		$attrs = $this->mall_attr_model->get_cat_attrs(array(
			'cid'=>$args['sid']
		));
		$attrs = $this->mall_attr_model->childs_assign($attrs);
		foreach($attrs as $key=>$attr){
			$remove_value = '';
			$new_array = array();
			foreach($attr['childs'] as $k=>$v){
				foreach($atr as $atr_temp){
					if( $atr_temp == $v['id'] ){
						$remove_value = $atr_temp;
						break;
					}
				}
			}
			
			foreach($atr as $atr_temp){
				if( $atr_temp != $remove_value ) $new_array[] = $atr_temp;
			}
			
			$attr['childs'][] = array(
				'id'=>'',
				'name'=>'全部'
			);
			
			foreach($attr['childs'] as $k=>$v){
				
				
				if( in_array($v['id'], $atr) ){
					$v['current'] = true;
				} else {
					$v['current'] = false;
				};
				
				$atr_temp = implode(',', $new_array);
				
				if( ! empty($v['id']) ){
					if( $atr_temp != '' ){
						$atr_temp .= ',' . $v['id'];
					} else {
						$atr_temp = $v['id'];
					}
				}
				
				$v['link'] = $this->_parse_url(
					array(
						'args'=>$args,
						'remove'=>'atr',
						'add'=>array('atr'=>$atr_temp)
					)
				);
				
				$attr['childs'][$k] = $v;
				
			}
			
			$attrs[$key] = $attr;
			
		}
		
		$this->tpl->assign('attrs', $attrs);
		
		// ====================== 分类属性处理 结束 ==================
		
		
		// 审核通过的产品
		$where_sql = " where flag > 0 and smallclass = '". $args['bid'] ."'";
		
		if( ! empty($args['sid']) ){
			$where_sql .= " and prdclass = '". $args['sid'] ."'";
		}
		
		if( ! empty( $args['atr'] ) ){
			$where_sql .= " and id in ( select pid from [mall_prd_attr] where 1=1";
			foreach($atr as $item){
				$where_sql .= " and attr_id in ( ". $item ." )";
			}
			$where_sql .= " )";
		}
		
		// 正常存在的用户
		$where_sql .= " and senduser in ( select username from company where delcode = 0 )";
		
		if( !empty($args['brd']) ){
			$where_sql .= " and brandid = '". $args['brd'] ."'";
		}
		
		$sort_sql = "";
		switch($args['sort']){
			case 'price':
				$sort_sql = "order by webprice desc";
				break;
			default:
				$sort_sql = "order by puttime desc";
				break;
		}
		
		
		$sql = "select * from ( select id, title, imgpath as image, smallclass, prdclass, brandid, price, webprice, senduser, row_number() over(". $sort_sql .") as num from sendbuy". $where_sql .") as temp where num between ". (($config['page']-1) * $config['size'] + 1) ." and " . ( $config['page'] * $config['size'] );
		$sql_count = "select count(*) as icount from sendbuy" . $where_sql;
		
		$result = $this->mall_product_model->get_list($sql, $sql_count);
		$list = $result['list'];
		$list = $this->mall_cmp_model->company_assign($list);
		
		foreach($list as $key=>$value){
			$list[$key]['thumb'] = $this->thumb->resize($value['image'], 234, 163);
		}
		
		$this->pagination->currentPage = $config['page'];
		$this->pagination->pageCount = $this->pagination->getPageCount($config['size'], $result['count']);
		
		$this->pagination->url_template_first = $this->mall_url . 'itemlist?' . $this->_parse_url(array('args'=>$args));
		$this->pagination->url_template = $this->mall_url . 'itemlist?page=<{page}>&' . $this->_parse_url(array('args'=>$args));
		$pagination = $this->pagination->toString(true);
		
		/* 排序类型 */
		$sorts = $this->_sort_types();
		foreach($sorts as $key=>$value){
			$sorts[$key]['link'] = $this->_parse_url(
				array(
					'base_url'=>rtrim($this->mall_url, '/') . '/itemlist',
					'args'=>$args,
					'add'=>array('sort'=>$value['key'])
				)
			);
		}
		
		$this->tpl->assign('pagination', $pagination);
		
		$this->tpl->assign('pagi', $this->pagination);
		$this->tpl->assign('args', $args);
		$this->tpl->assign('categories', $categories);
		$this->tpl->assign('cat1', $cat1);
		$this->tpl->assign('cat2', $cat2);
		$this->tpl->assign('brds', $brds);
		$this->tpl->assign('list', $list);
		$this->tpl->assign('sorts', $sorts);
		$this->tpl->assign('title', implode('|', array($cat1['class_name'], $cat2['class_name'], $brd['brand'])));
		
		
		
		$this->tpl->display('tuan/itemlist.html');
	}
	
	// 链接分析
	/*
	$config = array(
		'args'=>array(
			'bid'=>100,
			'sid'=>200,
			'brd'=>300,
			'page'=>5
		),
		'remove'=>'page', 或者 'remove'=>array('page', 'brd')
		
		'add'=>array(
			'page'=>5,
			'bid'=>10
		),
		'base_url'=>'http://mall.shzh.net/itemlist'
	);
	*/
	private function _parse_url($config){
		$args = array_change_key_case( $config['args'] );
		
		if( isset( $config['remove'] ) ){
			$remove = $config['remove'];
			if( ! is_array($remove) ) $remove = array($remove);
			foreach($remove as $item){
				unset( $args[strtolower($item)] );
			}
		}
		
		if( isset($config['add']) ){
			$add = $config['add'];
			foreach($add as $key=>$value){
				$args[strtolower($key)] = $value;
			}
		}
		
		$string = array();
		foreach($args as $key=>$value){
			if( $value != '' ){
				$string[] = $key . '=' . $value;
			}
		}
		
		$params = implode('&', $string);
		
		if( ! isset( $config['base_url'] ) ){
			$config['base_url'] = $this->mall_url . 'itemlist';
		}
		
		if( ! strpos($config['base_url'], '?') ){
			return $config['base_url'] . '?' . $params;
		}
		
		return $config['base_url'] . $params;
	}
	
	
	/*
	private function _attr_parse_values($default, $value){
		if( ! in_array($default, $value) ){
			$default[] = $value;
		} else {
			
		}
	}
	*/
	
}

?>