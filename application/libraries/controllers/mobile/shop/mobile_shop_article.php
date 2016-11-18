<?php

// 公司动态 文章
class mobile_shop_article extends mobile_shop_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('module', 'article');
	}
	
	public function _remap( $method, $args = array() )
	{
		if( ! empty( $method ) )
		{
			$method = strtolower( $method );
		}
		else
		{
			show_404();
		}
		if( preg_match('/^detail\-\d+$/', $method) )
		{
			$this->detail( str_replace('detail-', '', $method) );
		}
		else
		{
			show_404();
		}
	}
	
	public function home()
	{
		$tpl = $this->get_tpl( 'shop/article/home.html' );
		$this->load->model('company/usernews');
		$args = array(
			'page'		=> $this->encode->get_page(),
			'size'		=> 15
		);
		
		$where = $this->usernews->where . " and username = '". $this->username ."'";
		
		$sql = "select * from ( select id, title, addtime, username, base_showcount, showcount, num = row_number() over( order by id desc ) ".
		"from [". $this->usernews->table_name ."] where ". $where ." ) as tmp ".
		"where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from [". $this->usernews->table_name ."] where " . $where;
		
		$article = $this->usernews->gets( $sql, $sql_count );
		$this->mobile_url_model->assign_sp_article_url( $article['list'] );
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $article['count'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->url_template = $this->get_shop_complete_url('/article?page=<{page}>');
		$this->pagination->url_template_first = $this->get_shop_complete_url('/article');
		
		$pagination = $this->pagination->tostring_simple( array(
			'select'		=> TRUE
		) );

		$this->tpl->assign('article', $article['list']);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->display( $tpl );
	}
	
	public function detail($id)
	{
		
		$tpl = $this->get_tpl( 'shop/article/detail.html' );
		
		$this->load->model('company/usernews');
		$where = $this->usernews->where . " and username = '". $this->username ."'";
		$sql = "select id, title, addtime, username, base_showcount, showcount, detail, addtime ".
		"from [". $this->usernews->table_name ."] where " . $where;
		$article = $this->usernews->get( $sql );
		
		//var_dump2( $article );
		
		$article['detail'] = $this->encode->htmldecode( $article['detail'] );
		
		// 生成 ajax 请求的url 地址
		$article['vlink'] = 'http://m.shzh.net/shop/abcd1234/article/viewed';		
		//var_dump2( $article );
		
		
		$this->tpl->assign('article', $article);
		$this->tpl->display( $tpl );
		
	}
	
	/*
		time 				2016/11/10
		author				段
		dscription			新闻访问量控制展示， 根据请求的id，添加访问量
	*/
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