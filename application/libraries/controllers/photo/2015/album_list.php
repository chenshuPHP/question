<?php

class album_list extends album_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home($args = array()){
		
		$config = array(
			'size'=>40,
			'page'=>(isset($args[1])) ? $args[1] : 1
		);
		
		$id = '';
		$cat = NULL;
		$parent = NULL;
		
		if( isset( $args[0] ) ){
			$id = $args[0];
		} else {
			show_404();
			exit();
		}
		
		if($config['page'] == 1){
			$url = 'http://tuku.shzh.net/albums?id=' . $id;
		} else {
			$url = 'http://tuku.shzh.net/albums?id=' . $id . '&page=' . $config['page'];
		}
		
		header("HTTP/1.1 301 Moved Permanently");									//这个是说明返回的是301
		header("Location:" . $url);					//这个是重定向后的网址
		exit();
		
		
		// 启用缓存
		$tpl = $this->get_tpl( 'album_list.html' );
		$cache_dir = $this->get_cache_dictionary(array(
			'id'=>$id,
			'page'=>$config['page']
		));
		
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60 * 60 * 2.5; 	// 单位 分钟, 缓存2.5小时
		$this->tpl->cache_dir = $cache_dir;
		
		$cache_id = $config['page'];
		
		if( ! $this->tpl->isCached($tpl, $cache_id) ){
			
		$this->load->model('photo/album_category_model');
		$this->load->model('photo/album_image_model');
		$this->load->library('thumb');
		
		$cat = $this->album_category_model->get_cat($id, array(
			'fields'=>'id, name, pid',
			'format'=>false
		));
		$parent = $this->album_category_model->get_cat($cat['pid'], array(
			'fields'=>'id, name, pid, target, isgong as tp',
			'format'=>false
		));
		if( ! $parent || ! $cat ){
			show_404();
			exit();
		}
		
		$tp = $parent['tp'];	// 家装分类
		$where = 'isgong=' . $tp;
		
		$sql = "select id, name, pid, target from [photo_category] where " . $where . " and pid = 0";
		$cats = $this->album_category_model->get_list($sql);
		
		$cats = $this->_root_category_sort($cats['list']);
		
		/*
		if( $tp == 0 ){
			$cats = $this->_root_category_sort($cats['list']);
		} else {
			$cats = $cats['list'];
		}
		*/
		$cats = $this->album_category_model->assign_childs($cats, array(
			'fields'=>'id, pid, name'
		));
		
		$this->tpl->assign('cats', $cats);
		
		
		$result = NULL;
		
		if( $parent['target'] == 'album' ){
			$this->load->model('photo/Photo', 'album_model');
			
			$sql = "select * from ( select id, name, fm_image as fm, num = row_number() over(order by addtime desc) from [photo_album] where id in (select album_id from [photo_album_category_relation] where cid = '". $cat['id'] ."') and fm_image <> '' ) as temp where num between ". ( $config['size'] * ($config['page'] - 1) ) ." and " . ( $config['size'] * $config['page'] );
			$sql_count = "select count(*) as icount from [photo_album] where id in ( select album_id from [photo_album_category_relation] where cid = '". $cat['id'] ."') and fm_image <> ''";
			
			$result = $this->album_model->get_albums($sql, $sql_count, array(
				'cat'=>$cat
			)); 
			$result['list'] = $this->album_image_model->count_assign($result['list']);
			
			
		} elseif ( $parent['target'] == 'image' ) {
			
			$sql = "select * from (select id, name, imagepath as fm, num = row_number() over(order by addtime desc) from [photo_album_image] where id in (select image_id from [photo_image_category_relation] where cid = '". $cat['id'] ."') ) as temp where num between ". ( $config['size'] * ($config['page'] - 1) ) ." and " . ( $config['size'] * $config['page'] );
			$sql_count = "select count(*) as icount from [photo_album_image] where id in (select image_id from [photo_image_category_relation] where cid = '". $cat['id'] ."')";
			$result = $this->album_image_model->get_list($sql, $sql_count, array(
				'cat'=>$cat
			));
			
		}
		
		//if( $this->gr('debug') == '1' ){
			foreach($result['list'] as $key=>$value){
				$value['link'] = $value['link2015'];
				$result['list'][$key] = $value;
			}
		//}
			
		//echo('<!--');
		//var_dump($result['list']);
		//echo('-->');
		
		
		$urls = $this->config->item('url');
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $config['page'];
		$this->pagination->pageSize = $config['size'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->url_template = $urls['tuku'] . 'list-' . $id . '-<{page}>';
		$this->pagination->url_template_first = $urls['tuku'] . 'list-' . $id;
		$pagination = $this->pagination->toString(true);
		
		$tps = array(
			array('name'=>'家装'),
			array('name'=>'公装')
		);
		
		foreach($result['list'] as $key=>$value){
			$value['thumb'] = $this->thumb->resize($value['fm'], 285, 0, array('mode'=>'target'));
			$result['list'][$key] = $value;
		}
		
		//foreach($i = 0; $i < 1; $i++){
		//	$result['list'][$i]['thumb'] = $this->thumb->resize($result['list'][$i]['fm'], 285, 0, array('mode'=>'target'));
		//}
		//echo('<!--');
		//var_dump($result['list']);
		//echo('-->');
		
		$this->tpl->assign('target', $parent['target']);
		$this->tpl->assign('tp', $tp);
		$this->tpl->assign('tps', $tps);
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('pagination', $pagination);
		
		$title = $cat['name'] . '装修效果图大全2016,上海装潢网'. $cat['name'] .'装修图片欣赏';
		$keywords = $cat['name'] . '装修效果图,'. $cat['name'] .'图片,'. $cat['name'] .'设计';
		$description = '上海装潢网'. $cat['name'] .'装修效果图专区,提供2016年国内外最新流行的'. $cat['name'] .'装修效果图和'. $cat['name'] .'装修效果图欣赏案例,包括'. $cat['name'] .'居家装修装饰以及搭配案例，'. $cat['name'] .'效果图,'. $cat['name'] .'效果图,'. $cat['name'] .'效果图,'. $cat['name'] .'装修图片';
		
		// seo
		$this->tpl->assign('title', $title);
		$this->tpl->assign('cat_name', $cat['name']);
		$this->tpl->assign('keywords', $keywords);
		$this->tpl->assign('description', $description);
		
		$this->tpl->display($tpl, $cache_id);
		
		} else {
			$this->tpl->display($tpl, $cache_id);
			echo('<!-- cached -->');
		}
		
	}
	
	private function _root_category_sort($cats){
		$custom_cats = array(
			array('id'=>61, 'alias'=>'户型'),
			array('id'=>19, 'alias'=>'空间'),
			array('id'=>39, 'alias'=>'局部'),
			array('id'=>1, 'alias'=>'风格'),
			array('id'=>2, 'alias'=>'颜色'),
			array('id'=>70, 'alias'=>'属性'),
			
			array('id'=>83, 'alias'=>'办公'),
			array('id'=>75, 'alias'=>'商业'),
			array('id'=>89, 'alias'=>'公共')
			
		);
		$result = array();
		foreach($custom_cats as $item){
			foreach($cats as $cat){
				if($item['id'] == $cat['id']){
					$cat['alias'] = $item['alias'];
					$result[] = $cat;
					break;
				}
			}
		}
		return $result;
	}
	
	private function get_cache_dictionary($args = array()){
		$dir = $this->tpl->cache_dir;
		$dir = rtrim(strtolower( str_replace('/', '\\', $dir) ), '\\') . '\\';
		$dir .= 'photo\\list_2016\\'. $args['id'] .'\\';
		return $dir;
	}



}

?>