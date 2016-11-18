<?php

// 资讯频道基类
class article_base extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->load->library('tpl');
	}
	
}

?>