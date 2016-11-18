<?php

// 企业招聘 管理
class member_recruit_manage extends member_recruit_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		$tpl = $this->get_tpl('recruit/manage.html');
		
		$this->load->model('company/hr', 'company_recruit_model');
		
		$where = "senduser = '". $this->base_user ."'";
		$sql = "select id, title, jobnum, iput, puttime, senduser, school, zhuanye ".
		"from [". $this->company_recruit_model->table_name ."] where ". $where ." order by puttime desc";
		$sql_count = "select count(*) as icount from [". $this->company_recruit_model->table_name ."] where " . $where;
		$result = $this->company_recruit_model->gets($sql, $sql_count);
		
		var_dump2( $result );
		
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('count', $result['count']);
		$this->tpl->display( $tpl );
	}
	
}

?>