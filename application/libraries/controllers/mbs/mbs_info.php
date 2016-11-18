<?php

// 修改密码
class mbs_info extends mbs_base {

	public function __construct(){
		parent::__construct();
	}

	// 修改密码
	public function pswd(){
		$this->tpl->assign('module', 'info.pswd');
		$this->tpl->display('mbs/info.pswd.html');
	}
	
	// 修改密码提交
	public function pswd_handler(){
		$info = array(
			'page_id'=>$this->gf('page_id'),
			'pswd'=>$this->gf('pswd')
		);
		if( $info['page_id'] != $this->admin['page_id'] ){
			exit('用户不匹配');
		}
		
		$this->load->model('mobile/mobile_mall_model');
		
		try{
			$this->mobile_mall_model->edit_mbs_pswd($info);		// 修改密码
			$this->alert('修改成功', $this->get_mbs_complete_url('/info/pswd'));
		}catch(Exception $e){
			$this->alert('失败' . $e->getMessage());
		}
		
		
	}

}

?>