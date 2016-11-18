<?php

class admin_sjs_project_config extends admin_base {
	
	public function __construct(){
		parent::__construct();
		$this->load->model('sjs/sjs_case_config_model');
		$this->tpl->assign('module', 'category.config');
	}
	
	public function manage(){
		
		$rid = $this->gr('rid');
		if( $rid == '' ) $rid = 2;
		
		$child_mode = false;	// 是否有二级分类
		if( $rid == 2 ) $child_mode = true;	
		
		$class = $this->sjs_case_config_model->get($rid);
		$class = $this->sjs_case_config_model->assign_childs($class);
		
		if( $child_mode == true ) $class = $this->sjs_case_config_model->assign_childs_childs($class);
		
		$this->tpl->assign('roots', $this->sjs_case_config_model->get_roots());
		$this->tpl->assign('child_mode', $child_mode);
		$this->tpl->assign('rid', $rid);
		$this->tpl->assign('class', $class);
		
		$this->tpl->display( $this->get_tpl('sjs/project/config_manage.html') );
	}
	
	public function active(){
		
		$rid = $this->gr('rid');
		$pid = $this->gr('pid');
		$rurl = $this->gr('r');
		$id = $this->gr('id');
		
		$pclass = $this->sjs_case_config_model->get($pid);
		
		
		if( $id != '' ){
			$class = $this->sjs_case_config_model->get($id);
			$this->tpl->assign('class', $class);
		}
		
		
		$this->tpl->assign('rid', $rid);
		$this->tpl->assign('pclass', $pclass);
		$this->tpl->assign('roots', $this->sjs_case_config_model->get_roots());
		$this->tpl->assign('rurl', $rurl);
		$this->tpl->display( $this->get_tpl('sjs/project/config_manage_add.html') );
		
	}
	
	public function handler(){
		
		$info = $this->get_form_data();
		
		try{
			if( $info['id'] == 0 ) {
				// 添加
				$this->sjs_case_config_model->add($info);
			} else {
				// 修改
				$this->sjs_case_config_model->edit($info);
			}
			
			$this->alert('', $info['rurl']);
			
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
		
	}
	
	
}































?>