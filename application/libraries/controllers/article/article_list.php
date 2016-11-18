<?php

class article_list extends article_base {
	
	public $channel_id = 0;
	
	public function __construct(){
		parent::__construct();
	}

	public function home( $option = array() ){
		
		$this->load->library('encode');
		
		$args = array(
			'cat'=>$this->encode->get_request_encode('cat'),
			'page'=>$this->encode->get_request_encode('p'),
			'size'=>20
		);
		
		if( isset( $option[0] ) ) $args['cat'] = $option[0];
		if( isset( $option[1] ) ) $args['page'] = $option[1];
		
		if( ! preg_match('/^[1-9]\d*$/', $args['cat']) ) show_error('分类错误', 404);
		
		if( empty($args['page']) ){
			$args['page'] = 1;
		} else {
			if( ! preg_match('/^[1-9]\d*$/', $args['page']) ) show_error('分页参数错误', 404);
		}
		
		// 做缓存
		// 2016-03-21
		$tpl = 'article/list.html';
		$cache_dir = $this->get_cache_dictionary($args);
		
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60 * 60 * 2.5; 	// 单位 分钟, 缓存2.5小时
		
		$this->tpl->cache_dir = $cache_dir;
		
		$cache_id = $args['page'];
		
		if( ! $this->tpl->isCached($tpl, $cache_id) ){	// 缓存
		
		$this->load->model('article/category');
		$this->load->model('article/article');
		$this->load->library('thumb');
		$this->load->library('pagination');
		
		// 分类相关数据
		$root = $this->category->getRoot($args['cat']);
		$this->channel_id = $root['id'];
		$roots = $this->category->getChannels();
		$childs = $this->category->getChilds($this->channel_id);
		$cate_status = array();
		$this->category->getCatStatus($args['cat'], $cate_status);	// 分类ID的结构
		$cat = $cate_status[count($cate_status)-1];
		$cat_childs = $this->category->getChilds($cat['id']);

		// 文章列表
		$cats_id_arr = array($cat['id']);
		if( $cat_childs != false && count($cat_childs) > 0 ){
			
			//echo('<!--');
			//var_dump($cat_childs);
			//echo('-->');
			
			foreach($cat_childs as $item){
				array_push($cats_id_arr, $item['id']);
			}
			$this->tpl->assign('cat_childs', $cat_childs);
		} else {
			$this->tpl->assign('cat_childs', false);
		}
		if( count($cat_childs) == 0 ) $cat_childs = false;
		$where = "clsid in (". implode(',', $cats_id_arr) .")";
		
		$sql = "select * from ( select id, title, keyword, description, scontent as content, clsid, imgpath as img, addtime, base_showcount, showcount, row_number() over( order by id desc ) as num from art_art where ". $where ." ) as temp where num between ". (($args['page']-1) * $args['size'] + 1) ." and " . ($args['page'] * $args['size']);
		$sql_count = "select count(*) as icount from art_art where " . $where;
		
		$result = $this->article->get_list($sql, $sql_count);
		
		foreach($result['list'] as $key=>$item){
			unset($item['content']);
			$item['keyword'] = $this->article->add_keywords_link($item['keyword']);
			$item['thumb'] = $this->thumb->crop($item['img'], 140, 140);
			$result['list'][$key] = $item;
		}
		
		// 最新资讯
		$latest = $this->article->getShowCountNews($cat['id'], 6, array());
		
		// 图库部分内容
		$this->load->model('photo/Photo', 'photo');
		$this->load->model('photo/PhotoCategory','photoCategory');
		$album_home_cats = $this->photoCategory->getHotCategory(0, 'J');
		$album_office_cats = $this->photoCategory->getHotCategory(0, 'G');
		$albums = $this->photo->latestAlbum(4, 'id, name, fm_image as fm, image_count as icount, description');
		for($i=0; $i < count($albums); $i++){
			$albums[$i]['thumb_image'] = $this->thumb->crop($albums[$i]['fm'], 40, 40);	// 生成缩略图
			$temp = explode(' ', $albums[$i]['name']);
			$albums[$i]['short_name'] = $temp[0];
		}
		
		$this->tpl->assign('list', $result['list']);
		
		
		$this->pagination->currentPage = $args['page'];
		$this->pagination->pageCount = $this->pagination->getPageCount($args['size'], $result['count']);
		
		//$this->pagination->url_template_first = $this->config->item('base_url') . '/article/list.html?cat=' . $args['cat'];
		//$this->pagination->url_template = $this->config->item('base_url') . '/article/list.html?cat=' . $args['cat'] . '&p=<{page}>';
		
		$this->pagination->url_template_first = $this->config->item('base_url') . '/article/list-'. $args['cat'] .'.html';
		$this->pagination->url_template = $this->config->item('base_url') . '/article/list-'. $args['cat'] .'-<{page}>.html';
		
		
		$pagination = $this->pagination->toString(true);
		
		
		$this->tpl->assign('channel_id', $this->channel_id);
		$this->tpl->assign('roots', $roots);
		$this->tpl->assign('childs', $childs);
		$this->tpl->assign('cate_status', $cate_status);
		
		$this->tpl->assign('latest', $latest);
		
		$this->tpl->assign('album_home_cats', $album_home_cats);
		$this->tpl->assign('album_office_cats', $album_office_cats);
		$this->tpl->assign('albums', $albums);
		
		// seo 
		$this->tpl->assign('title', $cat['name']);
		$this->tpl->assign('keywords', $cat['name']);
		$this->tpl->assign('description', $cat['name']);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->display($tpl, $cache_id);
		
		} else {
			$this->tpl->display($tpl, $cache_id);
			echo('<!-- cached -->');
		}
		
		
	}
	
	private function get_cache_dictionary($args = array()){
		$dir = $this->tpl->cache_dir;
		$dir = rtrim(strtolower( str_replace('/', '\\', $dir) ), '\\') . '\\';
		$dir .= 'article\\list_2016\\'. $args['cat'] .'\\';
		return $dir;
	}
	
	
}

?>