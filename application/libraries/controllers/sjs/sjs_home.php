<?php

// 设计师首页
class sjs_home extends sjs_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		
		// 悬赏数据读取
		$this->load->model('sjs/sjs_zhaobiao_model');
		$this->load->model('sjs/sjs_zhaobiao_config_model');
		
		$zhaobiao = $this->sjs_zhaobiao_model->gets("select top 3 id, username, type, pay_type, cash_value, score_value, fm, addtime from [sjs_zhaobiao] where recycle = 0 order by addtime desc", '', array(
			'format'=>true
		));
		
		$zhaobiao = $this->sjs_zhaobiao_config_model->assign_map($zhaobiao['list'], array('types', 'pay_types'));
		// var_dump( $zhaobiao );
		
		$this->tpl->assign('zhaobiao', $zhaobiao);
		$this->tpl->display('sjs/home.html');
	}
	
}

?>