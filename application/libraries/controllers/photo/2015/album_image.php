<?php

// 图库详细显示页面
class album_image extends album_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 缓存目录
	private function get_cache_dictionary($id){
		$dir = $this->tpl->cache_dir;
		$dir = rtrim(strtolower( str_replace('/', '\\', $dir) ), '\\') . '\\';
		$temp = ceil($id / 3000);
		$dir .= 'photo\\image\\' . $temp . '\\';
		return $dir;
	}
	
	// 图片详细展示
	public function home($args = array()){
		
		$image_id = $args[0];
		
		// 记录上一次的浏览分类记录, 读取对应分类的数据, 使得上下页总是在当前分类
		$category_id = '';
		$ext = '';
		
		// 如果是从其他来源 则忽略cookie
		$refresh = '';
		if( isset( $_SERVER['HTTP_REFERER'] ) )
		{
			$refresh = strtolower( $_SERVER['HTTP_REFERER'] );
		}
		
		if( preg_match('/^http:\/\/tuku\.shzh\.net\/imagelist.*$/', $refresh) === TRUE OR preg_match('/^http:\/\/tuku\.shzh\.net\/image\-.*$/', $refresh) === TRUE )
		{
			if( isset($_COOKIE['user_select_image_category_id']) && ! empty( $_COOKIE['user_select_image_category_id'] ) )
			{
				$tmp = $_COOKIE['user_select_image_category_id'];
				$tmp = explode('&', $tmp);
				foreach($tmp as $t)
				{
					if( strpos($t, 'cid') !== false )
					{
						$category_id = explode('=', $t);
						$category_id = $category_id[1];
					}
					if( strpos($t, 'ext') !== false )
					{
						$ext = explode('=', $t);
						$ext = $ext[1];
					}
				}
			}
		}
		
		
		$cacahe_id = array();	// 设置缓存ID
		if( empty( $image_id ) )  $image_id = $this->gr('id');
		
		if( ! empty( $image_id ) )
		{
			$cacahe_id[] = $image_id;
		}
		if( ! empty($category_id) )
		{
			$cacahe_id[] = $category_id;
		}
		if( ! empty( $ext ) )
		{
			$cacahe_id[] = $ext;
		}
		
		$cacahe_id = implode('&', $cacahe_id);
		
		$tpl = $this->get_tpl( 'image_view.html' );
		
		$this->tpl->caching = TRUE;
		$this->tpl->cache_lifetime = 60 * 60 * 5;	// 缓存时间 5 小时
		$this->tpl->cache_dir = $this->get_cache_dictionary( $image_id );	// 设置缓存目录
		
		if( ! $this->tpl->isCached($tpl, $cacahe_id) ) {
			
		
		$this->load->model('photo/album_image_model');
		$this->load->model('photo/image_label_model');
		$this->load->model('photo/album_category_model');
		$this->load->library('thumb');
		
		$image = $this->album_image_model->get_image($image_id, array(
			'fields'=>'id, name, imagePath as path, description, albumid, addtime, admin, cid, assist_base, assist, showcount_base, showcount'
		));
		
		
		$cat = $this->album_category_model->get_cat( empty( $category_id ) ? $image['cid'] : $category_id );
		
		$this->image_label_model->assign($image);
		
		// 用户从列表页面过来后又从首页过来
		if( $category_id != $image['cid'] )
		{
			$category_id = $image['cid'];
		}
		
		$prev = $this->album_image_model->previous( $image_id, array(
			'category_id'=>$category_id,
			'ext'=>$ext
		) );
		$next = $this->album_image_model->next( $image_id, array(
			'category_id'=>$category_id,
			'ext'=>$ext
		) );
		
		// 相关图片 相同分类下的图片
		$abouts = array();
		$tmp = $this->album_image_model->get_list("select top 2 id, name, imagePath as path from [photo_album_image] where id > '". $image_id ."' and cid = '". $image['cid'] ."' order by id Asc");
		$abouts = array_merge($abouts, $tmp['list']);
		$tmp = $this->album_image_model->get_list("select top 2 id, name, imagePath as path from [photo_album_image] where id < '". $image_id ."' and cid = '". $image['cid'] ."' order by id Desc");
		$abouts = array_merge($abouts, $tmp['list']);
		
		foreach($abouts as $key=>$item) {
			$abouts[$key]['thumb'] = $this->thumb->crop($item['path'], 200, 200);
		}
		
		$this->tpl->assign('abouts', $abouts);
		$this->tpl->assign('image', $image);
		$this->tpl->assign('prev', $prev);
		$this->tpl->assign('next', $next);
		
		
		// == seo ==
		$keywords = array();
		foreach($image['labels'] as $item) {
			$keywords[] = $item['label'];
		}
		
		$this->tpl->assign('title', $image['name']);
		$this->tpl->assign('keywords', implode(',', $keywords));
		if( ! empty( $image['description'] ) ) {
			$this->tpl->assign('description', $image['description']);
		}
		
		$this->tpl->assign('cat', $cat);
		
		$this->tpl->display($tpl, $cacahe_id);
		
		} else {
			
			$this->tpl->display($tpl, $cacahe_id);
			echo('<!-- cached -->');
			
		}
		
	}
	
	// 浏览量统计功能
	public function viewed() {
		$image_id = $this->gr('id');
		$cookie_name = 'image_viewed_ids';
		
		if( ! isset( $_COOKIE[$cookie_name] ) ) {
			$viewed_ids = array();
		} else {
			$viewed_ids = explode(',', $_COOKIE[$cookie_name]);
		}
		
		$result = array();
		
		$this->load->model('photo/album_image_model');
		
		$count = 0;
		
		if( ! in_array($image_id, $viewed_ids) ) {
			try {
				$count = $this->album_image_model->viewed( $image_id, array('update'=>TRUE) );
				$viewed_ids[] = $image_id;
				setcookie($cookie_name, implode(',', $viewed_ids), time() + 3600, '/');
				$result['type'] = 'success';
			} catch ( Exception $e ) {
				$result['type'] = 'error';
				$result['message'] = $e->getMessage();
			}
		} else {
			$count = $this->album_image_model->viewed( $image_id, array('update'=>FALSE) );
			$result['type'] = 'exists';
		}
		
		
		$result['count'] = $count;
		
		echo( json_encode( $result ) );
	}





















}

?>