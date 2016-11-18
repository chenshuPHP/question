<?php

// 文章 编辑
class member_article_active extends member_article_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		$tpl = $this->get_tpl('article/active.html');
		
		$params = array(
			'id'			=> $this->gr('id'),
			'rurl'			=> $this->gr('r')
		);
		
		$article = FALSE;
		
		if( $params['id'] != '' )
		{
			$this->load->model('company/usernews', 'company_article_model');
			$article = $this->company_article_model->get_new($params['id'], array(
				'username'			=> $this->base_user,
				'fields'			=> 'id, title, detail',
				'format'			=> FALSE
			));
		}
		
		$this->tpl->assign('article', $article);
		$this->tpl->assign('params', $params);
		
		var_dump2( $article );
		var_dump2( $params );
		
		$this->tpl->display( $tpl );
	}
	
	// 提交处理
	public function handler()
	{
		$info = $this->get_form_data();
		$this->load->model('company/usernews', 'company_article_model');
		
		$error = '';
		
		if( $info['title'] == '' ) $error = '标题不能为空';
		if( $this->encode->utf8_strlen($info['detail']) < 15 )
		{
			$error = '内容太短不可少于15字';
		}
		
		if( $error == '' )
		{
			try
			{
				if( isset( $info['id'] ) && $info['id'] != '' )
				{
					$this->company_article_model->edit( array(
						'id'			=> $info['id'],
						'title'			=> $info['title'],
						'detail'		=> $info['detail'],
						'username'		=> $this->base_user
					) );
				}
				else
				{
					$id = $this->company_article_model->add( array(
						'title'			=> $info['title'],
						'detail'		=> $info['detail'],
						'username'		=> $this->base_user
					) );
					
					// 添加口碑值
					$this->load->model('company/company_koubei_model');
					$this->company_koubei_model->article($this->base_user, $id, array(
						'description'		=> '添加公司动态 - ' . $info['title']
					));
					
				}
				
				// 店铺更新日期
				$this->deco_model->company_update($this->base_user);
				
			}
			catch(Exception $e)
			{
				$error = $e->getMessage();
			}
		}
		
		if( $error == '' )
		{
			json_echo( array(
				'type'		=> 'success'
			) );
		}
		else
		{
			json_echo( array(
				'type'		=> 'error',
				'error'		=> $error
			) );
		}
		
	}
	
	
	// 文章删除
	// 删除到回收站
	public function delete()
	{
		$id = $this->gf('id');
		$error = '';
		if( ! preg_match('/^\d+$/', $id) ) $error = '参数错误';
		if( $error == '' )
		{
			try
			{
				$this->load->model('company/usernews', 'company_article_model');
				$this->company_article_model->recycle($id, array(
					'username'			=> $this->base_user
				));
				
				// 店铺更新日期
				$this->deco_model->company_update($this->base_user);
				
			}
			catch(Exception $e)
			{
				$error = $e->getMessage();
			}
		}
		if( $error == '' )
		{
			json_echo( array(
				'type'		=> 'success',
				'id'		=> $id
			) );
		}
		else
		{
			json_echo( array(
				'type'		=> 'error',
				'error'		=> $error,
				'id'		=> $id
			) );
		}
	}
	
}



?>