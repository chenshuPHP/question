<?php

// 日记管理
class admin_diary_diary extends admin_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function manage()
	{
		$tpl = $this->get_tpl('diary/diary/manage.html');
		$this->load->model('diary/diary_model');
		// $this->load->model('diary/diary_config_model');
		$this->load->model('diary/diary_project_model');
		$cfg = array(
			'size'=>20,
			'page'=>$this->encode->get_page()
		);
		
		$sql = "select * from ( select id, title, project_id, stage, addtime, admin, num = row_number() over( order by addtime desc ) from diary ) as tmp where num between ". ( ($cfg['page'] - 1) * $cfg['size'] + 1 ) ." and " . ( $cfg['size'] * $cfg['page'] );
		$count_sql = "select count(*) as icount from diary";
		$res = $this->diary_model->get_list($sql, $count_sql);
		$this->diary_project_model->assign($res['list']);
		
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->pageSize = $cfg['size'];
		$this->pagination->recordCount = $res['count'];
		$this->pagination->url_template = $this->get_complete_url('diary/diary/manage?page=<{page}>');
		$this->pagination->url_template_first = $this->get_complete_url('diary/diary/manage');
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->assign('list', $res['list']);
		$this->tpl->assign('module', 'diary.manage');
		$this->tpl->display( $tpl );
	}
	
	
	public function active()
	{
		$tpl = $this->get_tpl('diary/diary/active.html');
		
		$pid = $this->gr('pid');
		$rurl = $this->gr('r');
		
		$id = $this->gr('id');
		
		if( ! empty( $id ) )
		{
			$this->load->model('diary/diary_model');
			$diary = $this->diary_model->get($id);
			if( ! $diary ) exit('找不到日记');
			$pid = $diary['project_id'];
			$this->tpl->assign('module', 'diary.edit');
		}
		else
		{
			$diary = false;
			$this->tpl->assign('module', 'diary.add');
		}
		
		
		$this->load->model('diary/diary_project_model');
		$this->load->model('diary/diary_config_model');
		
		$project = $this->diary_project_model->get("select id, address from [diary_project] where id = '". $pid ."'");
		
		$stages = $this->diary_config_model->get_stages();
		
		$this->tpl->assign('diary', $diary);
		$this->tpl->assign('stages', $stages);
		$this->tpl->assign('project', $project);
		$this->tpl->assign('rurl', $rurl);
		
		$this->tpl->display( $tpl );
	}
	
	public function handler()
	{
		$data = $this->get_form_data();
		$this->load->model( 'diary/diary_model' );
		try
		{
			
			if( $data['id'] === '' )
			{
				$data['admin'] = $this->admin_username;
				$data['addtime'] = date('Y-m-d H:i:s');
				$this->diary_model->add($data);
				$this->alert('提交成功', $data['rurl']);
			}
			else
			{
				$this->diary_model->edit($data);
				$this->alert('编辑成功', $data['rurl']);
			}
			
		}
		catch( Exception $e )
		{
			$this->alert( $e->getMessage() );
			exit();
		}
		
	}
	
	
	
}


?>