<?php

// 客服代表
class publish_user extends publish_base {

	public function __construct(){
		parent::__construct();
	}
	
	// 获取客服代表信息
	public function get_info(){

		$aid = $this->gf('aid');

		$this->load->model('manager/manager_model');

		$info = $this->manager_model->get_manager($aid, 'id, username, fullname');

		echo(json_encode($info));

	}

}


?>