<?php

class admin_sys_admin_module extends admin_base {
	
	public function __construct(){
		parent::__construct();
		$this->load->model('manager/manager_module_model');
	}
	
	public function manage(){
		
		$modules = $this->manager_module_model->get_all(array(
			'fields'=>'name, label, fenzhan'
		));
		$this->tpl->assign('modules', $modules);
		$this->tpl->display( $this->get_tpl('sys_admin/module/manage.html') ); 
		
	}
	
	// 编辑模式
	public function active(){
		
		$rurl = $this->gr('r');
		$label = $this->gr('label');
		
		$module = $this->manager_module_model->get_module($label);
		
		$this->tpl->assign('module', $module);
		$this->tpl->assign('rurl', $rurl);
		$this->tpl->display( $this->get_tpl('sys_admin/module/active.html') );
		
	}
	
	// 提交处理
	public function handler(){
		
		$info = $this->get_form_data();
		
		try{
			$this->manager_module_model->edit($info);
			$this->alert('修改完成', $info['rurl']);
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
		
		
	}
	
	
}

?>