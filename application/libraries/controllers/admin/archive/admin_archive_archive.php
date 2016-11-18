<?php

// 施工档案创建
// 施工档案包括（日记档案，参与人员）
// 参与人员包括（施工团队，设计师）注：以后可能包括造价员等

class admin_archive_archive extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 施工档案列表管理
	public function manage(){
		
		$this->load->model('archive/archive_diary_model');	// 档案日记模型
		
		$this->load->library('pagination');
		
		$args = array(
			'page'=>$this->gr('page'),
			'size'=>20,
			'fields'=>'id, title, town, deco_name, address, addtime, admin',
			'record_count'=>0
		);
		if( ! preg_match('/^[1-9]\d*$/', $args['page']) ) $args['page'] = 1;
		
		$result = $this->archive_diary_model->get_diarys($args);
		
		$this->pagination->currentPage = $args['page'];
		$this->pagination->pageCount = $this->pagination->getPageCount($args['size'], $result->count);
		$this->pagination->url_template_first = $this->manage_url . 'archive/archive/manage';
		$this->pagination->url_template = $this->manage_url . 'archive/archive/manage?page=<{page}>';
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('list', $result->list);
		$this->tpl->assign('args', $args);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('module', 'manage');
		$this->tpl->display('admin/archive/archive_manage.html');
		
	}
	
	
	
	
	
	
}


?>