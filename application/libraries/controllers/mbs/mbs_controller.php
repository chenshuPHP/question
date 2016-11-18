<?php

// 微网页报名数据查看 控制器
class mbs_controller extends my_controller {

	public function __construct(){
		parent::__construct();
	}

	public function _remap($class, $args = array()){
		$this->route($class, $args, 'mbs');
	}

}

?>