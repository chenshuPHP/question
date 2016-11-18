<?php

// 企业证书 by 袁仙增
class sp_cert extends sp_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home( $args = array() )
	{
		$this->load->library('thumb');
		$this->load->model('company/zizhi', 'zizhi_model');
		$list = $this->zizhi_model->get_list(array(
			'username'=>$this->user
		));
		foreach($list as $key=>$val){
			$val['thumb'] = $this->thumb->crop($val['imgpath'], 156, 118);
			$list[$key] = $val;
		}
		$this->tpl->assign('list', $list);
		
		//var_dump( $list );
		$this->tpl->assign('module', 'cert');
		$this->tpl->display( $this->get_tpl('cert.html') );
	}

}
?>