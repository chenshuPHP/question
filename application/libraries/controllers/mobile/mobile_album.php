<?php

// 装修图库 手机版

class mobile_album extends mobile_base {
	public function __construct(){
		parent::__construct();
	}
	
	public function index() {
		
		$tpl = $this->get_tpl('album/imglist.html');
		$this->load->model('mobile/mobile_url_model');
		$this->load->model('photo/album_category_model');
		$this->load->library('thumb');
		$cats = $this->album_category_model->get_list("select id, name, char from photo_category where pid <> 0");
		$this->mobile_url_model->format_tuku_category($cats['list']);
		$cats = $this->album_category_model->char_merge($cats['list']);
		
		$args = array(
			'page' => $this->encode->get_page(),
			'size' => 24,
			'where' => array()
		);
		
		
		$curr_category = NULL;
		$cid = $this->gr('id');
		if( ! empty( $cid ) ) {
			$curr_category = $this->album_category_model->get_cat($cid, array(
				'format'=>false
			));
			$args['where'][] = "cid = '". $cid ."'";
		}
		
		$labels = array(
			array('name'=>'A B C D', 'pattern'=>'/[ABCD]/', 'childs'=>array()),
			array('name'=>'E F G', 'pattern'=>'/[EFG]/', 'childs'=>array()),
			array('name'=>'H I J K', 'pattern'=>'/[HIJK]/', 'childs'=>array()),
			array('name'=>'L M N', 'pattern'=>'/[LMN]/', 'childs'=>array()),
			array('name'=>'O P Q', 'pattern'=>'/[OPQ]/', 'childs'=>array()),
			array('name'=>'R S T', 'pattern'=>'/[RST]/', 'childs'=>array()),
			array('name'=>'U V W', 'pattern'=>'/[UVW]/', 'childs'=>array()),
			array('name'=>'X Y Z', 'pattern'=>'/[XYZ]/', 'childs'=>array()),
		);
		foreach( $labels as $key=>$value ) {
			foreach($cats as $item) {
				if( preg_match($value['pattern'], $item['py']) ) {
					$value['childs'][] = $item;
				}
			}
			$labels[$key] = $value;
		}
		unset($cats);
		
		
		$images = $this->_get_images($args);
		
		
		
		foreach($images['list'] as $key=>$value) {
			$images['list'][$key]['thumb'] = $this->thumb->resize($value['path'], 320, 0, array(
				'mode'=>'object'
			));
		}
		
		$images_1 = array();
		$images_2 = array();
		
		for($i = 0; $i < count($images['list']); $i++) {
			if( $i % 2 == 0 ) {
				$images_1[] = $images['list'][$i];
			} else {
				$images_2[] = $images['list'][$i];
			}
		}
		
		if( ! empty( $curr_category ) ) {
			$title = $curr_category['name'] . '-装修图库';
			$page_name = $curr_category['name'];
		} else {
			$title = '装修图库';
			$page_name = '装修图库';
		}
		
		$this->tpl->assign('title', $title);
		$this->tpl->assign('page_name', $page_name);
		$this->tpl->assign('current_category_id', $cid);
		
		$this->tpl->assign('images_1', $images_1);
		$this->tpl->assign('images_2', $images_2);
		$this->tpl->assign('labels', $labels);
		$this->tpl->display( $tpl );
		
	}
	
