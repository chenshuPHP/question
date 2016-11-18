<?php

// 产品分类管理控制器
class admin_mall_cate extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 类目总管理界面
	public function manage(){
		$this->load->model('mall/mall_category_model');
		$this->load->model('mall/mall_attr_model');
		
		$cat_data = $this->mall_category_model->get_all();
		$cat_data = $this->mall_category_model->prd_count_assign($cat_data);
		$cat_data = $this->mall_attr_model->assign_count($cat_data);
		
		//echo('<!--');
		//var_dump($cat_data);
		//echo('-->');
		
		$cats = $this->mall_category_model->get_status(array('data'=>$cat_data));
		unset($cat_data);
		
		$this->tpl->assign('cats', $cats);
		$this->tpl->assign('module', 'cate.manage');
		$this->tpl->display('admin/mall/cate_manage.html');
	}
	
	// 类目修改界面
	public function edit(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		$this->load->model('mall/mall_category_model');
		$cat = $this->mall_category_model->get_category($id);
		$cat['parent'] = $this->mall_category_model->get_category($cat['pid']);
		
		$this->tpl->assign('cat', $cat);
		$this->tpl->assign('rurl', $r);
		
		// 分类子属性数据
		$this->load->model('mall/mall_attr_model');
		$attrs = $this->mall_attr_model->get_cat_attrs( array('cid'=>$id) );
		$attrs = $this->mall_attr_model->childs_assign($attrs);
		$this->tpl->assign('attrs', $attrs);
		
		$parents = $this->mall_category_model->get_roots(array(
			'disabled'=>false
		));
		
		$this->tpl->assign('parents', $parents);
		$this->tpl->assign('module', 'cate.edit');
		$this->tpl->display('admin/mall/cate_edit.html');
		
	}
	
	// 类目修改提交
	public function edit_submit(){
		
		$this->load->model('mall/mall_category_model');
		
		$r = $this->gf('r');
		$cat = array(
			'id'=>$this->gf('id'),
			'pid'=>$this->gf('pid'),			// 根据pid判断在什么表
			'name'=>$this->gf('cate_name'),
			'disabled'=>$this->gf('disabled')	// 是否禁用分类
		);
		
		try{
			
			$this->mall_category_model->edit($cat);
			
			// 2015-07-22 新增 子分类你属于大分类的修改程序
			$p_cat_id = $this->gf('p_cat_id');
			if( ! empty( $p_cat_id ) && $p_cat_id != $cat['pid'] && $cat['pid'] != 0 ){
				$this->mall_category_model->parent_change($cat['id'], $p_cat_id);
			}
			
			$this->alert('修改成功', $r);
		}catch(Exception $e){
			$this->alert( $e->getMessage() );
		}
	}
	
	// 类目提交界面
	public function add(){
		$pid = $this->gr('pid');
		$r = $this->gr('r');
		$this->load->model('mall/mall_category_model');
		$parent = false;
		if( $pid != 0 ){
			$parent = $this->mall_category_model->get_category($pid);
		}
		$this->tpl->assign('parent', $parent);
		$this->tpl->assign('rurl', $r);
		$this->tpl->assign('module', 'cate.add');
		$this->tpl->display('admin/mall/cate_add.html');
	}
	
	// 类目提交表单处理
	public function add_submit(){
		$r = $this->gf('r');
		$cat = array(
			'pid'=>$this->gf('pid'),
			'name'=>$this->gf('cate_name'),
			'disabled'=>$this->gf('disabled')
		);
		
		if( empty( $cat['name'] ) ){
			$this->alert('分类名称不能为空');
		}
		
		$this->load->model('mall/mall_category_model');
		try{
			$this->mall_category_model->add($cat);
			$this->alert('添加成功', $r);
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
	}
	
	// 删除分类
	public function delete(){
		$id = $this->gr('id');
		$pid = $this->gr('pid');
		$r = $this->gr('r');
		
		$this->load->model('mall/mall_category_model');
		
		try{
			$this->mall_category_model->delete($pid, $id);
			$this->alert('', $r);
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
	}
	
	// 分类排序
	public function sortable(){
		$pid = $this->gf('pid');
		$sortable = $this->gf('sortable');
		$this->load->model('mall/mall_category_model');
		try{
			$this->mall_category_model->sortable($pid, $sortable);
		}catch(Exception $e){
			echo($e->getMessage());
		}
	}
	
}





















?>