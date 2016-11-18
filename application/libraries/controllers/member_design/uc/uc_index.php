<?php

// 设计师用户管理中心主页
class uc_index extends uc_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		
		$sjs = $this->infomation->getInfomation($this->info['username'], "id, username, face_image");
		$this->tpl->assign('sjs', $sjs);
		$this->tpl->display( $this->get_tpl('home.html') );
		
	}
	
	
}

?>