	public function image_view()
	{
		$id = $this->gr('id');
		
		$this->load->model('photo/album_image_model');
		$this->load->model('mobile/mobile_url_model');
		
		$image = $this->album_image_model->get_image($id, array(
			'fields'=>'id, name, imagepath as path, cid, description',
			'format'=>false
		));
		
		$this->mobile_url_model->format_tuku_image($image);
		
		
		$this->tpl->assign('image', $image);
		$this->tpl->assign('title', $image['name']);
		if( ! empty( $image['description'] ) )
		{
			$this->tpl->assign('description', $image['description']);
		}
		$tpl = $this->get_tpl('album/view.html');
		$this->tpl->display( $tpl );
	}
	
	
	private function _get_images($args = array()){
		
		$this->load->model('photo/album_image_model');
		
		$config = array_merge(array(
			'size'=>20,
			'fields'=>'id, name, imagePath as path, cid',
			'orderby'=>'order by id desc',
			'where'=>array()
		), $args);
		
		$where = '';
		if( count( $config['where'] ) != 0 ) {
			$where = " where " . implode(" and ", $config['where']);
		}
		
		//$sql = "select * from ( select ". $config['fields'] .", num = row_number() over( order by id desc ) from [photo_album_image]". $where ." ) as tmp where num between ". (( $config['page'] - 1 ) * $config['size'] + 1) ." and " . ( $config['size'] * $config['page'] );
		
		$sql = "select top ". $config['size'] ." ". $config['fields'] ." from photo_album_image" . $where . " " . $config['orderby'];
		
		$sql_count = "select count(*) as icount from photo_album_image" . $where;
		
		$result = $this->album_image_model->get_list($sql, $sql_count);
		$this->mobile_url_model->format_tuku_image($result['list']);
		return $result;
	}
	
	// 全部分类页面
	public function category(){
		$this->load->model('photo/PhotoCategory', 'album_category');
		$data = $this->album_category->getAll();
		$data = $this->mobile_url_model->format_batch_album_category($data);
		$this->tpl->assign('data', $data);
		$this->tpl->display('mobile/album/album_category.html');
	}
	
	
	public function loader() {
		
		$this->load->library('thumb');
		
		$config = array(
			'size'=>$this->gr('size'),
			'start'=>$this->gr('start'),
			'cid'=>$this->gr('cid'),
			'original'=>$this->gr('original'),
			'type'=>$this->gr('type')
		);
		
		if( empty( $config['size'] ) ) $config['size'] = 16;
		if( $config['size'] > 30 ) $config['size'] = 30;
		
		if( empty( $config['type'] ) ) $config['type'] = 'next';
		if( $config['type'] != 'next' ) $config['type'] = 'previous';
		
		$where = array();
		$orderby = 'order by id desc';
		if( ! empty( $config['cid'] ) ) {
			$where[] = "cid='". $config['cid'] ."'";
		}
		
		if( ! empty( $config['start'] ) )
		{
			
			if( $config['type'] === 'next' )
			{
				$where[] = "id < '". $config['start'] ."'";
			}
			else
			{
				$where[] = "id > '". $config['start'] ."'";
				$orderby = "order by id asc";
			}
			
		}
		
		$result = $this->_get_images(array(
			'size' => $config['size'],
			'where'=>$where,
			'orderby'=>$orderby,
			'type'=>$config['type']
		));
		
		$result = $result['list'];
		
		if( $config['original'] == 1 )
		{
			$upload_url = config_item('upload_image_options');
		}
		
		foreach($result as $key=>$item) {
			$result[$key]['thumb'] = $this->thumb->resize($item['path'], 320, 0, array(
				'mode'=>'object'
			));
			
			if( $config['original'] == 1 )
			{
				// $upload_url = config_item('upload_image_options');
				$result[$key]['original'] = $upload_url[0]['url'] . ltrim($item['path'], '/');
			}
			
		}
		
		// var_dump2($result);
		
		echo( json_encode( $result ) );
		
	}
	
	/*
	public function image_view(){
		$this->load->library('encode');
		$this->load->model('photo/Photo', 'album_model');
		$this->load->model('photo/PhotoCategory', 'album_category');
		$this->load->model('mobile/mobile_url_model');
		$image_id = $this->encode->get_request_encode('id');
		$cid = $this->encode->get_request_encode('cid');
		$r = $this->encode->get_request_encode('r');
		$image = $this->album_model->getImage($image_id);
		$this->tpl->assign('image', $image);
		$next = $this->album_model->getNext($image_id);
		$next = $this->mobile_url_model->format_single_album_image($next, $cid);
		$prev = $this->album_model->getPrev($image_id);
		$prev = $this->mobile_url_model->format_single_album_image($prev, $cid);
		$this->tpl->assign('next', $next);
		$this->tpl->assign('prev', $prev);
		$this->tpl->assign('r', $r);
		$cate = $this->album_category->getItem($cid);
		$this->tpl->assign('cate', $cate);
		$this->tpl->display('mobile/album/album_image_view.html');
	}
	*/
	
}
?>