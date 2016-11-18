<?php

class admin_budget_configure extends admin_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'configure');
	}
	
	public function manage(){
		
		$this->load->model('budget/budget_config_model');
		
		$id = $this->gr('id');
		if( empty($id) ) $id = 1;
		
		$roots = $this->budget_config_model->roots();
		$current = NULL;
		
		foreach($roots as $item){
			if( $item['id'] == $id ){
				$current = $item;
				break;
			}
		}
		
		$childs = $this->budget_config_model->childs($current['id'], array(
			'fields'=>'id, name, pid, recmd'
		));
		$childs = $this->budget_config_model->budget_count_assign($childs);
		
		$this->tpl->assign('roots', $roots);
		$this->tpl->assign('current', $current);
		$this->tpl->assign('childs', $childs);
		$this->tpl->display( $this->get_tpl('budget/budget_configure_manage.html') );
		
	}
	
	public function active(){
		
		// 获取父分类
		$pid = $this->gr('id');
		if( empty($pid) ) $pid = 0;
		
		$this->load->model('budget/budget_config_model');
		
		if( $pid == 0 ){
			$parent = array('id'=>0, 'name'=>'顶级分类');
		} else {
			$parent = $this->budget_config_model->get($pid, array(
				'fields'=>'id, name, recmd'
			));
		}
		
		$this->tpl->assign('rurl', $this->gr('r'));
		$this->tpl->assign('parent', $parent);
		
		$aid = $this->gr('aid');
		$attr = false;
		if( ! empty( $aid ) ){
			$attr = $this->budget_config_model->get($aid, array(
				'fields'=>'id, name, recmd'
			));
		}
		
		$this->tpl->assign('attr', $attr);
		$this->tpl->display( $this->get_tpl('budget/budget_configure_active.html') );
	}
	
	public function handler(){
		
		$info = $this->get_form_data();
		if( empty( $info['name'] ) ){
			exit('请输入名称');
		}
		if( ! isset( $info['recmd'] ) ) $info['recmd'] = 0;
		
		$this->load->model('budget/budget_config_model');
		
		try{
			
			if( $info['id'] == '' ){
				$this->budget_config_model->add($info);
			} else {
				$this->budget_config_model->edit($info);
			}
			
			$this->alert('', $this->gf('rurl'));
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
		
		
	}
	
	public function sorter(){
		
		$info = $this->get_form_data();
		$this->load->model('budget/budget_config_model');
		
		try{
			$this->budget_config_model->sorter($info);
			echo('success');
		}catch(Exception $e){
			echo($e->getMessage());
		}
		
	}

	// 分类删除
	public function delete(){
		
		$id = $this->gr('aid');
		$rurl = $this->gr('r');
		
		// $this->load->model('budget_model');
		$this->load->model('budget/budget_config_model');
		
		$attr = $this->budget_config_model->get($id);
		$attr = $this->budget_config_model->budget_count_assign($attr);
		
		if( $attr['budget_count'] != 0 ){
			exit('无法删除，分类下有（'. $attr['budget_count'] .'）个预算');
		}
		
		try{
			$this->budget_config_model->delete($id);
			$this->alert('', $rurl);
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
		
		
		
	}

























	
}

?>