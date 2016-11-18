<?php

// 装修团购主题设计
class admin_mall_zxtg extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 产品管理界面
	public function prds(){
		
		$this->load->model('mall/mall_zxtg_model');
		$prds = $this->mall_zxtg_model->get_prds();
		
		$this->tpl->assign('module', 'prds.manage');
		$this->tpl->assign('prds', $prds);
		
		$this->tpl->display('admin/mall/zxtg/zxtg_prds.html');
		
	}
	
	// 添加商品
	public function prd_active(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		// 编辑模式
		if( ! empty($id) ){
			$this->load->model('mall/mall_zxtg_model');
			$prd = $this->mall_zxtg_model->get_prd($id);
			$this->tpl->assign('prd', $prd);
			$this->tpl->assign('rurl', $r);
		}
		
		$this->tpl->assign('module', 'prds.manage');
		$this->tpl->display('admin/mall/zxtg/zxtg_prd_active.html');
	}
	
	// 图片替换
	private function _replace_image(&$object){
		
		$this->load->library('fileact');
		$config = $this->config->item('upload_image_options');
		$root = $config[0]['path'];
		
		$temp_file_path = $root . str_replace('/', '\\', $object['image']);	// 临时文件的完整路径
		
		if( file_exists( $temp_file_path ) ){
			
			if( ! $this->fileact->move_temp_file($temp_file_path) ){
				throw new Exception('移动文件失败');
			}
			
			// 移除原来的文件
			if( ! empty( $object['origin_image'] ) ){
				$original_file_path = $root . str_replace('/', '\\', $object['origin_image']);
				if( file_exists( $original_file_path ) ){
					$this->fileact->delete_file($original_file_path);
				}
			}
			
			$object['image'] = str_replace('/temp/', '/', $object['image']);
			
		} else {
			
			$object['image'] = $object['origin_image'];
			
		}
		
	}
	
	// 数据表单提交
	public function prd_active_handler(){
		
		$prd = array(
			
			'id'=>$this->gf('id'),	// 编辑模式
		
			'image'=>$this->gf('image'),
			'origin_image'=>$this->gf('origin_image'),
			'name'=>$this->gf('name'),
			'price'=>$this->gf('price'),
			'webprice'=>$this->gf('webprice'),
			'link'=>$this->gf('link'),
			
			'type'=>$this->gf('type'),
			'labels'=>$this->gf('labels'),	// 抢购labels
			
			'description'=>$this->gf('description'),
			
			'username'=>$this->gf('username'),
			
			'score'=>$this->gf('score')		// 兑换礼品积分数量
			
		);
		
		if( empty($prd['type']) ){
			$prd['type'] = 'prd';
		}
		
		
		$r = $this->gf('r');
		if( empty($r) ) $r = $this->get_complete_url('/mall/zxtg/prds');
		
		if( empty($prd['image']) ) $prd['image'] = $prd['origin_image'];
		
		if($prd['image'] != $prd['origin_image'] && $prd['image'] != ''){
			$this->_replace_image($prd);
		}
		
		$this->load->model('mall/mall_zxtg_model');
		
		try{
			
			if( empty( $prd['id'] ) ){
				$this->mall_zxtg_model->prd_add($prd);
				$this->alert('提交成功', $r);
			} else {
				$this->mall_zxtg_model->prd_edit($prd);
				$this->alert('修改成功', $r);
			}
			
		}catch(Exception $e){
			$this->alert($e->getMessage(), '');
		}
	}
	
	// 产品排序
	public function prd_sort(){
		
		$data = $_POST['data'];
		$this->load->model('mall/mall_zxtg_model');
		
		try{
			$this->mall_zxtg_model->prd_sort($data);
			echo('success');
		}catch(Exception $e){
			$this->alert($e->getMessage(), '');
		}
		
	}
	
	// 产品删除
	public function prd_delete(){
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		$this->load->model('mall/mall_zxtg_model');
		
		$prd = $this->mall_zxtg_model->get_prd($id, 'image');
		
		if( $prd != false ){
			if( ! empty( $prd['image'] ) ){
				
				$config = $this->config->item('upload_image_options');
				
				$this->load->library('fileact');
				
				$path = $config[0]['path'];
				$file_complete_path = $path . str_replace('/', '\\', $prd['image']);
				
				//echo( $file_complete_path );
				
				$this->fileact->delete_file($file_complete_path);
			}
			
			try{
				$this->mall_zxtg_model->prd_delete($id);
				$this->alert('', $r);
			}catch(Exception $e){
				echo($e->getMessage());
			}
			
		} else {
			echo('没有找到需要删除的对象');
		}
		
	}
	
	// 限时抢购
	public function robs(){
		
		$this->load->model('mall/mall_zxtg_model');
		
		$settings = array(
			'type'=>'rob',		// 读取抢购的数据
			'fields'=>'id, name, image, price, webprice, labels, link'
		);
		
		$prds = $this->mall_zxtg_model->get_prds($settings);
		
		$this->tpl->assign('module', 'robs.manage');
		$this->tpl->assign('prds', $prds);
		
		$this->tpl->display('admin/mall/zxtg/zxtg_robs.html');
		
	}
	
	
	public function rob_active(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		// 编辑模式
		if( ! empty($id) ){
			$this->load->model('mall/mall_zxtg_model');
			$prd = $this->mall_zxtg_model->get_prd($id, 'id, name, image, price, webprice, link, sortid, labels');
			$this->tpl->assign('prd', $prd);
			
		}
		
		$this->tpl->assign('rurl', $r);
		
		$this->tpl->assign('module', 'robs.manage');
		$this->tpl->display('admin/mall/zxtg/zxtg_rob_active.html');
	}
	
	// 热销品牌
	public function brands(){
		
		$this->load->model('mall/mall_zxtg_model');
		
		$settings = array(
			'type'=>'brand',		// 读取热销品牌数据
			'fields'=>'id, name, image, link'
		);
		
		$prds = $this->mall_zxtg_model->get_prds($settings);
		
		$this->tpl->assign('module', 'brands.manage');
		$this->tpl->assign('prds', $prds);
		
		$this->tpl->display('admin/mall/zxtg/zxtg_brands.html');
		
	}
	
	public function brand_active(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		// 编辑模式
		if( ! empty($id) ){
			$this->load->model('mall/mall_zxtg_model');
			$prd = $this->mall_zxtg_model->get_prd($id, 'id, name, image, price, webprice, link, sortid, labels');
			$this->tpl->assign('prd', $prd);
			
		}
		
		$this->tpl->assign('rurl', $r);
		
		$this->tpl->assign('module', 'brands.manage');
		$this->tpl->display('admin/mall/zxtg/zxtg_brand_active.html');
	}
	
	// 施工队
	public function workers(){
		
		$this->load->model('mall/mall_zxtg_model');
		
		$settings = array(
			'type'=>'workers',
			'fields'=>'id, name, image, link'
		);
		
		$prds = $this->mall_zxtg_model->get_prds($settings);
		
		$this->tpl->assign('module', 'workers.manage');
		$this->tpl->assign('prds', $prds);
		
		$this->tpl->display('admin/mall/zxtg/zxtg_workers.html');
		
	}
	
	public function worker_active(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		// 编辑模式
		if( ! empty($id) ){
			$this->load->model('mall/mall_zxtg_model');
			$prd = $this->mall_zxtg_model->get_prd($id, 'id, name, image, link, description');
			$this->tpl->assign('prd', $prd);
		}
		
		$this->tpl->assign('rurl', $r);
		
		$this->tpl->assign('module', 'workers.manage');
		$this->tpl->display('admin/mall/zxtg/zxtg_worker_active.html');
	}
	
	// 设计师
	public function designs(){
		
		$this->load->model('mall/mall_zxtg_model');
		
		$settings = array(
			'type'=>'designs',
			'fields'=>'id, name, image, link'
		);
		
		$prds = $this->mall_zxtg_model->get_prds($settings);
		
		$this->tpl->assign('module', 'designs.manage');
		$this->tpl->assign('prds', $prds);
		
		$this->tpl->display('admin/mall/zxtg/zxtg_designs.html');
		
	}
	
	public function design_active(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		// 编辑模式
		if( ! empty($id) ){
			$this->load->model('mall/mall_zxtg_model');
			$prd = $this->mall_zxtg_model->get_prd($id, 'id, name, image, link, description');
			$this->tpl->assign('prd', $prd);
		}
		
		$this->tpl->assign('rurl', $r);
		
		$this->tpl->assign('module', 'designs.manage');
		$this->tpl->display('admin/mall/zxtg/zxtg_design_active.html');
	}
	
	// 装修公司
	public function decos(){
		
		$this->load->model('mall/mall_zxtg_model');
		
		$settings = array(
			'type'=>'decos',
			'fields'=>'id, name, image, username, link'
		);
		
		$prds = $this->mall_zxtg_model->get_prds($settings);
		
		$this->tpl->assign('module', 'decos.manage');
		$this->tpl->assign('prds', $prds);
		
		$this->tpl->display('admin/mall/zxtg/zxtg_decos.html');
		
	}
	
	public function deco_active(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		// 编辑模式
		if( ! empty($id) ){
			$this->load->model('mall/mall_zxtg_model');
			$prd = $this->mall_zxtg_model->get_prd($id, 'id, name, image, link, username, description');
			$this->tpl->assign('prd', $prd);
		}
		
		$this->tpl->assign('rurl', $r);
		
		$this->tpl->assign('module', 'decos.manage');
		$this->tpl->display('admin/mall/zxtg/zxtg_deco_active.html');
	}
	
	// 检测装修公司用户名是否存在
	public function deco_exists(){
		$username = $this->gf('username');
		$this->load->model('company/company', 'deco_model');
		$deco = $this->deco_model->get_company($username, 'id');
		if( ! $deco ){
			echo(0);
		} else {
			echo($deco['id']);
		}
	}
	
	// 积分兑换
	public function exchange(){
		
		$this->load->model('mall/mall_zxtg_model');
		
		$settings = array(
			'type'=>'exchange',
			'fields'=>'id, name, image, score'
		);
		
		$prds = $this->mall_zxtg_model->get_prds($settings);
		
		$this->tpl->assign('module', 'exchange.manage');
		$this->tpl->assign('prds', $prds);
		
		$this->tpl->display('admin/mall/zxtg/zxtg_exchange.html');
		
	}
	
	public function exchange_active(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		// 编辑模式
		if( ! empty($id) ){
			$this->load->model('mall/mall_zxtg_model');
			$prd = $this->mall_zxtg_model->get_prd($id, 'id, name, image, link, score');
			$this->tpl->assign('prd', $prd);
		}
		
		$this->tpl->assign('rurl', $r);
		$this->tpl->assign('module', 'exchange.manage');
		$this->tpl->display('admin/mall/zxtg/zxtg_exchange_active.html');
		
	}
	
	// 赠品
	public function gift(){
		$this->load->model('mall/mall_zxtg_model');
		$settings = array(
			'type'=>'gift',
			'fields'=>'id, name, image'
		);
		$prds = $this->mall_zxtg_model->get_prds($settings);
		$this->tpl->assign('module', 'gift.manage');
		$this->tpl->assign('prds', $prds);
		$this->tpl->display('admin/mall/zxtg/zxtg_gift.html');
	}
	
	// 赠品
	public function gift_active(){
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		// 编辑模式
		if( ! empty($id) ){
			$this->load->model('mall/mall_zxtg_model');
			$prd = $this->mall_zxtg_model->get_prd($id, 'id, name, image');
			$this->tpl->assign('prd', $prd);
		}
		
		$this->tpl->assign('rurl', $r);
		$this->tpl->assign('module', 'gift.manage');
		$this->tpl->display('admin/mall/zxtg/zxtg_gift_active.html');
	}
	
}




























?>