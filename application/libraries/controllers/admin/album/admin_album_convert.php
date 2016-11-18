<?php

class admin_album_convert extends admin_base {
	
	public function __construct(){
		parent::__construct();
		$this->load->library('mdb');
	}
	
	public function image(){
		
		$max = $this->gr('max');
		
		$where = "name is null";
		
		if( $max != '' ){
			$where .= " and id > " . $max;
		}
		
		$sql = "select top 500 id, name, cats from [photo_album_image] where ". $where ." and id not in ( select image_id from [photo_image_category_relation] ) and cats <> '' order by id asc";
		
		$images = $this->mdb->query($sql);
		foreach($images as $key=>$image){
			
			$image['cats'] = trim($image['cats'], ',');
			
			if( strpos($image['cats'], ',') != false ){
				$images[$key]['cats'] = explode(',', $image['cats']);
			} else {
				$images[$key]['cats'] = explode('/', $image['cats']);
			}
			
		}
		
		$data = $this->get_cat_objects_data($images);
		
		
		$images = $this->assign_cat_objects($images, $data);
		
		//var_dump($images);
		//exit();
		
		
		foreach($images as $key=>$image){
			foreach($image['cat_ids'] as $id){
				$this->mdb->insert("insert into photo_image_category_relation(image_id, cid)values('". $image['id'] ."', '". $id ."')");
				$this->mdb->update("update photo_album_image set name = '". implode('', $image['cats']) ."' where id = '". $image['id'] ."'");
			}
		}
		if( count($images) == 0 ) exit('done');
		echo('<a href="?max='. $images[count($images)-1]['id'] .'">'. $images[count($images)-1]['id'] .'</a>');
		
	}
	
	private function get_cat_objects_data($images){
		
		$names = array();
		foreach($images as $image){
			foreach($image['cats'] as $k=>$cat){
				if( $cat != '' && ! in_array($cat, $names) ){
					$names[] = $cat;
				}
			}
		}
		
		$data = $this->mdb->query("select id, name from [photo_category] where name in ('". implode("','", $names) ."')");
		return $data;
	}
	
	private function assign_cat_objects($images, $data){
		foreach($images as $key=>$image){
			$image['cat_ids'] = array();
			foreach($data as $k=>$v){
				if( in_array($v['name'], $image['cats']) ){
					$image['cat_ids'][] = $v['id'];
				}
			}
			$images[$key] = $image;
		}
		return $images;
	}
	
	
}

?>