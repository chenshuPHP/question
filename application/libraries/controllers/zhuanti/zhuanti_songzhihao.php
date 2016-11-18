<?php

// 宋志浩 装修行业的“3.15标准”
// 2015-07-31
class zhuanti_songzhihao extends zhuanti_base {
	public function __construct(){
		parent::__construct();
	}
	
	public function handler(){
		
		$this->active->tpl->assign('title', '装修行业的“3.15标准”');
		
		$this->active->load->model('article/article', 'article_model');
		$arts = $this->active->article_model->getCustomNews("select top 12 id, title, clsid, addtime from art_art where contains((keyword, title), '宋志浩') order by id desc");
		
		
		// var_dump($arts);
		
		$this->active->tpl->assign('arts', $arts);
		
		
		$this->active->tpl_name = 'zhuanti/songzhihao/index.html';
		
	}
}


?>