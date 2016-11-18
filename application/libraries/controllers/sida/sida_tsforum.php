<?php

// 投诉申诉
// ajax 交互
class sida_tsforum extends sida_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('sida/tousu_writeable_model');
	}
	
	private function get_logo_complete_url($path)
	{
		return $this->upload_complete_url( $path, 'logo' );
	}
	
	private function _assign_userinfo( &$result, $username )
	{
		$this->load->model('company/company');
		$userinfo = $this->company->get("select username, company, rejion, logo, register from company where username = '". $username ."'");
		$userinfo['logo_url'] = $this->get_logo_complete_url( $userinfo['logo'] );
		$result['userinfo'] = $userinfo;
	}
	
	// 检测当前登录用户的权限
	// ajax
	public function check_writeable()
	{
		$tsid = $this->gf('tsid');
		
		if( $tsid == '' || ! preg_match('/^\d+$/', $tsid) ) show_404();
		
		$username = $this->tousu_writeable_model->get_current_username();
		
		$result = $this->tousu_writeable_model->get_mobile_ive( $username, $tsid );
		
		$this->_assign_userinfo($result, $username);
		
		echo( json_encode( $result ) );
	}


	
	// 新增申诉
	public function add()
	{
		$error = '';

		// 申诉内容
		$info = array(
			'detail'			=> $this->gf('detail'),
			'tsid'				=> $this->gf('tsid')
		);

		// gf('token')					通道的 token
		// gf('user_login_token')		用户单点登录的 token
		$a = $this->gf('user_login_token');
			//echo json_encode(array($a));exit;
//		echo json_encode(array(3333,4444));
//		if( ! empty( $this->gf('user_login_token') ) && config_item('csrf_token_name') == $this->gf('token') )
//		{
//			echo json_encode(array(3333,4444));exit;
//
//
//			$token = $this -> gf('user_login_token');
//
//			$this->load->model('loginModel');
//			if( ! $username = $this->loginModel->check_token($token) )
//			{
//				$error = '未登录无法发布内容';
//			}
//		}else
//		{
//			echo json_encode(array(444,5555));exit;
			if( ! $username = $this->tousu_writeable_model->get_current_username() )
			{
				$error = '未登录无法发布内容';
			}
//	    }

		if( $error == '' )
		{
			$check = $this->tousu_writeable_model->get_mobile_ive($username, $info['tsid']);
			$error = $check['writeable'] === TRUE ? '' : $check['message'];		// 检测是否有发布权限
		}
		
		if( $error == '' )
		{
			$info['username'] 		= $username;
			$info['ive']			= $check['type'];		// 记录留言类型 ( 投诉方 active 或 被投诉方 passive )
			
			$this->load->model('sida/tousu_forum_model');
			try
			{
				
				$id = $this->tousu_forum_model->add( $info );
				
				// 返回当前投诉的信息
				$forum = $this->tousu_forum_model->get($id, array(
					'fields'		=> 'tsid, username, addtime, ive'
				));
				
				$forum['addtime'] = date('Y-m-d H:i', strtotime( $forum['addtime'] ));
				
				$this->load->model('company/company');
				
				// 取得当前投诉人的基本信息
				$this->company->assign($forum, array(
					'fields'				=> 'username, company, rejion, logo, register',
					'assign_key_name'		=> 'userinfo'
				));
				
				// LOGO 路径处理
				if( isset( $forum['userinfo']['logo'] ) )
				{
					$forum['userinfo']['logo_url'] = $this->get_logo_complete_url( $forum['userinfo']['logo'] );
				}
				
				
				// 2016-09-28 被投诉人 3小时内的 第一条反馈信息会以短信的形势通知投诉人
				if( $info['ive'] == 'passive' )
				{
					
					$count = $this->tousu_forum_model->get_count( array(
						'where'		=> "tsid = '". $info['tsid'] ."' and ive = '". $info['ive'] ."' and datediff(hour, addtime, '". date('Y-m-d H:i:s') ."') <= 3"
					) );
					
					if( $count <= 1 )
					{
						// 发送短信给业主 通知有回复了
						$this->load->model('sida/TousuModel', 'tousu_model');
						
						$_tousu = $this->tousu_model->getSingle($info['tsid'], array(
							'fields'		=> 'id, tel, username',
							'format'		=> FALSE
						));
						
						$_tousu = $this->tousu_model->formatSingle($_tousu, array(
							'format_display_truename'		=> TRUE,
							'format_display_truetel'		=> TRUE
						));
						
						$content = '业主'. $_tousu['username'] .'您好，您在上海装潢网的装修投诉有了新回复，详情地址 '. $_tousu['send_message_link'] .'【上海装潢网】';
						
						if( preg_match('/^1[3-9]\d{9}$/', $_tousu['tel']) )
						{
							$_arr = array(
								'tsid'			=> $info['tsid'],
								'content'		=> $content,
								'mobile'		=> $_tousu['tel'],
								'addtime'		=> date('Y-m-d H:i:s'),
								'username'		=> 'szwlzaq'
							);
							$this->load->model('sida/tousu_letter_model');
							$_res = $this->tousu_letter_model->send( $_arr );
							if( $_res['type'] == 'error' )
							{
								// do something
							}
						}
						
					}
				}
				
				
				
			}
			catch(Exception $e)
			{
				$error = $e->getMessage();
			}
		}

		// 返回当前发布的投诉相关信息
		if( $error == '' )
		{
			echo( json_encode( array(
				'type'		=> 'success',
				'forum'		=> $forum
			) ) );
		}
		else
		{
			echo( json_encode(array(
				'type'		=> 'error',
				'error'		=> $error
			)) );
		}
		
	}
	
	// 读取留言
	public function gets()
	{
		
		$config = array(
			'page'			=> $this->gr('page'),
			'size'			=> 10,
			'fields'		=> 'id, tsid, username, title, detail, addtime, ive',
			'order'			=> 'order by addtime asc',
			'tsid'			=> $this->gr('tsid'),
			'style'			=> 'pc'
		);
		if( $this->gr('style') != '' ) $config['style'] = $this->gr('style');
		if( $this->gr('size') != '' ) $config['size'] = $this->gr('size');
		
		switch( $config['style'] )
		{
			case 'mobile':
				$tpl = 'xiehui/tousu/forum_list_segment_mobile.html';
				break;
			default:
				$tpl = 'xiehui/tousu/forum_list_segment.html';
				break;
		}
		
		
		if( $config['page'] == '' ) $config['page'] = 1;
		
		if( $config['tsid'] == '' || ! preg_match('/^\d+$/', $config['tsid']) ) show_404();
		
		$where = "tsid = '". $config['tsid'] ."'";
		
		$this->load->model('sida/tousu_forum_model');
		$this->load->model('company/company');
		
		$sql = "select * from ( select ". $config['fields'] .", num = row_number() over( ". $config['order'] ." ) ".
		"from [". $this->tousu_forum_model->table_name ."] where tsid = '". $config['tsid'] ."' ) ".
		"as tmp where num between ". ( ($config['page'] - 1) * $config['size'] + 1 ) ." and " . ( $config['page'] * $config['size'] );
		$sql_count = "select count(*) as icount from [". $this->tousu_forum_model->table_name ."] where " . $where;
		
		$res = $this->tousu_forum_model->gets($sql, $sql_count);
		// $this->tousu_forum_model->ive_assign( $res['list'] );
		$this->company->assign( $res['list'], array(
			'fields'		=> 'username, company, rejion, logo, register'
		) );
		
		if( $config['style'] == 'mobile' )
		{
			$this->load->library('pagination');
			$this->pagination->currentPage = $config['page'];
			$this->pagination->pageSize = $config['size'];
			$this->pagination->recordCount = $res['count'];
			$urls = config_item('url');
			$this->pagination->url_template = 'javascript:changepage(<{page}>);';
			$this->pagination->url_template_first = 'javascript:changepage(1);';
			$this->tpl->assign('pagination', $this->pagination->tostring_simple( array(
				'select'		=> TRUE,
				'onchange'		=> 'javascript:changepage(this.value);',
				'option_data'	=> TRUE,
				'value'			=> 'page',
				'return'		=> TRUE
			) ));
		}
		else
		{
			$this->load->library('pagination');
			$this->pagination->currentPage = $config['page'];
			$this->pagination->pageSize = $config['size'];
			$this->pagination->recordCount = $res['count'];
			$urls = config_item('url');
			$this->pagination->url_template = 'javascript:forum.load_forum(<{page}>);';
			$this->pagination->url_template_first = 'javascript:forum.load_forum(1);';
			$this->tpl->assign('pagination', $this->pagination->toString(TRUE));
		}
		
		
		$this->tpl->assign('list', $res['list']);
		$content = $this->tpl->fetch( $tpl );
		
		echo( $content );
		
		
	}
	
	// 内测版 界面
	/*
	public function view()
	{
		$tpl = 'xiehui/tousu/forum.html';
		$this->tpl->display( $tpl );
	}
	*/ 
}

?>