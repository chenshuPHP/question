<?php

class admin_member_letter extends admin_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('module', '');
		$this->load->model('company/member_letter_model');
		
	}
	
	public function manage()
	{
		$tpl = $this->get_tpl( 'member/letter/manage.html' );
		
		$config = array(
			'page'		=> $this->encode->get_page(),
			'size'		=> 30
		);
		
		$sql = "select * from ( select id, title, type, admin, addtime, num = row_number() over(order by addtime desc) ".
		"from [". $this->member_letter_model->table_name ."] ) as tmp ".
		"where num between ". ( ( $config['page'] - 1 ) * $config['size'] + 1 ) ." and " . ( $config['page'] * $config['size'] );
		
		$sql_count = "select count(*) as icount from [". $this->member_letter_model->table_name ."]";
		
		$res = $this->member_letter_model->gets($sql, $sql_count, $args = array(
			'format'		=> TRUE
		));
		
		// 附加管理员信息
		$this->load->model('manager/manager_model');
		$res['list'] = $this->manager_model->assign( $res['list'] );
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $config['page'];
		$this->pagination->recordCount = $res['count'];
		$this->pagination->pageSize = $config['size'];
		$this->pagination->url_template = $this->get_complete_url( '/member/letter/manage?page=<{page}>' );
		$this->pagination->url_template_first = $this->get_complete_url( '/member/letter/manage' );
		
		$this->tpl->assign('list', $res['list']);
		$this->tpl->assign('pagination', $this->pagination->toString( TRUE ));
		$this->tpl->assign('config', $config);
		$this->tpl->assign('module', 'manage');
		$this->tpl->display( $tpl );
	}
	
	
	// 写信
	public function active()
	{
		
		$id = $this->gr('id');
		$rurl = $this->gr('r');
		
		$letter = FALSE;
		
		if( ! empty( $id ) )
		{
			$sql = "select id, title, type, detail, vip, ordinary, admin, addtime ".
			"from [". $this->member_letter_model->table_name ."] where id = '". $id ."'";
			$letter = $this->member_letter_model->get( $sql );
			
			$this->member_letter_model->assign_additional( $letter );
			
			// var_dump2( $letter );
		}
		
		$this->tpl->assign('letter', $letter);
		
		$tpl = $this->get_tpl( 'member/letter/active.html' );
		$this->tpl->assign('types', $this->member_letter_model->types());
		$this->tpl->assign('module', 'add');
		$this->tpl->assign('rurl', $rurl);
		$this->tpl->display( $tpl );
	}
	
	// 写信界面提交
	public function handler()
	{
		$info = $this->get_form_data();
		
		$id = $info['id'];
		$rurl = $info['rurl'];
		
		if( ! isset( $info['group'] ) )
		{
			$group = array();
		}
		else
		{
			$group = $info['group'];
		}
		
		
		
		unset( $info['id'] );
		unset( $info['rurl'] );
		unset( $info['group'] );
		
		$info['vip'] = in_array('vip', $group) ? 1 : 0;					// 高级会员可见
		$info['ordinary'] = in_array('ordinary', $group) ? 1 : 0;		// 普通会员可见
		
		try
		{
			if( empty( $id ) )
			{
				$info['admin'] = $this->admin_username;
				$info['addtime'] = date('Y-m-d H:i:s');
				$this->member_letter_model->add( $info );
				$this->alert('提交成功', $this->get_complete_url('member/letter/manage'));
			}
			else
			{
				$info['update_admin'] = $this->admin_username;
				$this->member_letter_model->update($info, $id);
				$this->alert('修改成功', $rurl);
			}
		}
		catch(Exception $e)
		{
			exit( $e->getMessage() );
		}
		
	}
	
}


?>