<?php

// 资讯详细页面
class article_detail extends article_base {
	
	public $channel_id = 0;
	
	public function __construct(){
		parent::__construct();
		
		$this->load->model('article/article_tpl_model');
		
	}
	
	// 获取资讯详细内容缓存目录
	// 这里可能要转移到模型中，在后台修改文章时调用获取缓存路径，删除旧缓存文件
	private function get_cache_dictionary($id){
		
		return $this->article_tpl_model->get_cache_dictionary($id);
		
	}
	
	public function home($params = array()){
		
		if( count($params) != 1 ) show_404();
		if( ! preg_match('/^[1-9]\d*$/', $params[0]) ) show_404();
		$id = $params[0];
		
		// 将手机端访问的用户导入到手机端页面，提高访问质量
		// 判断是否手机端
		$this->load->model('mobile/mobile_checker');
		
		$this->load->model('mobile/mobile_url_model');
			
		// 获取手机端对应的网址
		$m_article = $this->mobile_url_model->format_single_article(
			array('id'=>$id)
		);

		//if( $this->mobile_checker->is_mobile() ){	// 判断是否手机端
			// 301 跳转到手机端页面 
			//$this->load->library('encode');
			//$this->encode->moved_permanently($m_article['mobile_link']);
			//header( 'Location:'. $m_article['mobile_link'] );
			//exit();
		//}
		
		// 缓存目录
		$cache_dir = $this->get_cache_dictionary($id);
		
		
		$tpl = $this->article_tpl_model->get_detail_tpl();
		$this->tpl->caching = $this->article_tpl_model->article_detail_cache;
		
		
		$this->tpl->cache_lifetime = 60 * 60 * 24 * 365; 	// 单位 分钟, 缓存365天
		$this->tpl->cache_dir = $cache_dir;

		

		$this->tpl->assign('m_article', $m_article);
		
		if( ! $this->tpl->isCached($tpl, $id) ){
			
			$this->load->model('article/category');
			$this->load->model('article/article');
			$this->load->library('thumb');
			
			$article = $this->article->getArt($id);
			if( ! $article ) show_error('文章不存在', 404);
			
			$keywords = $article['keyword'];		// 供seo使用
			
			$article['keyword'] = $this->article->add_keywords_link($article['keyword']);
			$article['content'] = $this->remove_outside_link($article['content'], array('shzh.net', 'snzsxh.org.cn', 'dszh.com'));
			
			$article['content'] = $this->format_content($article['content']);
			
			
			
			// 分类相关数据
			$root = $this->category->getRoot($article['clsid']);
			$this->channel_id = $root['id'];
			$roots = $this->category->getChannels();
			$childs = $this->category->getChilds($this->channel_id);
			$cate_status = array();
			$this->category->getCatStatus($article['clsid'], $cate_status);	//分类ID的结构
			
			// 上，下一页
			$prev = $this->article->getPrev($article['id']);
			$next = $this->article->getNext($article['id']);
			
			// 最新资讯
			$latest = $this->article->getLatestNews($article['clsid'], 5, array($article['id']));
			
			// 图库部分内容
			$this->load->model('photo/Photo', 'photo');
			$this->load->model('photo/PhotoCategory','photoCategory');
			$album_home_cats = $this->photoCategory->getHotCategory(5, 'J');
			$album_office_cats = $this->photoCategory->getHotCategory(5, 'G');
			$albums = $this->photo->latestAlbum(3);
			for($i=0; $i < count($albums); $i++){
				$albums[$i]['thumb_image'] = $this->thumb->crop($albums[$i]['fm'], 40, 40);	// 生成缩略图
				$temp = explode(' ', $albums[$i]['name']);
				$albums[$i]['short_name'] = $temp[0];
			}
			
			// 推荐装修公司
			$this->load->model('company/company', 'deco_model');
			
			$decos = $this->deco_model->getKouBeiList(array(
				'top'=>3,
				'fields'=>'company, username, koubei, shortname, logo'
			));
			
			// 显示作者昵称 2015-10-26
			$this->load->model('manager/manager_model');
			$admin = $this->manager_model->get_manager($article['admin'], 'username, nick');
			
			$this->tpl->assign('channel_id', $this->channel_id);
			$this->tpl->assign('article', $article);
			$this->tpl->assign('roots', $roots);
			$this->tpl->assign('childs', $childs);
			$this->tpl->assign('cate_status', $cate_status);
			
			$this->tpl->assign('prev', $prev);
			$this->tpl->assign('next', $next);
			
			$this->tpl->assign('latest', $latest);
			
			$this->tpl->assign('album_home_cats', $album_home_cats);
			$this->tpl->assign('album_office_cats', $album_office_cats);
			
			//echo('<!--');
			//var_dump($album_home_cats);
			//echo('-->');
			
			$this->tpl->assign('albums', $albums);
			
			$this->tpl->assign('decos', $decos);
			
			// 作者信息
			$this->tpl->assign('admin', $admin);
			
			// seo 
			$this->tpl->assign('title', $article['title']);
			$this->tpl->assign('keywords', implode(',', $keywords));
			$this->tpl->assign('description', $article['description']);
			$this->tpl->display($tpl, $id);
		} else {
			$this->tpl->display($tpl, $id);
			echo('<!--from the cache-->');
		}
	}
	
	// 移除正文的外部链接
	private function remove_outside_link($content, $allow_urls = array()){
		return $this->encode->remove_outside_link($content, $allow_urls);
	}
	
	// 格式化正文标签
	private function format_content($content){
		
		$content = str_replace('　', '&nbsp;', $content);
		$content = preg_replace('/(?<=\>)\s*&nbsp;(\s*&nbsp;)*/', '', $content);
		
		return $content;
	}

	// 文章浏览次数统计
	public function counter(){
		
		$art_id = $this->gf('id');
		
		if( ! preg_match('/^\d+$/', $art_id) ){
			exit( json_encode( array(
				'type'=>'error',
				'message'=>'参数非法'
			) ) );
		}
		
		if( isset( $_COOKIE['art_viewed'] ) ){
			$viewed_ids = explode(',', $_COOKIE['art_viewed']);
		} else {
			$viewed_ids = array();
		}
		
		$exists = false;
		
		foreach( $viewed_ids as $item ){
			if( $item == $art_id ){
				$exists = true;
				break;
			}
		}
		
		$result = array();
		
		if( $exists == true ){
			$result['type'] = 'error';
			$result['message'] = '已浏览';
		} else {
			
			$this->load->model('article/Article', 'article_model');
			$art = $this->article_model->getArt($art_id, 'id, showcount, base_showcount', array('format'=>false));
			
			if( ! $art ) {
				$result['type'] = 'error';
				$result['message'] = '找不到文章';
			} else {
				
				$this->load->model('article/article_active_model');
				$this->article_active_model->add_show_count($art_id);
				
				$viewed_ids[] = $art_id;
				
				// 两个小时的期限
				setcookie('art_viewed', implode(',', $viewed_ids), time() + 3600*2, '/');
				
				$result['type'] = 'success';
				$result['view_count'] = $art['showcount'] + $art['base_showcount'] + 1;
				
			}
		}
		
		echo( json_encode( $result ) );
		
	}








}


















?>