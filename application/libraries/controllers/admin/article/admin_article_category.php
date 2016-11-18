<?php

class admin_article_category extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 资讯分类管理
	public function manage(){
		
		$cfg = array(
			'id'=>$this->gr('id')
		);
		if( ! preg_match('/^\d+$/', $cfg['id']) ) $cfg['id'] = 1;
		
		$this->load->model('article/category');
		$roots = $this->category->getChannels();
		$childs = $this->category->getChilds($cfg['id']);
		
		foreach($childs as $key=>$val){
			$val['childs'] = $this->category->getChilds($val['id']);
			if( count($val['childs']) == 0 ) $val['childs'] = false;
			$childs[$key] = $val;
		}
		
		
		
		$this->tpl->assign('roots', $roots);
		$this->tpl->assign('childs', $childs);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->display('admin/article/channel_manage.html');
		
	}
	
}

?>