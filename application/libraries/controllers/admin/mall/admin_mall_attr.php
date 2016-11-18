<?php

class admin_mall_attr extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function active(){
		
		$info = array(
			'name'=>$this->gf('name'),
			'cid'=>$this->gf('cid'),
			'pid'=>$this->gf('pid'),
			'id'=>$this->gf('id')
		);
		
		$result = array(
			'errors'=>array()
		);
		
		if( empty($info['name']) ){
			$result['errors'][] = '属性名称不能为空';
		}
		
		if( empty($info['cid']) ){
			$result['errors'][] = '分类ID不能为空';
		}
		
		if( count( $result['errors'] ) == 0 ){
			$this->load->model('mall/mall_attr_model');
			try{
				
				if( empty($info['id']) ){
					// 添加模式
					$attr = $this->mall_attr_model->add($info);
					$result['attr'] = $attr;
				} else {
					// 编辑模式
					$this->mall_attr_model->edit($info);
					$result['attr'] = $info;
				}
				
			} catch(Exception $e) {
				$result['errors'][] = $e->getMesage();
			}
		}
		
		if( count( $result['errors'] ) == 0 ){
			$result['type'] = 'success';
		} else {
			$result['type'] = 'error';
		}
		
		echo( json_encode($result) );
		
	}
	
	// 排序
	public function sort_handler(){
		$cid = $this->gf('cid');
		$sorts = $this->gf('data');
		
		$this->load->model('mall/mall_attr_model');
		
		$result = array(
			'errors'=>array()
		);
		
		try{
			$this->mall_attr_model->sort_active($sorts);
		}catch(Exception $e){
			$result['errors'][] = $e->getMessage();
		}
		
		if( count($result['errors']) == 0 ){
			$result['type'] = 'success';
		} else {
			$result['type'] = 'error';
		}
		
		echo( json_encode( $result ) );
		
	}
	
	// 删除
	public function delete(){
		$cid = $this->gf('cid');
		$id = $this->gf('id');
		$this->load->model('mall/mall_attr_model');
		
		$result = array(
			'errors'=>array()
		);
		
		try{
			$this->mall_attr_model->delete($id);
		}catch(Exception $e){
			$result['errors'][] = $e->getMessage();
		}
		
		if( count($result['errors']) == 0 ){
			$result['type'] = 'success';
		} else {
			$result['type'] = 'error';
		}
		
		echo( json_encode($result) );
		
	}
	
}

















?>