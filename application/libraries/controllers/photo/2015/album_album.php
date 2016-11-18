<?php

// 20156-01-08
// kko4455@163.com
// 相册详细展示

class album_album extends album_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 缓存目录
	private function get_cache_dictionary($id){
		$dir = $this->tpl->cache_dir;
		$dir = rtrim(strtolower( str_replace('/', '\\', $dir) ), '\\') . '\\';
		$temp = ceil($id / 1000);
		$dir .= 'photo\\album\\' . $temp . '\\';
		return $dir;
	}
	
	public function home($args = array()){
		
		$info = array(
			'aid'=>$args[0],
			'image_id'=>$this->gr('img')
		);
		
		// 缓存机制
		$tpl = $this->get_tpl('album_album.html');
		
		if( ! $this->gr('debug') == 'true' ) $this->tpl->caching = true;
		
		$this->tpl->cache_lifetime = 60 * 60 * 10; 											// 单位 分钟, 缓存10小时
		$this->tpl->cache_dir = $this->get_cache_dictionary($info['aid']);					// 缓存目录
		
		// $cache_id = $info['aid'] . '_' . $info['cid'];
		$cache_id = $info['aid'];
		if( $info['image_id'] != '' ){
			$cache_id = $info['aid'] . '_' . $info['image_id'];		// 缓存ID
		}
		
		if( ! $this->tpl->isCached($tpl, $cache_id) ){
		
		
		$this->load->library('thumb');
		$this->load->model('photo/album_category_model');
		$this->load->model('photo/Photo', 'album_model');
		$this->load->model('photo/album_image_model');
		
		// 读取相册基本信息
		$album = $this->album_model->getAlbum($info['aid'], array(
			'fields'=>'id, name, description, fm_image as fm, tp'
		));
		
		$tp = $album['tp'] == 'home' ? 0 : 1;			// 根据相册tp属性判断页面是 家装 OR 工装
		$tp_name = $tp == 0 ? '家装图库' : '公装图库';
		
		// 将图片附加给相册
		/*
			这里需要考虑相册内部图片过多时的问题
			如果图片过多, 只能附加部分图片给相册
			还要考虑到用户指定了图片ID给相册, 那么只能附加指定ID附近的图片给相册
		*/
		$album = $this->album_image_model->image_assign($album, array('format'=>true));
		
		foreach($album['images'] as $key=>$value){
			$album['images'][$key]['thumb'] = $this->thumb->crop($value['path'], 100, 100);
		}
		
		// 为相册附加分类数据
		$album = $this->album_category_model->category_assign_albums($album, array(
			'gong'=>$tp,
			'fields'=>'id, name, pid'
		));
		
		// 这一步需要调整, 不能默认指定成第一个分类
		// 这里修正为默认一个户型 PID = 61 + 一个风格 PID = 1
		$curr = array();
		$curr_exists = array();
		
		foreach($album['cats'] as $item){
			if( count($curr) == 2 ) break;
			if( in_array($item['pid'], array(1, 61)) && in_array($item['pid'], $curr_exists) == false ) {
				$curr[] = $item['id'];
				$curr_exists[] = $item['pid'];
			}
		}
		
		$image = false;		// 保存当前点选的图片, 默认为封面
		// 为图册子图片集指定一个current属性, 判定当前所展示的图片
		// 如果没找到, 那么image_id参数有误 返回 404
		foreach($album['images'] as $key=>$value){
			if( $info['image_id'] == $value['id'] ){
				$value['current'] = true;
				$image = $value;
				$album['images'][$key] = $value;
				break;
			} else {
				if( $value['path'] == $album['fm'] ){
					$value['current'] = true;
					$image = $value;
					$album['images'][$key] = $value;
					break;
				}
			}
			
		}
		if( $info['image_id'] != '' && $image['id'] != $info['image_id'] ) {
			show_error('图片参数错误', 404);
			exit();
		}
		$this->tpl->assign('image', $image);
		
		$cats = $this->album_category_model->get_list("select id, name from [photo_category] where pid = 0 and [disabled] = 0 and isGong = '". $tp ."' order by target asc");
		$cats = $this->album_category_model->assign_childs($cats['list'], array(
			'size'=>12,
			'fields'=>'id, pid, name'
		));
		
		$this->tpl->assign('cats', $cats);
		$this->tpl->assign('curr', $curr);
		$this->tpl->assign('tp_name', $tp_name);
		$this->tpl->assign('album', $album);
		
		// 最新相册
		$albums = $this->album_model->get_albums("select top 5 id, name, fm_image as fm from [photo_album] where fm_image <> '' order by addtime desc");
		$this->tpl->assign('albums', $albums);
		
		// 最新文章
		$this->load->model('article/Article', 'article_model');
		$articles = $this->article_model->get_list("select top 5 id, title, short_title, clsid from [art_art] order by addtime desc");
		$articles = $articles['list'];
		$this->tpl->assign('articles', $articles);
		
		// 监理日记
		$this->load->model('diary/diary_model');
		$diaries = $this->diary_model->get_list("select top 5 id, title, deco_name from [diary] order by id desc");
		$this->tpl->assign('diaries', $diaries['list']);
		
		// 热门分类
		$hots = $this->album_category_model->get_list("select top 10 id, name from [photo_category] where hot = 1");
		$this->tpl->assign('hots', $hots['list']);
		
		// seo
		$this->tpl->assign('title', $album['name']);
		
		// 2016-01-18
		// 上几篇, 下几篇
		$prev = $this->load(array(
			'aid'=>$album['id'],
			'cid'=>$curr,
			't'=>'prev',
			'size'=>5,
			'echo'=>false
		));
		
		$next = $this->load(array(
			'aid'=>$album['id'],
			'cid'=>$curr,
			't'=>'next',
			'size'=>5,
			'echo'=>false
		));
		
		$this->tpl->assign('prevs', $prev);
		$this->tpl->assign('nexts', $next);
		
		$this->tpl->display( $tpl, $cache_id );
		
		
		} else {
			$this->tpl->display( $tpl, $cache_id );
			echo('<!-- cached -->');
		}
	}
	
	public function load($settings = array()){
		
		$config = array(
			'aid'=>$this->gr('id'),
			'cid'=>$this->gr('cid'),
			't'=>$this->gr('t'),
			'size'=>10,
			'echo'=>true
		);
		
		$config = array_merge($config, $settings);
		
		$aid = $config['aid'];
		$cid = $config['cid'];
		$t = $config['t'];
		
		$this->load->library('thumb');
		$this->load->model('photo/Photo', 'album_model');
		$this->load->model('photo/album_image_model');
		
		if( ! is_array($cid) ){
			if( preg_match('/^\d+$/', $cid) ) $cid = array($cid);
			if( preg_match('/^\d+(,\d+)+$/', $cid) ) $cid = explode(',', $cid);
		}
		
		$where = "fm_image <> ''";
		foreach($cid as $item){
			$where .= " and id in ( select album_id from [photo_album_category_relation] where cid = '". $item ."' )";
		}
		
		if( $t == 'prev' ){
			$sql = "select top ". $config['size'] ." id, name, fm_image as path from [photo_album] where ". $where ." and id > $aid order by id desc";
		} else {
			$sql = "select top ". $config['size'] ." id, name, fm_image as path from [photo_album] where ". $where ." and id < $aid order by id desc";
		}
		$albums = $this->album_model->get_albums($sql, '', array(
			'format'=>true
			,'cat'=>array('id'=>$cid)
		));
		
		$albums = $this->album_image_model->count_assign($albums);
		
		$cfg = $this->config->item('upload_image_options');
		$url = $cfg[0]['url'];
		
		if( ! $albums ) $albums = array();
		
		foreach($albums as $key=>$value){
			$value['pic'] = $url . ltrim($value['path'], '/');
			$value['thumb'] = $this->thumb->crop($value['path'], 100, 100);
			$albums[$key] = $value;
			
		}
		
		if( $config['echo'] == true ){
			echo( json_encode( $albums ) );
		} else {
			return $albums;
		}
		
	}
	
	
}


?>