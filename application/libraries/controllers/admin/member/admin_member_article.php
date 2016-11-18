<?php

// 会员文章管理
// 2015-07-21
class admin_member_article extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function manage(){
		
		$this->load->model('company/usernews', 'deco_article_model');
		$this->load->model('company/company', 'deco_model');
		
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>20
		);
		
		$so = array(
			'key'		=> $this->gr('key')
		);
		
		$where = 'username in ( select username from company where delcode = 0 and register = 2 )';
		$params = array();
		if( ! empty( $so['key'] ) )
		{
			$so['key'] = $this->encode->gbk_to_utf8($so['key']);
			$where .= " and title like '%". $so['key'] ."%'";
			$params[] = "key=" . $so['key'];
		}
		
		// 2015-07-21
		
		$sql = "select * from ( select id, title, addtime, username, recycle, showcount, base_showcount, num = row_number() over(order by addtime desc) from [user_news] ".
		"where ". $where ." ) as temp where num between ". ( ($args['page'] - 1) * $args['size'] ) ." and " . ( $args['size'] * $args['page'] );
		$sql_count = "select count(*) as icount from [user_news] where " . $where;
		$result = $this->deco_article_model->gets($sql, $sql_count);
		$result['list'] = $this->deco_model->fill_collection($result['list'], array(
			'fields'=>array('username', 'company')
		), false);
		
		$this->load->library('pagination');
		$this->pagination->pageSize = $args['size'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->currentPage = $args['page'];
		if( count( $params ) == 0 )
		{
			$this->pagination->url_template = $this->get_complete_url( '/member/article/manage?page=<{page}>' );
			$this->pagination->url_template_first = $this->get_complete_url( '/member/article/manage' );
		}
		else
		{
			$this->pagination->url_template = $this->get_complete_url( '/member/article/manage?page=<{page}>&' . implode('&', $params) );
			$this->pagination->url_template_first = $this->get_complete_url( '/member/article/manage?' . implode('&', $params) );
		}
		
		$this->tpl->assign('pagination', $this->pagination->toString(true)); 
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('args', $args);
		$this->tpl->assign('so', $so);
		$this->tpl->assign('module', 'article.manage');
		$this->tpl->display( $this->get_tpl('member/article/manage.html') );
	}
	
	public function active()
	{
		
		$info = array(
			'id'		=> $this->gr('id'),
			'rurl'		=> $this->gr('r')
		);
		
		$tpl = $this->get_tpl('member/article/active.html');
		
		$this->load->model('company/usernews', 'company_article_model');
		
		$article = $this->company_article_model->get_new($info['id'], array(
			'fields'		=> 'id, title, addtime, username, detail, base_showcount, showcount, recycle',
			'format'		=> TRUE
		));
		
		$this->tpl->assign('article', $article);
		$this->tpl->assign('info', $info);
		$this->tpl->assign('module', 'article.active');
		$this->tpl->display( $tpl );
		
	}
	
	public function handler()
	{
		
		$info = $this->get_form_data();
		$rurl = $info['rurl'];
		unset( $info['rurl'] );
		
		$this->load->model('company/usernews', 'company_article_model');
		
		try
		{
			$this->company_article_model->edit( $info );
			$this->alert( '修改成功', $rurl );
		}
		catch(Exception $e)
		{
			$this->alert( $e->getMessage() );
		}
		
		
	}
	
}


?>