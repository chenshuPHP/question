<?php

class zhuanti_common extends zhuanti_base {
	
	public function __construct(){}
	
	public function dsdcb(){
		$this->active->load->model('publish/PubModel', 'pub');
		// 获取预约总和
		$count = $this->active->pub->get_deco_count();
		$count = str_split($count, 1);
		$this->active->tpl->assign('count', $count);
	}
	
}
?>