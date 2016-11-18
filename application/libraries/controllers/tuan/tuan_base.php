<?php

if ( ! defined('BASEPATH') ) exit('tuan_base 禁止直接浏览');

class tuan_base extends MY_Controller {
	
	public $mall_url = NULL;
	
	public function __construct() {
		parent::__construct();
		//$this->tpl->assign('hide_kf', '1');
		$this->mall_url = 'http://mall.shzh.net/';
	}
	
	// 获取商铺用户名
	protected function get_shop_username(){
		
		$username = $this->gr('u');
		
		if( empty($username) ){
			show_404();
			exit();
		}
		
		$username = strtolower($username);
		
		return $username;
		
	}
	
}

?>