<?php

// 装修保障

class multi_index extends multi_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		
		$this->load->model('article/article', 'article_model');
		$arts = $this->article_model->getCustomNews("select top 4 id, title, clsid, description, addtime from art_art where contains((keyword, title), '宋志浩') order by id desc");
		
		//echo('<!--');
		//var_dump($arts);
		//echo('-->');
		
		$this->tpl->assign('arts', $arts);
		$this->tpl->display('multi/home.html');
		
	} 
	
	
}

?>