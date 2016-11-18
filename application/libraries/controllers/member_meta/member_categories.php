<?php

// 商铺自定义分类 管理控制器

class member_categories extends member_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 分类管理
	public function manage(){
		$this->load->model('mall/mall_usercat_model');
		$categories = $this->mall_usercat_model->get_structs($this->info['username']);
		
		//var_dump($categories);
		
		$categories = $this->mall_usercat_model->assign_prd_count($categories);
		
		$this->tpl->assign('cats', $categories);
		$this->tpl->assign('module', 'categories');
		$this->tpl->assign('sub_module', 'manage');
		$this->tpl->display('member_meta/categories/manage.html');
	}
	
	// 产品归纳
	public function induce(){
		
		$this->load->model('mall/mall_usercat_model');
		$this->load->model('mall/mall_product_model');
		$this->load->model('mall/mall_ucat_rel_model');
		
		$categories = $this->mall_usercat_model->get_structs($this->info['username']);
		
		$args = array(
			'size'=>20,
			'page'=>$this->encode->get_page()
		);
		
		$sql = "select * from ( select id, title, senduser, imgpath, price, webprice, num = row_number() over(order by puttime desc) from sendbuy where senduser = '". $this->info['username'] ."' ) as temp where num between ". (($args['page'] - 1) * $args['size'] + 1) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from sendbuy where senduser = '". $this->info['username'] ."'";
		$result = $this->mall_product_model->get_list($sql, $sql_count);
		
		$list = $result['list'];
		
		$list = $this->mall_ucat_rel_model->assign_rels($list);	// 分配关系到列表

		$this->tpl->assign('list', $list);
		$this->tpl->assign('module', 'categories');
		$this->tpl->assign('categories', $categories);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->url_template = $this->member_url . 'categories/induce?page=<{page}>';
		$this->pagination->url_template_first = $this->member_url . 'categories/induce';
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('sub_module', 'induce');
		$this->tpl->assign('args', $args);
		$this->tpl->display('member_meta/categories/induce.html');
	}
	
	// 分类变更提交处理
	public function settings(){
		
		$data = $this->gf('data');
		$cats = array();
		if( empty($data) ){
			$data = array();
		}
		foreach($data as $key=>$value){
			$temp = array();
			$temp['id'] = $value[0];
			$temp['name'] = $value[1];
			$temp['childs'] = array();
			
			if( isset($value[2]) ){
				foreach($value[2] as $k=>$v){
					$temp['childs'][] = array('id'=>$v[0], 'name'=>$v[1]);
				}
			}
			$cats[] = $temp;
		}
		unset($data);
		
		$this->load->model('mall/mall_usercat_model');
		
		try{
			$this->mall_usercat_model->edit($cats, $this->info['username']);
			echo('success');
		}catch(Exception $e){
			echo($e->getMessage());
		}
		
	}
	
	// 
	public function bind(){
		$pid = $this->gr('pid');
		$this->load->model('mall/mall_product_model');
		$this->load->model('mall/mall_usercat_model');
		$this->load->model('mall/mall_ucat_rel_model');
		$categories = $this->mall_usercat_model->get_structs($this->info['username']);
		$prd = $this->mall_product_model->get_product($pid, 'id, title, imgpath', $this->info['username']);
		
		$rels = $this->mall_ucat_rel_model->get_prd_ucat_id($pid);
		
		foreach($categories as $key=>$value){
			$value['checked'] = false;
			foreach($rels['pids'] as $id){
				if( $value['id'] == $id ){
					$value['checked'] = true;
					break;
				}
			}
			foreach($value['childs'] as $k=>$v){
				$value['childs'][$k]['checked'] = false;
				foreach($rels['cids'] as $id){
					if( $v['id'] == $id ){
						$value['childs'][$k]['checked'] = true;
						break;
					}
				}
			}
			$categories[$key] = $value;
		}
		
		$this->tpl->assign('prd', $prd);
		$this->tpl->assign('categories', $categories);
		$this->tpl->assign('rels', $rels);
		$this->tpl->display('member_meta/categories/bind.html');
	}
	
	public function bind_handler(){
		
		$data = array(
			'prd_id'=>$this->gf('pid'),
			'pids'=>$this->gf('pids'),
			'cids'=>$this->gf('cids')
		);
		
		$this->load->model('mall/mall_product_model');
		$prd = $this->mall_product_model->get_product($data['prd_id'], 'id', $this->info['username']);
		
		if( ! $prd ) exit('产品ID错误');
		$this->load->model('mall/mall_ucat_rel_model');
		
		$data['pids'] = explode(',', $data['pids']);
		$data['cids'] = explode(',', $data['cids']);
		
		try{
			
			$this->mall_ucat_rel_model->set_rels($data);
			echo(0);
		}catch(Exception $e){
			echo($e->getMessage());
		}
		
		
	}
	
	// 解除绑定 单一的
	public function unbind(){
		$cid = $this->gf('cid');
		$pid = $this->gf('pid');
		$type = $this->gf('type');
		$this->load->model('mall/mall_product_model');
		$prd = $this->mall_product_model->get_product($pid, 'id', $this->info['username']);
		if( ! $pid ) exit('产品ID错误');
		$this->load->model('mall/mall_ucat_rel_model');
		try{
			$this->mall_ucat_rel_model->unbind($pid, $cid, $type);
			echo(0);
		}catch(Exception $e){
			echo($e->getMessage());
		}
	}
	
	public function batch_active(){
		
		$ids = $this->gf('ids');
		$active = $this->gf('active');
		$ucat_id = $this->gf('tid');
		$type = $this->gf('type');	// 分类类型
		
		if( $type != '1' && $type != '2' ){
			exit('目标分类类型错误');
		}
		
		$this->load->model('mall/mall_ucat_rel_model');
		$this->load->model('mall/mall_product_model');
		
		$ids = explode(',', $ids);
		
		// 防止提交非自己产品
		$prds = $this->mall_product_model->get_list("select id from sendbuy where id in (". implode(',', $ids) .") and senduser = '". $this->info['username'] ."'");
		$ids = $this->mall_product_model->get_id_array($prds['list']);
		
		//var_dump($ids);
		//exit();
		// 批量添加
		if( $active == 'add' ){
			$this->mall_ucat_rel_model->prds_rel_add($ids, $ucat_id, $type);
		} elseif ( $active == 'move' ) {	// 批量移动
			$this->mall_ucat_rel_model->prds_rel_move($ids, $ucat_id, $type);
		}
		
	}
	
}

?>