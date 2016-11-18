<?php

class blog_case extends blog_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function view($args = array()){
		
		$id = $args[0];
		
		$this->load->model('sjs/sjs_case');
		$this->load->model('sjs/sjs_case_config_model');
		
		$pro = $this->sjs_case->get($id, array(
			'add_fields'=>'show_count'
		));
		$pro = $this->sjs_case_config_model->assign_config($pro);
		$this->sjs_case->image_assign($pro);
		$pro['detail'] = $this->encode->htmldecode($pro['detail'], true);
		
		echo('<!--');
		var_dump($this->info);
		var_dump($pro);
		echo('-->');
		
		$abts = $this->sjs_case->gets("select top 5 id, username, case_name, fm, addtime from [sjs_case] where username = '". $this->blog_user ."' and id <>
		 '". $id ."' and fm <> '' order by addtime desc");
		 
		$this->tpl->assign('abts', $abts['list']);
		$this->tpl->assign('pro', $pro);
		$this->tpl->assign('title', $pro['case_name'] . '-' . $this->info['true_name']);
		$this->tpl->display('sjs/blog/case_view.html');
		
	}
	
	
}

?>