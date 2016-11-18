<?php

// 样板房案例
class mobile_sample extends mobile_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 样板房首页
	// 2015-03-25
	public function index(){
		
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60 * 60 * 24;	// 24 小时
		
		//if( $this->gr('v') != 2016 ){
		//	$tpl = 'mobile/sample/index.html';
		//	$this->tpl->cache_dir .= 'mobile\\sample\\';
		//} else {
		$tpl = $this->get_tpl('sample/home.html');
		$this->tpl->cache_dir = $this->get_cache_dir('sample');
		//}
		
		if( ! $this->tpl->isCached( $tpl ) ){
			
			$this->load->model('archive/archive_cas_model');
			
			// 查询 样板房 图集
			$cas = $this->archive_cas_model->get_list("select id, tid, name, fm, style, budget, area from archive_cas where sample = 1 order by name Asc");
			$cas = $cas['list'];
			$cas = $this->_format_batch_sample($cas);
			
			$this->tpl->assign('cas', $cas);
			
			// 缩略图
			$this->load->library('thumb');
			foreach($cas as $key=>$value){
				$cas[$key]['thumb'] = $this->thumb->crop($value['fm'], 110, 60);
			}
			
			$cas = $this->mobile_url_model->format_batch_sample($cas);
			$this->tpl->assign('samples', $cas);
			
			$this->tpl->assign('title', '八种风格线下体验馆');
			$this->tpl->assign('page_name', '体验馆');
			
			$this->tpl->display($tpl);
			
		} else {
			
			$this->tpl->display($tpl);
			echo('<!-- cached -->');
			
		}
	}
	
	// 样板房详情
	public function view(){
		
		$id = $this->gr('id');
		
		
		//if( $this->gr('v') == 2016 ){
		$tpl = $this->get_tpl('sample/view.html');
		//} else {
		//	$tpl = 'mobile/sample/view.html';
		//}
		
		
		
		$this->load->model('archive/archive_cas_model');
		$this->load->model('archive/archive_album_model');
		$this->load->model('archive/archive_prod_model');
		
		$cas = $this->archive_cas_model->get_cas($id, 'id, tid, name, address, community, huxing, area, budget, detail, fm, style, wages, sdate, edate, video');
		$cas = $this->_format_sample($cas);
		
		$cas['date_limit'] = floor((strtotime( $cas['edate'] )-strtotime( $cas['sdate'] )) / 86400);
		
		$cas['stages'] = $this->archive_album_model->get_album_stages($cas['id']);		// 附加阶段数据
		$cas['stages'] = $this->archive_album_model->assign_images($cas['stages']);		// 附加图片数据
		
		$cas = $this->archive_album_model->image_count_assign_albums($cas);		// 附加图片数量统计
		$cas = $this->mobile_url_model->format_single_sample_cas($cas);			// 附加手机端链接
		
		// 产品数据
		$cas['prds'] = $this->archive_prod_model->get_cas_prds($cas['id'], array(
			'bind_mall'=>false,
			'fields'=>'id, cas_id, prd_id, name, unit, price, brand, amount'
		));
		
		$images = array();	// 图集数组
		
		foreach($cas['stages'] as $key=>$value){
			$cas['stages'][$key]['images'] = $this->mobile_url_model->format_batch_sample_image($value['images']);
			if( count($images) < 4 ){
				foreach($cas['stages'][$key]['images'] as $image){
					$images[] = $image;
					if( count($images) >= 4 ) break;
				}
			}
		}
		
		$this->load->library('thumb');
		
		// 生成缩略图
		foreach($images as $key=>$value){
			$images[$key]['thumb'] = $this->thumb->crop($value['image'], 350, 350);
		}
		
		$this->tpl->assign('cas', $cas);
		$this->tpl->assign('images', $images);
		
		$this->tpl->assign('page_name', $cas['style'] . '体验馆');
		$this->tpl->assign('title', $cas['style'] . '体验馆');
		
		$this->tpl->display($tpl);
	}
	
	// 相册浏览
	public function album(){
		
		$cas_id = $this->gr('id');				// 案例id
		$image_id = $this->gr('image_id');		// 单图ID
		
		if( empty($image_id) ) $image_id = 0;
		
		$this->load->model('archive/archive_cas_model');
		$this->load->model('archive/archive_album_model');
		
		if( empty($cas_id) ){
			$cas = $this->archive_cas_model->get_album_by_image($image_id, 'id, name, fm');
		} else {
			$cas = $this->archive_cas_model->get_cas($cas_id, 'id, name, fm');
		}
		
		$cas['stages'] = $this->archive_album_model->get_album_stages($cas['id']);		// 附加阶段数据
		$cas['stages'] = $this->archive_album_model->assign_images($cas['stages']);		// 附加图片数据
		
		// 所有图片数据
		$images = array();
		foreach($cas['stages'] as $key=>$value){
			foreach($value['images'] as $k=>$v){
				$images[] = $v;
			}
		}
		
		$tpl = 'mobile/sample/album.html';
		
		$this->tpl->assign('cas', $cas);
		$this->tpl->assign('images', $images);
		$this->tpl->assign('image_id', $image_id);
		$this->tpl->display( $tpl );
		
	}
	
	private function _format_sample($sample){
		$this->load->model('archive/archive_cas_model');
		return $this->archive_cas_model->format_sample($sample);
	}
	
	private function _format_batch_sample($list){
		$this->load->model('archive/archive_cas_model');
		return $this->archive_cas_model->format_batch_sample($list);
	}
	
	// 预约提交处理
	public function handler(){
		$info = array(
			'name'=>$this->gf('name'),
			'mobile'=>$this->gf('mobile'),
			'address'=>'',
			'tid'=>$this->gf('tid')
		);
		
		if( $info['name'] == '' || $info['mobile'] == '' || $info['tid'] == '' ){
			exit('信息输入不完整');
		}
		
		$this->load->model('archive/archive_visit_model');
		
		try{
			$this->archive_visit_model->add($info);
			echo(1);
		}catch(Exception $e){
			exit( $e->getMessage() );
		}
		
		
	}
	
}

















?>