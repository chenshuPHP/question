<?php

class admin_multi_contract extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 合同下载管理
	public function download(){
		
		$this->load->model('download/res_download_model');
		$this->load->model('download/res_download_config_model');
		$this->load->model('multi/contract_model');
		
		$sort = $this->encode->get_request_encode('s');
		
		$cfg = array(
			'page'=>$this->encode->get_page(),
			'size'=>20
		);
		
		$order = "order by addtime desc";
		
		if( $sort == 'atime' ){
			$order = "order by atime desc";
		}
		
		$where = "res_type = 'contract'";
		$sql = "select * from ( select id, name, mobile, budget_id, addtime, mobile_addr, atime, user_type, num = row_number() over(". $order .") from [budget_download] where ". $where ." ) as tmp where num between ". ( ($cfg['page'] - 1) * $cfg['size'] + 1 ) ." and " . ( $cfg['page'] * $cfg['size'] );
		$sql_count = "select count(*) as icount from [budget_download] where " . $where;
		
		$result = $this->res_download_model->get_list($sql, $sql_count);
		$result['list'] = $this->contract_model->assign($result['list']);

		$result['list'] = $this->res_download_config_model->assign($result['list']);		// 绑定用户类型
		
		// 回访状态
		$this->load->model('download/res_download_relation_model');
		$result['list'] = $this->res_download_relation_model->check_visit_assign($result['list']);
		
		echo('<!--');
		var_dump($result['list']);
		echo('-->');
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->pageSize = $cfg['size'];
		
		$this->pagination->url_template_first = $this->get_complete_url('multi/contract/download?s=' . $sort);
		$this->pagination->url_template = $this->get_complete_url('multi/contract/download?page=<{page}>&s=' . $sort);
		
		$pagination = $this->pagination->toString(true);
		$this->tpl->assign('pagination', $pagination);
		
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->display( $this->get_tpl('multi/contract/download.html') );
	}
	
	
}

?>