<?php

// 2016-05-24 图库列表
class album_imglist extends album_base {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function home() {
		
		$tpl = $this->get_tpl( 'album_imglist.html' );
		
		$this->load->library('thumb');
		
		$this->load->model('photo/album_category_model');
		$this->load->model('photo/image_label_model');
		$this->load->model('photo/album_image_model');
		
		$args = array(
			'id'=>$this->gr('id'),
			'ext'=>$this->gr('ext'),
			'size'=>30,
			'page'=>$this->encode->get_page()
		);
		
		if( empty( $args['id'] ) ) {
			show_404();
			exit;
		}
		
		$params = array();
		$where = array("1=1");
		
		if( ! empty( $args['id'] ) ) {
			$params[] = "id=" . $args['id'];
			$where[] = "cid = '". $args['id'] ."'";
		}
		
		
		$ext = false;
		if( ! empty( $args['ext'] ) ) {
			$params[] = "ext=" . $args['ext'];
			
			$ext = $this->album_category_model->get_cat($args['ext'], array(
				'fields'=>'id, name, name2, description',
				'format'=>false
			));
			
			$where[] = "id in ( select image_id from photo_image_label where label in ('". $ext['name'] ."', '". $ext['name2'] ."') )";
		}
		
		$cats = $this->album_category_model->get_list("select id, name, name2, char from photo_category where pid <> 0 order by char asc");
		$current = $this->album_category_model->get_cat($args['id'], array(
			'fields'=>'id, name, description',
			'format'=>false
		));
		
		// 提取分类关联的词汇
		$ref = $this->image_label_model->ref( $current['id'] );
		$ref = $ref['data'];
		if( $ext != false ) {
			foreach( $ref as $item ) {
				if( $item['id'] == $args['ext'] ) {
					$ext = $item;
				}
			}
		}
		
		if( $current == false ) {
			show_404();
			exit;
		}
		
		$cats = $this->album_category_model->char_merge($cats['list']);
		
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
		
		$where = implode(" and ", $where);
		
		$sql = "select * from ( select id, name, ImagePath as path, cid, num = row_number() over( order by addtime desc ) from photo_album_image where ". $where ." ) as tmp where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from photo_album_image where " . $where;
		
		$images = $this->album_image_model->get_list($sql, $sql_count);
		$this->image_label_model->assign($images['list'], array(
			'size'=>4
		));
		
		foreach($images['list'] as $key=>$value) {
			$value['thumb'] = $this->thumb->resize($value['path'], 285, 0, array(
				'mode'=>'object'
			));
			$images['list'][$key] = $value;
		}
		
		$urls = $this->config->item('url');
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->recordCount = $images['count'];
		
		$this->pagination->url_template = $urls['tuku'] . 'imglist?page=<{page}>&' . implode('&', $params);
		$this->pagination->url_template_first = $urls['tuku'] . 'imglist?' . implode('&', $params);
		
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		
		$this->tpl->assign('list', $images['list']);
		$this->tpl->assign('current', $current);
		$this->tpl->assign('labels', $labels);
		$this->tpl->assign('ref', $ref);
		$this->tpl->assign('ext', $ext);
		
		if( $ext == false ) {
			$this->tpl->assign('title', $current['name'] . '-装修图库');
			$this->tpl->assign('keywords', $current['name']);
		} else {
			$this->tpl->assign('title', $ext['label'] . '-装修图库');
			$this->tpl->assign('keywords', $current['name'] . ',' . $ext['name']);
		}
		$this->tpl->assign('description', $current['description']);
		
		$this->tpl->display( $tpl );
		
	}
	
}

?>