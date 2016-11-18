<?php

// 2015-12-03
// kko4455@163.com
// 相册管理
class admin_album_album extends admin_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'manage');
	}
	
	
	public function manage(){
		$this->load->model('photo/photo', 'album_model');
		$this->load->model('photo/album_image_model');
		$this->load->model('photo/album_category_model');
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>20
		);
		
		$so = array(
			'key'=>$this->gr('key'),
			'cate_no_set'=>$this->gr('cate_no_set')
		);
		
		$params = '';
		$where = '1=1';
		if( ! empty( $so['key'] ) ){
			$so['key'] = iconv("gbk", "utf-8", $so['key']);
			$params .= 'key=' . $so['key'];
			$where .= " and name like '%". $so['key'] ."%'";
		}
		if( ! empty( $so['cate_no_set'] ) ){
			$params .= 'cate_no_set=' . $so['cate_no_set'];
			$where .= " and id not in ( select album_id from [photo_album_category_relation] )";
		}
		
		$sql = "select * from ( select id, name, gongcheng, fm_image as fm, admin, addtime, num = row_number() over(order by addtime desc) from photo_album where ". $where ." ) as temp where num between ". ( ($args['page']-1) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		
		$sql_count = "select count(*) as icount from [photo_album] where " . $where;
		$result = $this->album_model->get_albums($sql, $sql_count, array(
			'format'=>true
		));
		
		$result['list'] = $this->album_image_model->count_assign($result['list']);
		$result['list'] = $this->album_category_model->category_assign_albums($result['list']);
		
		// var_dump2($result['list']);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->recordCount = $result['count'];
		
		
		if( $params != '' ){
			$this->pagination->url_template = $this->get_complete_url('/album/album/manage?page=<{page}>&' . $params);
			$this->pagination->url_template_first = $this->get_complete_url('/album/album/manage?' . $params);
		} else {
			$this->pagination->url_template = $this->get_complete_url('/album/album/manage?page=<{page}>');
			$this->pagination->url_template_first = $this->get_complete_url('/album/album/manage');
		}
		
		
		$this->tpl->assign('pagination', $this->pagination->toString(true));
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('args', $args);
		$this->tpl->assign('so', $so);
		$this->tpl->display( $this->get_tpl('album/album/manage.html') );
	}
	
	
	// 新增 & 编辑相册
	public function add(){
		$info = array(
			'id'=>$this->gr('id'),
			'rurl'=>$this->gr('r')
		);
		
		if( $info['id'] != '' ){
			$this->load->model('photo/photo', 'album_model');
			$this->load->model('photo/album_category_model');
			$this->load->model('photo/album_image_model');
			$this->load->library('thumb');
			$album = $this->album_model->getAlbum($info['id'], array(
				'fields'=>'id, name, description, imagetype, gongcheng, admin, tp, rank',
				'format'=>false
			));
			$album = $this->album_category_model->category_assign_albums($album);
			
			$album = $this->album_image_model->image_assign($album, array(
				'fields'=>'id, name, imagepath as path, albumid'
			));
			
			foreach($album['images'] as $key=>$item){
				$album['images'][$key]['thumb'] = $this->thumb->crop($item['path'], 100, 100);
			}
			
			// var_dump2($album);
			
			$this->tpl->assign('album', $album);
			$this->tpl->assign('module', 'edit');
		} else {
			$this->tpl->assign('album', false);
			$this->tpl->assign('module', 'add');
		}
		
		$this->tpl->assign('rurl', $info['rurl']);
		
		$this->tpl->display( $this->get_tpl('album/album/active.html') );
		
	}
	
	public function handler(){
		$info = $this->get_form_data();
		$info['addtime'] = date('Y-m-d H:i:s');
		$info['admin'] = $this->admin_username;
		$info['cats'] = explode(',', $info['cats']);
		$this->load->model('photo/album_active_model');
		
		if( $info['rurl'] == '' ) $info['rurl'] = $this->get_complete_url('/album/album/manage');
		
		try{
			
			if( $info['id'] == '' ){
				$this->album_active_model->add($info);
			} else {
				$this->album_active_model->edit($info);
			}
			
			$this->alert('提交成功', $info['rurl']);
			
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
	}






















}

?>