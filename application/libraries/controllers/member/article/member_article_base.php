<?php

// 会员中心 文章管理
class member_article_base extends member_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('module', 'article');
	}
	
}


?>