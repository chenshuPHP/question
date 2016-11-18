<?php

// 装修公司 新闻 集中展示 
class news_controller extends MY_Controller {
	
	public function _remap($method, $params = array()){
		
		if( method_exists($this, strtolower($method)) ){
			$this->$method();
		} else {
			show_404();
		}
		
	}
	
	private function get_tpl($tpl){
		return 'company/news/' . ltrim($tpl, '/');
	}
	
	public function home(){
		
		$this->load->model('company/usernews', 'deco_article_model');
		$this->load->model('company/company', 'deco_model');
		
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>32
		);
		
		$where = "username in ( select username from company where register = 2 and hangye = '装潢公司' and delcode = 0 ) and recycle = 0";
		
		$sql = "select * from ( select id, title, username, addtime, num = row_number() over(order by addtime desc) from [user_news] where ". $where ." ) as temp where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['size'] * $args['page'] );
		$sql_count = "select count(*) as icount from [user_news] where " . $where;
		
		$result = $this->deco_article_model->gets($sql, $sql_count);
		$result['list'] = $this->deco_model->fill_collection($result['list'], array(
			'fields'=>array('username', 'company')
		), false);
		
		$this->load->library('pagination');
		$this->pagination->pageSize = $args['size'];
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $result['count'];
		
		$urls = $this->config->item('url');
		
		$this->pagination->url_template = $urls['www'] . '/mnews/home?page=<{page}>';
		$this->pagination->url_template_first = $urls['www'] . '/mnews/home';
		
		$sql = "select top 20 username, company, shortname from company where flag = 2 and hangye = '装潢公司' and delcode = 0 order by koubei desc";
		$decos = $this->deco_model->get_list($sql, '', true);
		$decos = $decos['list'];
		
		$this->tpl->assign('decos', $decos);
		$this->tpl->assign('pagination', $this->pagination->toString(true));
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('title', '装修公司-新闻动态');
		$this->tpl->display( $this->get_tpl('home.html') );
		
	}
	
	
	
	
	
	
	
	
}



