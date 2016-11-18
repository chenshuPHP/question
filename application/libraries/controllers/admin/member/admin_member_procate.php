<?php

// 案例分类配置
class admin_member_procate extends admin_base {
	
	public function __construct(){
		parent::__construct();
		$this->load->model('company/project_category_model');
	}
	
	public function manage(){
		
		$this->tpl->assign('module', 'manage');
		
		$roots = $this->project_category_model->roots();
		
		$pid = $this->gr('id');
		if( empty($pid) ) $pid = 1;
		
		$attr = $this->project_category_model->attr($pid);
		
		// 获取一级分类
		$childs = $this->project_category_model->childs($attr['id']);	// 获取子分类
		
		// 如果是房屋类型，则加载子分类
		if( $attr['type'] == 1 ){
			$childs = $this->project_category_model->childs_assign($childs);
		}
		
		//echo('<!--');
		//var_dump($childs);
		//echo('-->');
		
		$this->tpl->assign('roots', $roots);
		$this->tpl->assign('attr', $attr);
		$this->tpl->assign('childs', $childs);
		
		$this->tpl->display( $this->get_tpl('member/project/cate_manage.html') );
		
	}
	
	public function add(){
		
		$info = array(
			'pid'=>$this->gf('pid'),
			'name'=>$this->gf('name')
		);
		
		try{
			$this->project_category_model->add($info);
			echo('success');
		}catch(Exception $e){
			echo( $e->getMessage() );
		}
		
	}
	
	public function edit(){
	
		$info = $this->get_form_data();
	
		//var_dump($info);
		//exit();
	
		if( empty($info['name']) ) exit('不允许空的分类名称');
	
		try{
			
			$this->project_category_model->edit($info);
			
			if( $this->gf('post_type') == 'form' ){
				$this->alert('提交成功', $this->gf('r'));
			} else {
				echo('success');
			}
			
		}catch(Exception $e){
			echo( $e->getMessage() );
		}
	}
	
	public function delete(){
		$id = $this->gf('id');
		$childs = $this->project_category_model->childs($id, array('size'=>1, 'fields'=>'id'));
		if( count($childs) > 0 ){
			exit('有子分类存在，无法删除');
		}
		
		// 是否验证绑定案例后无法删除，这里不验证
		try{
			$this->project_category_model->delete($id);
			echo('success');
		}catch(Exception $e){
			echo( $e->getMessage() );
		}
	}
	
	// 详细编辑
	public function edit_detail(){
		
		$this->load->model('company/project_category_model');
		
		$id = $this->gr('id');
		$rurl = $this->gr('r');
		
		$cate = $this->project_category_model->get($id, array(
			'fields'=>'id, name, rmd'
		));
		
		// var_dump($cate);
		$this->tpl->assign('cate', $cate);
		$this->tpl->assign('rurl', $rurl);
		$this->tpl->display( $this->get_tpl('member/project/cate_edit_detail.html') );
		
	}
	
}

?>