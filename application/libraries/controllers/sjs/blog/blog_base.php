<?php

// BLOG 基类
class blog_base extends sjs_base {
	
	public $blog_user = '';
	public $info = NULL;
	
	public function __construct(){
		parent::__construct();
	}
	
	public function init(){
		
		$this->load->model('sjs/sjs_user_model');
		$this->load->model('sjs/sjs_config_model');
		
		$info = $this->sjs_user_model->get($this->blog_user, array(
			'fields'=>'username, true_name, face_image, field, oth_field, wyear, lilun'
		));
		$info = $this->sjs_config_model->assign_field($info);
		$info = $this->sjs_config_model->assign_wyear($info);
		
		
		$this->info = $info;
		
		// seo
		$this->tpl->assign('title', $info['true_name'] . '-设计博客');
		
		$this->tpl->assign('info', $this->info);
		
	}
	
}


?>