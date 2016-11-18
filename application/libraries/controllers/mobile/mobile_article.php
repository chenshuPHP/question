<?php
class mobile_article extends mobile_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function index(){
		
		$tpl = $this->get_tpl('article/home.html');
		$this->tpl->caching = true;
		$this->tpl->cache_dir = $this->get_cache_dir('article/home');
		$this->cache_lifetime = 60 * 60 * 2;
		
		if( ! $this->tpl->isCached($tpl) ){
			
			$this->load->library('thumb');
			$this->load->model('mobile/mobile_url_model');
			$this->load->model('article/Article', 'article_model');
			$this->load->model('article/category', 'article_category_model');
			
			
			// 6, 1, 55, 97
			$arts = array(
				array('id'=>6, 'name'=>'装修设计', 'class'=>'title_zxsj', 'arts'=>NULL),
				array('id'=>1, 'name'=>'施工验收', 'class'=>'title_sgys', 'arts'=>NULL),
				array('id'=>55, 'name'=>'风水常识', 'class'=>'title_fscs', 'arts'=>NULL),
				array('id'=>97, 'name'=>'选材常识', 'class'=>'title_xccs', 'arts'=>NULL)
			);
			
			$arts = $this->mobile_url_model->format_article_category($arts);
			
			foreach($arts as $key=>$val){
				
				// 为分类附加文章
				$sql = "select top 3 id, clsid, imgpath as path, title, description from art_art where cid like ',". $val['id'] .",%' and imgpath <> '' order by addtime desc";
				$res = $this->article_model->getCustomNews($sql, false);
				$res = $this->mobile_url_model->format_batch_article($res);
				foreach($res as $k=>$v){
					$res[$k]['thumb'] = $this->thumb->crop($v['path'], 140, 140);
				}
				$val['arts'] = $res;
				
				// 为分类附加子分类
				$childs = $this->article_category_model->getChilds($val['id'], 2);
				$childs = $this->mobile_url_model->format_article_category($childs);
				$val['childs'] = $childs;
				
				$arts[$key] = $val;
				
			}
			
			// 体验馆 模版中 静态内容
		
			
			$this->tpl->assign('title', '装修学堂');
			$this->tpl->assign('arts', $arts);
			$this->tpl->display($tpl);
			
		} else {
			$this->tpl->display($tpl);
			echo('<!-- cached -->');
		}
		
	}
	
	// 获取缓存目录
	private function get_cache_directory($id){
		$dir = $this->tpl->cache_dir;
		$dir = rtrim(strtolower( str_replace('/', '\\', $dir) ), '\\') . '\\';
		$temp = ceil($id / 10000);
		$dir .= 'mobile\\article\\detail\\' . $temp . '\\';
		return $dir;
	}
	
	public function view(){

		$id = $this->gr('id');
		$r = $this->gr('r');
		
		$cache_dir = $this->get_cache_directory($id);
		
		//if( $this->gr('v') == 2016 ){
		$this->tpl->caching = true;
		$tpl = $this->get_tpl('article/view.html');
		//} else {
		//	$tpl = 'mobile/article/view.html';
		//	$this->tpl->caching = true;
		//}
		
		$this->tpl->cache_lifetime = 60 * 60 * 2;	// 缓存 2 小时
		$this->tpl->cache_dir = $cache_dir;
		
		$this->tpl->assign('r', $r);
		
		if( ! $this->tpl->isCached($tpl, $id) ){
			
			$this->load->model('article/Article', 'article_model');
			$this->load->model('article/Category', 'article_category_model');
			$this->load->model('mobile/mobile_url_model');
			
			$img_url = $this->config->item('upload_image_options');
			$urls = $this->config->item('url');
			
			$article = $this->article_model->getArt($id);
			$article['content'] = str_replace('&nbsp;', '', $article['content']);
			$article['content'] = str_replace('src="/upimg/', 'src="' . $img_url[0]['url'], $article['content']);
			$article['content'] = str_replace('src="/eWebEditor/', 'src="'. $urls['www'] .'/eWebEditor/', $article['content']);
			
			// cid
			$cate = $this->article_category_model->getCurrCat($article['clsid']);
			
			// 相关文章
			$arts = $this->article_model->get_cate_topics($cate['id'], 5);
			
			$prevs = $this->article_model->getPrev($article['id'], array('number'=>3));
			$prevs = $prevs == false ? array() : $prevs;
			$nexts = $this->article_model->getNext($article['id'], array('number'=>3));
			$nexts = $nexts == false ? array() : $nexts;
			$abouts = array_merge($prevs, $nexts);
			$abouts = $this->mobile_url_model->format_batch_article($abouts);
			
			$this->tpl->assign('page_name', $cate['name']);
			$this->tpl->assign('abouts', $abouts);
			$this->tpl->assign('article', $article);
			$this->tpl->display( $tpl, $id );
		} else {
			$this->tpl->display( $tpl, $id );
			echo('<!-- cached -->');
		}
		
	}


	// 文章列表
	public function collect(){
		
		$id = $this->gr('id');
		
		if( empty($id) ){
			show_error('参数错误', 404);
			exit();
		}
		
		$args = array(
			'page'=>$this->gr('page'),
			'size'=>20
		);
		
		$tpl = $this->get_tpl('article/list.html');
		
		// 缓存新闻列表页面
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60 * 60 * 1;	// 缓存一个小时 更新一次
		$this->tpl->cache_dir = $this->get_cache_dir('article/collect/' . $id);
		$cache_id = $args['page'];
		
		if( ! $this->tpl->isCached($tpl, $cache_id) ){
			
		$this->load->model('article/category', 'article_category_model');
		$this->load->model('article/Article', 'article_model');
		$this->load->model('mobile/mobile_url_model');
		$this->load->library('thumb');
		
		$cats = $this->article_category_model->getRoot($id);
		if( ! $cats ) {
			show_error('找不到分类', 404);
			exit();
		}
		$cats['childs'] = $this->article_category_model->getChilds($cats['id']);
		$cats['childs'] = $this->article_category_model->assign_childs($cats['childs']);
		$cats['childs'] = $this->mobile_url_model->format_article_category($cats['childs']);
		
		$current = $this->article_category_model->getCurrCat($id);
		$status = array();
		$this->article_category_model->getCatStatus($id, $status);
		
		$cats = $this->format_cate_tree($cats, $status);
		
		// 读取文章
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>10
		);
		
		if( ! $this->article_category_model->getChilds($id) ){
			$where = "clsid = '". $id ."'";
		} else {
			if( $current['pid'] == 0 ){
				$id = $cats['childs'][0]['id'];
			}
			$where = "cid like '%,". $id .",%'";
		}
		
		$sql = "select * from ( select id, title, description, imgpath as path, clsid, base_showcount, showcount, num = row_number() over(order by addtime desc) from art_art where ". $where ." ) as temp where num between ". (($args['page'] - 1) * $args['size'] + 1) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from art_art where " . $where;
		$result = $this->article_model->get_list($sql, $sql_count);
		$result['list'] = $this->mobile_url_model->format_batch_article($result['list']);
		
		foreach($result['list'] as $key=>$val){
			$val['thumb'] = '';
			if( ! empty( $val['path'] ) ){
				$val['thumb'] = $this->thumb->crop($val['path'], 140, 140);
			}
			$result['list'][$key] = $val;
		}
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->url_template = $this->get_mobile_url('/article/collect?id=' . $id . '&page=<{page}>');
		$this->pagination->url_template_first = $this->get_mobile_url('/article/collect?id=' . $id);
		$pagination = $this->pagination->tostring_simple(true);
		
		if( $args['page'] > $this->pagination->get_page_count() ){
			show_error('page 超出范围', 404);
			exit();
		}
		
		
		$this->tpl->assign('cats', $cats);
		$this->tpl->assign('status', $status);
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('args', $args);
		$this->tpl->assign('pagination', $pagination);
		
		$title = array();
		foreach($status as $item){
			$title[] = $item['name'];
		}
		$this->tpl->assign('title', implode('-', $title));
		
		$this->tpl->display($tpl, $cache_id);
		
		} else {
			
			$this->tpl->display($tpl, $cache_id);
			echo('<!-- cached -->');
			
		}
		
	}
	
	// 为分类附加选中状态
	private function format_cate_tree($tree, $status){
		
		$ids = array();
		foreach($status as $item){
			$ids[] = $item['id'];
		}
		
		$exists = false;
		foreach($tree['childs'] as $key=>$value){
			
			if( $value['childs'] == false ){
				$value['childs'] = array($value);
			}
			
			foreach($value['childs'] as $k=>$v){
				$v['current'] = false;
				if( in_array($v['id'], $ids) ){
					$v['current'] = true;
				}
				$value['childs'][$k] = $v;
			}
			
			$value['current'] = false;
			if( in_array($value['id'], $ids) ){
				$value['current'] = true;
				$exists = true;
			}
			
			$tree['childs'][$key] = $value;
		}
		if( $exists == false ) $tree['childs'][0]['current'] = true;
		
		return $tree;
	}










}






?>