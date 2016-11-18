<?php

// 企业店铺 文章
class sp_article extends sp_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'news');
	}
	
	public function home( $args = array() )
	{
		$this->load->model('company/usernews', 'news_model');
		
		$sql = "select id, title, addtime, username ".
		"from user_news where username = '". $this->user ."' and recycle = 0 order by addtime desc";
		$result = $this->news_model->gets($sql, '');
		
		
		$this->tpl->assign('list', $result['list']);
		
		$this->tpl->assign('module', 'news');
		$this->tpl->display( $this->get_tpl('news.html') );
	}
	
	public function detail($args = array()){
		$id = $args[0];
		if( ! is_numeric( $id ) )
		{
			show_404();
		}
		$this->load->model('company/usernews', 'news_model');
		$article = $this->news_model->get_new($id, array(
			'fields'		=> 'id, title, addtime, username, base_showcount, showcount, detail, recycle',
			'username'		=> $this->user,
			'format'		=> TRUE
		));
		if( ! $article ) show_404();
		$this->load->library('encode');
		$article['detail'] = $this->encode->htmldecode($article['detail']);
		
		$this->tpl->assign('new_object', $article);
		$this->tpl->assign('title', $article['title'] . '-' . $this->infomation['company']);
		$this->tpl->display( $this->get_tpl('news_show.html') );
	}
	
	// 浏览记录
	// 2分钟后访问才会更新一次
	public function viewed(){
		
		$id = $this->gf('id');
		if( preg_match('/^\d+$/', $id) ){
			$this->load->model('company/usernews', 'news_model');
			try{
				$this->news_model->viewed($id);
				echo('success');
			}catch(Exception $e){
				echo($e->getMessage());
			}
		} else {
			show_404();
		}
	}
	
	
}

?>