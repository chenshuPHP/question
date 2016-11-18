<?php
	
	class ArticleBaseController extends CI_Controller {
		
		function __construct(){
			
			parent::__construct();
			
			$this->load->library('tpl');
			$this->tpl->assign('article_search_url', '/search/app.asp?t=art&key=');
			// 在资讯频道, 关闭了错误报告
			error_reporting(0);
			
		}
		
	}


?>