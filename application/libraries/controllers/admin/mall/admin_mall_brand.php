<?php

// 建材商城品牌管理
// kko4455@163.com
// 2015-1-29
class admin_mall_brand extends admin_base {

	public function __construct(){
		parent::__construct();

	}

	// 品牌列表展示
	public function manage(){
		
		$args = array(
			'size'=>20,
			'page'=>$this->encode->get_page()
		);

		$search = array(
			'key'=>$this->gr('key')
		);

		if( !empty($search['key']) ){
			$search['key'] = iconv("gbk", "utf-8", $search['key']);
		}

		$this->load->model('mall/mall_brand_model');
		$this->load->model('mall/mall_category_model');

		$search_sql = '1=1';
		$url_params = array();

		if( !empty( $search['key'] ) ){
			$search_sql .= " and lib.brand like '%" . $search['key'] . "%'";
			$url_params[] = "key=" . $search['key'];
		}
		
		$sql = "select * from ( select lib.id, lib.brand, c.cc, row_number() over( order by lib.id desc ) as num from mall_brandlib as lib left join ( select count(id) as cc, brandid from sendbuy group by brandid ) as c on c.brandid = lib.id where ". $search_sql ." ) as temp where num between ". (($args['page'] - 1) * $args['size'] + 1) ." and " . ( $args['page'] * $args['size'] );

		$sql_count = "select count(*) as icount from mall_brandlib as lib where " . $search_sql;
		$result = $this->mall_brand_model->get_list($sql, $sql_count);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->recordCount = $result['count'];
		
		$url_params = implode('&', $url_params);

		$this->pagination->url_template_first = $this->get_complete_url('mall/brand/manage' . (empty($url_params) ? '' : '?' . $url_params));
		$this->pagination->url_template = $this->get_complete_url('mall/brand/manage?page=<{page}>' . (empty($url_params) ? '' : '&' . $url_params));

		$pagination = $this->pagination->toString(true);
		
		$list = $result['list'];
		unset($result);
		$this->mall_category_model->assign_brand($list);	// 将分类分配到品牌
		
		$this->tpl->assign('list', $list);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('args', $args);
		$this->tpl->assign('search', $search);

		$this->tpl->assign('module', 'brand.manage');
		$this->tpl->display('admin/mall/brand/manage.html');
	}

	// 品牌操作, 新增&编辑
	public function active(){

		$this->load->model('mall/mall_category_model');

		$cats = $this->mall_category_model->get_status();

		$this->tpl->assign('cats', $cats);

		$rurl = $this->gr('r');
		$id = $this->gr('id');

		$brand = NULL;
		$rels = NULL;

		if( ! empty($id) ){
			$this->load->model('mall/mall_brand_model');
			$brand = $this->mall_brand_model->get_brand($id, array(
				'fields'=>'id, brand, image, recommend'
			));
			if( $brand != false )
				$rels = $this->mall_brand_model->get_relation_ids($brand['id']);
		}

		$this->tpl->assign('rurl', $rurl);
		$this->tpl->assign('brand', $brand);
		$this->tpl->assign('rels', $rels);
		$this->tpl->assign('module', 'brand.active');
		$this->tpl->assign('time', time());
		$this->tpl->display('admin/mall/brand/active.html');
	}

	// 表单提交处理
	public function active_handler(){

		$rurl = $this->gf('rurl');

		$object = array(
			'id'=>$this->gf('id'),
			'name'=>$this->gf('brand_name'),
			'upload_image'=>$this->gf('image_path'),
			'old_image'=>$this->gf('old_image_path'),
			'big_cat'=>$this->gf('big_cat'),
			'small_cat'=>$this->gf('small_cat'),
			'recmd'=>$this->gf('recmd')
		);

		$this->load->model('tempfile_move_model');

		$object['image'] = $this->tempfile_move_model->tempfile_move($object['upload_image'], $object['old_image']);

		$this->load->model('mall/mall_brand_model');

		if( $object['id'] == 0 ){
			try{
				$this->mall_brand_model->add($object);
				$this->alert('添加成功', $this->get_complete_url('mall/brand/manage'));
			}catch(Exception $e){
				$this->alert($e->getMessage());
			}
		} else {
			// 修改模式
			try{
				$this->mall_brand_model->edit($object);
				$this->alert('修改成功', $rurl);
			}catch(Exception $e){
				$this->alert($e->getMessage());
			}
		}
	}
	
	// 品牌删除
	public function delete(){

		$rurl = $this->gr('r');
		$id = $this->gr('id');
		
		$this->load->model('mall/mall_brand_model');
		try{
			$this->mall_brand_model->delete($id);
			$this->alert('', $rurl);
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
	}

}

?>