<?php

// 装修档案，装修工地的材料清单

class admin_archive_product extends admin_base {

	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'product');
	}
	
	// 材料清单列表，管理
	public function manage(){

		$cas_id = $this->gr('cas');
		$rurl = $this->gr('r');

		$cas = $this->_get_cas($cas_id);

		$this->load->model('archive/archive_prod_model');
		
		// 获取产品列表
		$prds = $this->archive_prod_model->get_cas_prds($cas_id);

		$total = $this->archive_prod_model->get_prds_total($prds);

		$this->tpl->assign('prds', $prds);
		$this->tpl->assign('rurl', $rurl);
		$this->tpl->assign('cas', $cas);
		$this->tpl->assign('total', $total);
		$this->tpl->display('admin/archive/product/manage.html');


	}

	// 添加材料，修改材料
	public function edit(){

		$cas_id = $this->gr('cas');
		$rurl = $this->gr('r');

		$product = false;

		$id = $this->gr('id');
		if( ! empty($id) ){
			$this->load->model('archive/archive_prod_model');
			$product = $this->archive_prod_model->get($id);
		}

		$cas = $this->_get_cas($cas_id);
		
		$this->tpl->assign('product', $product);
		$this->tpl->assign('rurl', $rurl);
		$this->tpl->assign('cas', $cas);

		$this->tpl->display('admin/archive/product/edit.html');
		
	}

	// 移除产品
	public function remove(){

		$id = $this->gr('id');
		$cas = $this->gr('cas');
		$request_type = $this->gr('type');
		$rurl = $this->gr('r');

		$result = array();
		$result['error'] = '';
		
		$this->load->model('archive/archive_prod_model');

		try{
			$this->archive_prod_model->delete($id);
		}catch(exception $e){
			$result['error'] = $e->getMessage();
		}
		
		if( empty($result['error']) ){
			$result['data'] = 'success';
		} else {
			$result['data'] = 'error';
			$result['message'] = $result['error'];
		}

		echo( json_encode($result) );

	}

	private function _get_cas($id){
		$this->load->model('archive/archive_cas_model');
		$cas = $this->archive_cas_model->get_cas($id, 'id, tid, name, huxing, address, community');
		return $cas;
	}

	// 材料内容提交
	public function edit_handler(){

		$rurl = $this->gf('r');

		$prod = array(
			'id'=>$this->gf('id'),
			'cas_id'=>$this->gf('cas_id'),
			'image_path'=>$this->gf('image_path'),
			'old_image_path'=>$this->gf('old_image_path'),
			'name'=>$this->gf('name'),
			'unit'=>$this->gf('unit'),
			'price'=>$this->gf('price'),
			'type'=>$this->gf('type'),
			'brand'=>$this->gf('brand'),
			'amount'=>$this->gf('amount'),
			'bind_mall_url'=>$this->gf('bind_mall_url'),
			'addtime'=>date('Y-m-d H:i:s'),
			'admin'=>$this->admin_username
		);

		// 将临时目录图片转移，返回相对路径
		try{
			$prod['image'] = $this->move_temp_image($prod['image_path'], $prod['old_image_path']);
			unset($prod['old_image_path']);
			unset($prod['image_path']);
		}catch(Exception $e){
			echo('图片添加失败, ' . $e->getMessage());
		}

		// 提取ID
		$prod['prd_id'] = $this->get_mall_prod_id($prod['bind_mall_url']);
		unset( $prod['bind_mall_url'] );
		
		$this->load->model('archive/archive_prod_model');
		
		try{

			if( $prod['id'] == 0 || empty($prod['id']) ){
				$id = $this->archive_prod_model->add($prod);
				$this->alert('添加成功', $rurl);
			} else {
				$id = $this->archive_prod_model->edit($prod);
				$this->alert('修改成功', $rurl);
			}

		} catch (Exception $e){
			$this->alert('失败' . $e->getMessage());
		}

	}
	
	// $new_image 新地址, 可能是一个在临时目录中的文件
	// $new_image : temp/archive_prod/2015/01/23/65887dbedb915baeeb8b33e2a76d3437.jpg
	// $old_image 旧图片地址, 可能为空（第一次提交表单时）
	// $new_image : archive_prod/2015/01/23/65887dbedb915baeeb8b33e2a76d3437.jpg
	// 删除旧文件，将新文件转移到原旧文件位置，使用旧文件名称
	private function move_temp_image($new_image, $old_image){
		
		if( empty($new_image) ) return $old_image;

		$target = '';
		$config = $this->config->item('upload_image_options');
		$path = $config[0]['path'];

		if( empty( $old_image ) ){
			$target = str_replace('temp/', '', $new_image);
		} else {
			$target = $old_image;
		}

		$this->load->library('fileact');
		$new_image = $path . str_replace('/', '\\', $new_image);
		$target_image = $path . str_replace('/', '\\', $target);
		if( ! $this->fileact->move_file($new_image, $target_image) ){
			throw new Exception('移动文件到目标位置出错');
		}

		return $target;

	}
	
	// 从商城URL中提取产品ID
	// url : http://mall.shzh.net/item.htm?id=126554
	private function get_mall_prod_id($url){

		$url = strtolower($url);

		$matchs = NULL;

		preg_match('/.+?\?.*?id\=(\d+).*/', $url, $matchs);
		
		if( count($matchs) == 2 ){
			return $matchs[1];
		}

		return 0;

	}


}


?>