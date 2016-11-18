<?php

class video_index extends video_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		
		$this->load->model('Shipin/ShipinModel', 'spm');
		
		$settings = array(
			'size'=>20,
			'page'=>$this->encode->get_page()
		);
		
		$res = $this->spm->getList($settings);
		
		$count = $res['count'];
		
		$this->load->library('pagination');
		
		$this->pagination->recordCount = $count;
		$this->pagination->pageSize = $settings['size'];
		$this->pagination->currentPage = $settings['page'];
		
		$urls = $this->config->item('url');
		
		$this->pagination->url_template = $urls['video'] . '?page=<{page}>';
		$this->pagination->url_template_first = $urls['video'];
		
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('list', $res['list']);
		$this->tpl->display('shipin/index.html');
			
	}
	
	
	
}

?>