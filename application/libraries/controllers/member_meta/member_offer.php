<?php


// 产品操作相关控制器

class member_offer extends member_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function pub_attr(){
		$this->pub('', 'member_meta/offer/pub4.html');
	}
	
	// 产品添加&修改
	public function pub($id = '', $tpl = ''){
		
		if( empty($id) ) $id = $this->gr('id');
		
		// 加载分类模型
		$this->load->model('mall/mall_category_model');
		$categories = $this->mall_category_model->get_status(
			array('disabled_opt'=>true)	// 不加载被禁用了的分类
		);
		
		if( ! empty($id) ){
			
			$this->load->model('mall/mall_product_model');
			
			$prd = $this->mall_product_model->get_product($id, 'id, smallclass, prdclass, brandid, title, price, webprice, imgpath, content', $this->info['username']);
			$prd['attrs'] = $this->mall_product_model->get_prd_attr($id);
			
			if( !$prd ) exit('您没有权限修改本条数据');
			
			$prd['thumbs'] = $this->mall_product_model->get_thumbs($prd['id']);
			
			$this->tpl->assign('object', $prd);
			
		}
		
		$this->tpl->assign('categories', $categories);
		$this->tpl->assign('module', 'offer.pub');
		$this->tpl->display(empty($tpl) ? 'member_meta/offer/pub.html' : $tpl);
	}
	
	// ajax 分类获取
	public function get_brand(){
		$id = $this->gf('cid');
		$this->load->model('mall/mall_brand_model');
		$brds = $this->mall_brand_model->get_brands(0, $id);
		echo( json_encode($brds) );
	}
	
	// ajax 获取分类属性
	public function get_cat_attr(){
		
		$cid = $this->gf('cid');
		$result = array();
		// 品牌数据
		$this->load->model('mall/mall_brand_model');
		$this->load->model('mall/mall_attr_model');
		$brds = $this->mall_brand_model->get_brands(0, $cid);
		
		$attrs = $this->mall_attr_model->get_cat_attrs(array(
			'cid'=>$cid
		));
		$attrs = $this->mall_attr_model->childs_assign($attrs);
		
		//var_dump($attrs);
		
		$result['brand'] = $brds;
		$result['attrs'] = $attrs;
		
		echo( json_encode( $result ) );
		
	}


	// 产品发布表单提交 == 多缩略图
	public function pub_handler(){
		
		$prd = array(
			'id'=>$this->gf('id'),
			'smallclass'=>$this->gf('smallclass'),
			'prdclass'=>$this->gf('prdclass'),
			'brandid'=>$this->gf('brand'),
			'title'=>$this->gf('prd_name'),
			'price'=>$this->gf('price'),
			'webprice'=>$this->gf('webprice'),
			'content'=>$this->gf('editor_content'),
			'senduser'=>$this->info['username'],
			
			'attrs'=>$this->gf('attrs')
			
			,'thumbs'=>$this->gf('thumb')		// 缩略图
		);
		
		$prd['attrs'] = $this->encode->htmldecode( $prd['attrs'] );
		$prd['attrs'] = json_decode( $prd['attrs'] );
		
		$errors = array();
		if( empty($prd['smallclass']) || empty($prd['prdclass']) ){
			$errors['cat'] = '请选择分类';
		}
		if( empty( $prd['title'] ) ){
			$errors['title'] = '您没有填写产品标题';
		}
		if( preg_match('/^\d+(\.\d+)?$/', $prd['price']) == false || preg_match('/^\d+(\.\d+)?$/', $prd['webprice']) == false ){
			$errors['price'] = '产品价格有误';
		}
		
		if( empty( $prd['thumbs'] ) ){
			$errors['thumbs'] = '请上传产品缩略图';
		}
		
		if( empty( $prd['senduser'] ) ){
			$errors['senduser'] = '没有找到用户';
		}
		
		if( count( $errors ) == 0 ){
			
			$this->load->model('mall/mall_product_model');
			
			try{
				
				if( empty( $prd['id'] ) ){
					$id = $this->mall_product_model->add($prd);	// 增加
				} else {
					$this->mall_product_model->edit($prd);		// 修改
				}
				
			}catch(Exception $e){
				$errors['handler'] = $e->getMessage();
			}
		}
		
		if( count( $errors ) > 0 ){
			$this->tpl->assign('errors', $errors);
			$this->tpl->assign('object', $prd);
			$this->pub();
		} else {
			$this->alert('提交成功', '/member/offer/product_list.asp');
		}
	}








}

?>