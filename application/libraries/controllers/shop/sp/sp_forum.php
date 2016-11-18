<?php

// 店铺评论 
class sp_forum extends sp_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function handler( $params = array() )
	{
		$info = $this->get_form_data();
		
		$error = '';
		
		if( $error == '' )
		{
			if( $this->encode->utf8_strlen( $info['comment'] ) < 15 )
			{
				$error = '不能少于15字';
			}
		}
		
		/*
		if( $error == '' )
		{
			if( $info['comment_username'] == $this->user )
			{
				$error = '无法给自己留言';
			}
		}
		*/
		
		// 店铺留言板模型
		$this->load->model('company/company_forum_model');
		
		try
		{
			$_arr = array(
				'sp_username'				=> $this->user,
				'comment_username'			=> '',					// 所有留言均为匿名
				'comment'					=> $info['comment']
			);
			$this->company_forum_model->add( $_arr );
		}
		catch(Exception $e)
		{
			$error = $e->getMessage();
		}
		
		if( $error == '' )
		{
			json_echo( array(
				'type'			=> 'success'
			) );
		}
		else
		{
			json_echo( array(
				'type'			=> 'error',
				'message'		=> $error
			) );
		}
		
	}

	// 加载评论
	public function loader( $option = array() )
	{
		$info = array(
			'page'			=> $this->gr('page'),
			'size'			=> 10,
			'datatype'		=> $this->gr('datatype')
		);

		if( empty($info['page']) ) $info['page'] = 1;
		if( empty($info['datatype']) ) $info['datatype'] = 'html';


		$info = array_merge($info, $option);
		$this->load->model('company/company_forum_model');

		$where = $this->company_forum_model->build_where(array(
			'username'		=> $this->user
		));
		$where .= " and recycle = 0";
		$sql = "select * from ( select id, sp_username, comment, addtime, ip, num = row_number() over( order by addtime desc ) from [". $this->company_forum_model->table_name ."] where ". $where ." ) as tmp where num between ". ( ($info['page'] - 1) * $info['size'] + 1 ) ." and " . ( $info['page'] * $info['size'] );
		$sql_count = "select count(*) as icount from [". $this->company_forum_model->table_name ."] where " . $where;

		$result = $this->company_forum_model->gets($sql, $sql_count);

		if($info['datatype'] == 'json')
		{
			json_encode( $result );
		}
		elseif ( $info['datatype'] == 'html' )
		{
			$tpl = $this->get_tpl('forum/segment.html');
			$this->tpl->assign('list', $result['list']);
			
			
			$this->load->library('pagination');
			$this->pagination->currentPage = $info['page'];
			$this->pagination->recordCount = $result['count'];
			$this->pagination->pageSize = $info['size'];
			$this->pagination->url_template = 'javascript:load_forum(<{page}>);';
			$this->pagination->url_template_first = 'javascript:load_forum(1);';
			
			$this->tpl->assign('pagination', $this->pagination->toString( TRUE ));
			
			$html = $this->tpl->fetch( $tpl );
			echo( $html );
		}

	}
	
}

?>