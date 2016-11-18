<?php

// 手机网页 首页

if( !defined('BASEPATH') ) exit('禁止浏览');

class mobile_index extends mobile_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function index(){
		
		$this->tpl->caching = true;
		
		$tpl = $this->get_tpl('home.html');
		
		$this->tpl->cache_lifetime = 60 * 10;	// 缓存时间 10 分钟
		$this->tpl->cache_dir = $this->get_cache_dir();
		
		if( ! $this->tpl->isCached($tpl) ){
			
			// 装修订单
			$this->load->model('publish/deco_orders_model');
			
			// 装修效果图
			$this->load->model('photo/photo', 'album_model');
			$this->load->model('photo/album_category_model');
			$this->load->model('photo/album_image_model');
			
			// 资讯
			$this->load->model('article/Article', 'art_model');
			
			// 生成缩略图组件
			$this->load->library('thumb');
			
			
			
			$orders = $this->deco_orders_model->get_deco_order_list("select top 10 id, town, fullname, sroom as room, housearea as area, addtime from sendzh where hide = 0 order by addtime desc");
			// $order_count = $this->deco_orders_model->get_count("select count(*) as icount from sendzh");
			
			$this->load->model('publish/pubModel', 'pub_model');
			$order_count = $this->pub_model->get_total_count();
			$order_count = $order_count['count2'];
			
			$this->tpl->assign('orders', $orders['list']);
			$this->tpl->assign('order_count', $order_count);
			
			// 规范化分类 2016
			$album_cats = array(
				array('id'=>5, 'alias'=>'简约'),
				array('id'=>20, 'alias'=>'玄关'),
				array('id'=>100, 'alias'=>'背景墙'),
				array('id'=>67, 'alias'=>'别墅')
			);
			$album_cats_tmep_ids = array();
			foreach($album_cats as $item){
				$album_cats_tmep_ids[] = $item['id'];
			}
			$result = $this->album_category_model->get_list("select id, name from [photo_category] where id in (". implode(',', $album_cats_tmep_ids) .")");
			
			foreach($album_cats as $key=>$item){
				$item['info'] = false;
				foreach($result['list'] as $it){
					if( $it['id'] == $item['id'] ){
						$item['info'] = $it;
						break;
					}
				}
				$album_cats[$key] = $item;
			}
			unset($result);
			
			$album_cats = $this->album_category_model->paste_target( $album_cats );
			$album_cats = $this->mobile_url_model->format_batch_album_category($album_cats);

			// 为分类附加图片
			foreach($album_cats as $key=>$item){
				$sql = "select * from (select id, name, imagepath as image, num = row_number() over(order by addtime desc) from [photo_album_image] where id in (select image_id from [photo_image_category_relation] where cid = '". $item['id'] ."') ) as temp where num <= 4 ";
				$_temp = $this->album_image_model->get_list($sql);
				$_temp = $_temp['list'];
				foreach($_temp as $k=>$v){
					$_temp[$k]['thumb'] = $this->thumb->crop($v['image'], 350, 350);
				}
				$_temp = $this->mobile_url_model->format_batch_album_image($_temp);
				$album_cats[$key]['elements'] = $_temp;
			}
			
			$this->tpl->assign('album_cats', $album_cats);

			// 资讯
			$arts = $this->art_model->get_list("select top 3 id, title, description, imgpath as image, clsid from art_art where imgpath <> '' order by addtime desc");
			$arts = $this->mobile_url_model->format_batch_article($arts['list']);
			
			foreach($arts as $k=>$v){
				$arts[$k]['thumb'] = $this->thumb->crop($v['image'], 160, 160);
			}
			
			$this->tpl->assign('arts', $arts);
			
			$this->tpl->display($tpl);
		
		} else {
			
			$this->tpl->display($tpl);
			echo('<!-- is cached -->');
			
		}
		
		
	}
	
	
}
?>