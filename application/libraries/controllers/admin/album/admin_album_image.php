<?php

// 2015-12-08
// kko4455@163.com
// 相册图片管理

class admin_album_image extends admin_base {
	
	private $album = NULL;
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'image');
	}
	
	public function manage(){
		
		$this->load->model('photo/album_image_model');
		$this->load->model('photo/album_category_model');
		$this->load->model('photo/image_label_model');
		$this->load->model('photo/Photo', 'album_model');
		
		$this->load->library('pagination');
		
		$tpl = $this->get_tpl('album/image/manage.html');
		
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>25
		);
		
		$so = array(
			'tmp'=>$this->gr('tmp'),
			'key'=>$this->gr('key'),
			'cid'=>$this->gr('cid')
		);
		
		$where = "1=1";
		$params = array();
		
		if( $so['tmp'] == 1 ) {
			$this->tpl->assign('module', 'recycle');
			$where .= " and cid = 0";
			$params[] = "tmp=1";
		} else {
			$where .= " and cid <> 0";
			$this->tpl->assign('module', 'manage');
		}
		
		if( ! empty( $so['key'] ) ) {
			$so['key'] = iconv('gbk', 'UTF-8', $so['key']);
			$where .= " and name like '%". $so['key'] ."%'";
			$params[] = "key=" . $so['key'];
		}
		
		if( ! empty( $so['cid'] ) ) {
			$where .= " and cid='". $so['cid'] ."'";
			$params[] = "cid=" . $so['cid'];
		}
		
		$sql = "select * from ( select id, name, imagePath as path, class, cid, admin, albumid, addtime, num = row_number() over( order by addtime desc ) from photo_album_image where ". $where ." ) as tmp where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from photo_album_image where " . $where;
		
		$result = $this->album_image_model->get_list($sql, $sql_count, array(
			'format'=>false
		));
		
		// 为图片附加相册信息
		// $result['list'] = $this->album_model->album_assign_object($result['list']);
		
		// 为图片附件标签
		$this->image_label_model->assign($result['list']);
		// 附加主要分类
		$result['list'] = $this->album_category_model->assign($result['list'], array(
			'key_name'=>'cid',
			'assign_name'=>'category'
		));
		
		// var_dump2($result['list']);
		
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->pageSize = $args['size'];
		
		if( count( $params ) == 0 ){
			$this->pagination->url_template = $this->get_complete_url('/album/image/manage?page=<{page}>');
			$this->pagination->url_template_first = $this->get_complete_url('/album/image/manage');
		} else {
			$this->pagination->url_template = $this->get_complete_url('/album/image/manage?page=<{page}>&' . implode('&', $params));
			$this->pagination->url_template_first = $this->get_complete_url('/album/image/manage?' . implode('&', $params));
		}
		
		$this->tpl->assign('so', $so);
		$this->tpl->assign('pagination', $this->pagination->toString(true));
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('count', $result['count']);
		$this->tpl->assign('args', $args);
		
		$this->tpl->display( $tpl );
		
	}
	
	public function act($module = 'edit'){
		
		$rurl = $this->gr('r');
		$id = $this->gr('id');
		
		$this->load->model('photo/album_image_model');
		$this->load->model('photo/album_category_model');
		$this->load->model('photo/image_label_model');
		
		$tpl = $this->get_tpl( '/album/image/active.html' );
		
		if( $module == 'edit' ) {
			
			$image = $this->album_image_model->get_image($id, array(
				'format'=>false,
				'fields'=>'id, name, imagepath as path, description, albumid, sort_id, cats, class, cid, assist_base, showcount_base, assist, showcount, addtime, admin, recommend',
			));
			
			// 附加主要分类
			$image = $this->album_category_model->assign($image, array(
				'key_name'=>'cid',
				'assign_name'=>'class_info'
			));
			
		}
		
		$this->image_label_model->assign($image);
		
		$this->tpl->assign('image', $image);
		$this->tpl->assign('module', $module);
		$this->tpl->assign('rurl', $rurl);
		$this->tpl->display( $tpl );
	}
	
	// 图片上传
	public function upload() {
		$this->act( 'upload' );
	}
	
	// 图片删除
	private function delete() {
		
		$id = $this->gf('id');
		
		// 删除功能暂时不做
		$this->load->model('photo/album_image_model');
		
		try{
			$this->album_image_model->delete($id);
			echo('success');
		} catch(Exception $e) {
			echo( $e->getMessage() );
		}
		
		
	}
	
	public function handler() {
		
		if( $this->gf('id') != '' ) {
			$this->edit();
		} else {
			$this->add();
		}
		
	}
	
	private function add() {
		
		$info = $this->get_form_data();
		
		$info['addtime'] = date('Y-m-d H:i:s');
		$info['admin'] = $this->admin_username;
		
		$this->load->model('tempfile_move_model');
		$info['path'] = $this->tempfile_move_model->tempfile_move($info['new_thumb']);
		
		
		$this->load->model('photo/album_image_model');
		
		try {
			$id = $this->album_image_model->add( $info );
			$this->alert('成功', $this->get_complete_url('album/image/manage'));
		} catch ( Exception $e ) {
			$this->alert( $e->getMessage() );
		}
		
		
	}
	
	private function edit() {
		$info = $this->get_form_data();
		$this->load->model('photo/album_image_model');
		
		$info['rurl'] = str_replace('&amp;', '&', $info['rurl']);
		try {
			$this->album_image_model->edit( $info );
			$this->alert('成功', $info['rurl']);
		} catch ( Exception $e ) {
			$this->alert('失败, 原因:' . $e->getMessage());
		}
	}
	
	// 分类/标签选择窗口
	public function labels_selection_dialog(){
		
		$tp = $this->gr('tp');
		
		$tpl = $this->get_tpl('album/image/labels_selection_dialog.html');
		
		$this->load->model('photo/album_category_model');
		$cats = $this->album_category_model->get_list("select id, name, name2, pid, char from photo_category where pid <> 0 order by char asc");
		
		$cats = $this->album_category_model->char_merge($cats['list']);
		
		
		$array = array(
			array('name'=>'ABCDEFG', 'pattern'=>'/^[ABCDEFG]$/', 'data'=>array()),
			array('name'=>'HIJKLMN', 'pattern'=>'/^[HIJKLMN]$/', 'data'=>array()),
			array('name'=>'OPQRST', 'pattern'=>'/^[OPQRST]$/', 'data'=>array()),
			array('name'=>'UVWXYZ', 'pattern'=>'/^[UVWXYZ]$/', 'data'=>array()),
		);
		
		foreach($array as $key=>$value){
			foreach($cats as $item){
				if( preg_match($value['pattern'], $item['py']) == true ){
					$value['data'][] = $item;
				}
			}
			$array[$key] = $value;
		}
		
		$this->tpl->assign('tp', $tp);
		$this->tpl->assign('data', $array);
		$this->tpl->display( $tpl );
		
	}
	
	/*
	public function parse(){
		
		$this->load->model('photo/image_label_model');
		$this->image_label_model->build_cid_by_name();
		
	}
	*/

}


?>