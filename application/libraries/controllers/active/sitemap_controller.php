<?php

// 2015-01-14 Sitemap
// kko4455@163.com

class sitemap_controller extends MY_Controller {
	
	public function __construct(){
		$config = array(
			'xss_disabled'=>true,
			'rurl_disabled'=>true,
			'header'=>false
		);
		parent::__construct( $config );
		
		$this->tpl->assign('doctype', '<?xml version="1.0" encoding="utf-8"?>');
		$this->tpl->assign('urls', $this->config->item('url'));
		
	}
	
	
	public function _remap($class, $args = array()){
		
		$class = strtolower($class);
		$class = explode('.', $class);
		
		$class = $class[0];
		
		$method = '';
		$args = array();
		
		switch(true){
			case preg_match('/^[^\-]+\-\d+$/', $class) : 
				$args = explode('-', $class);
				$method = array_shift( $args );
				break;
			default:
				$method = $class;
				break;
		}
		
		if( ! method_exists($this, $method) ){
			show_404();
		}
		
		if( count($args) > 0 ){
			$this->$method($args);
		} else {
			$this->$method();
		}
		
	}
	
	
	// 主域名 手机适配
	public function home(){
		header('Content-type:text/xml;charset=UTF-8');
		$this->tpl->display('active/sitemap/home.xml.html');
	}
	
	// 文章 手机适配
	public function article(){
		header('Content-type:text/xml;charset=UTF-8');
		$this->tpl->display('active/sitemap/article.xml.html');
	}
	
	// 360 全站适配
	public function site_adapter_360(){
		header('Content-type:text/html;charset=UTF-8');
		$this->tpl->display('active/sitemap/site_adapter_360.html');
	}
	
	
	public function article_detail($args = array()){
		header('Content-type:text/xml;charset=UTF-8');
		$config = array(
			'size'=>10000,
			'page'=>$args[0]
		);
		if( empty($config['page']) ) $config['page'] = 1;
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60 * 60 * 12;	// 缓存时间 12 小时
		$this->tpl->cache_dir .= 'sitemap\\';
		// 新版手机网页模版 
		$tpl = 'active/sitemap/article_detail.map.html';
		if( ! $this->tpl->isCached($tpl, $config['page']) ){
			$this->load->model('article/article', 'article_model');
			$sql = "select * from ( select id, clsid, addtime, row_number() over( order by id asc ) as num from art_art ) as temp where num between ". (($config['page'] - 1) * $config['size'] + 1) ." and " . ( $config['page'] * $config['size'] );
			$arts = $this->article_model->getCustomNews($sql);
			if( count($arts) == 0 ) exit('找不到数据');
			$this->tpl->assign('arts', $arts);
			$this->tpl->display($tpl, $config['page']);
		} else {
			$this->tpl->display($tpl, $config['page']);
			echo('<!-- cached -->');
		}
	}
	
	// 图库 图片单页
	public function tuku_image($args = array()){
		
		header('Content-type:text/xml;charset=UTF-8');
		$config = array(
			'size'=>5000,
			'page'=>$args[0]
		);
		if( empty($config['page']) ) $config['page'] = 1;
		
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60 * 60 * 12;		// 缓存 12 小时
		$this->tpl->cache_dir .= 'sitemap\\';
		
		$tpl = 'active/sitemap/tuku/image_detail.html';
		
		if( ! $this->tpl->isCached($tpl, $config['page']) ){
		
			$this->load->model('photo/album_image_model');
			
			$sql = "select * from ( select id, addtime, num = row_number() over( order by id asc ) from photo_album_image ) as tmp where num between ". (($config['page'] - 1) * $config['size'] + 1) ." and ". ( $config['size'] * $config['page'] );
			
			$images = $this->album_image_model->get_list($sql);
			$images = $images['list'];
			if( count($images) == 0 ) exit('找不到数据');
			
			$this->tpl->assign('images', $images);
			$this->tpl->display($tpl, $config['page']);
			
		} else {
			$this->tpl->display($tpl, $config['page']);
			echo('<!-- cached -->');
		}
	}
	
	// 相册集合
	public function tuku_album($args = array()){
		
		header('Content-type:text/xml;charset=UTF-8');
		$config = array(
			'size'=>1000,
			'page'=>$args[0]
		);
		if( empty($config['page']) ) $config['page'] = 1;
		$tpl = 'active/sitemap/tuku/album.html';
		
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60 * 60 * 12;		// 缓存 12 小时
		$this->tpl->cache_dir .= 'sitemap\\';
		
		if( ! $this->tpl->isCached($tpl, $config['page']) ){
		
			$this->load->model('photo/Photo', 'album_model');
			$sql = "select * from ( select id, addtime, num = row_number() over( order by id asc ) from [Photo_album] ) as tmp where num between ". (( $config['page'] - 1 ) * $config['size'] + 1) ." and ". ( $config['size'] * $config['page'] );
			$albums = $this->album_model->get_albums($sql);
			if( count($albums) == 0 ) exit('找不到数据');
			$this->tpl->assign('albums', $albums);
			$this->tpl->display($tpl, $config['page']);
		
		} else {
			$this->tpl->display($tpl, $config['page']);
			echo('<!-- cached -->');
		}
		
		
	}
	
}


?>