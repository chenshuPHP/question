<?php

if( ! defined('BASEPATH') ) exit('禁止直接浏览');

class bespeak_base extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('hide_kf', '1');
		$this->tpl->assign('display_head_ban', '0');
		$this->tpl->assign('title', '预约');
	}
	
}

?>