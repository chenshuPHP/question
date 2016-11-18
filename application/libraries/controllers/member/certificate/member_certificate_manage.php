<?php

// 资质证书管理
class member_certificate_manage extends member_certificate_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		$tpl = $this->get_tpl( 'certificate/manage.html' );
		
		$this->load->model('company/zizhi', 'company_certificate_model');
		
		$where = "senduser = '". $this->base_user ."'";
		$sql = "select id, imgpath as path, senduser, zizhiname as name ".
		"from [". $this->company_certificate_model->table_name ."] where " . $where . " order by sortid asc";
		$res = $this->company_certificate_model->gets($sql, '', array(
			'format'			=> TRUE
		));
		
		var_dump2( $res );
		
		$this->tpl->assign('list', $res['list']);
		$this->tpl->assign('count', $res['count']);
		$this->tpl->display( $tpl );
	}
	
}


?>