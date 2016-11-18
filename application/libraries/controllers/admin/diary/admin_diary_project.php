<?php

class admin_diary_project extends admin_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'project');
	}
	
	public function manage(){
		
		$this->load->model('city_model');
		$this->load->model('diary/diary_project_model');
		
		$args = array(
			'size'=>20,
			'page'=>$this->encode->get_page()
		);
		
		$where = array("1=1");
		$params = array();
		
		$so = array(
			'key'=>$this->gr('key')
		);
		
		if( $so['key'] !== '' )
		{
			$so['key'] = iconv('gbk', 'utf-8', $so['key']);
			$where[] = "(address like '%". $so['key'] ."%' or community like '%". $so['key'] ."%')";
			$params[] = "key=" . $so['key'];
		}
		
		$sql = "select * from ( select id, home_type, address, sheng, city, town, community, deco_username, deco_name, addtime, num = row_number() over( order by addtime desc ) from diary_project where ". implode(' and ', $where) ." ) as temp where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from diary_project where " . implode(' and ', $where);
		$projects = $this->diary_project_model->get_list($sql, $sql_count);
		// 附加城市信息
		$projects['list'] = $this->city_model->assign($projects['list'], array('in_label'=>'sheng', 'out_label'=>'sheng_info'));
		$projects['list'] = $this->city_model->assign($projects['list'], array('in_label'=>'city', 'out_label'=>'city_info'));
		$projects['list'] = $this->city_model->assign($projects['list'], array('in_label'=>'town', 'out_label'=>'town_info'));
		// 附加日记数量
		$projects['list'] = $this->diary_project_model->diary_count_assign($projects['list']);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $projects['count'];
		$this->pagination->pageSize = $args['size'];
		
		if( count($params) === 0 )
		{
			$this->pagination->url_template_first = $this->get_complete_url('/diary/project/manage');
			$this->pagination->url_template = $this->get_complete_url('/diary/project/manage?page=<{page}>');
		}
		else
		{
			$this->pagination->url_template_first = $this->get_complete_url('/diary/project/manage?' . implode('&', $params));
			$this->pagination->url_template = $this->get_complete_url('/diary/project/manage?page=<{page}>&' . implode('&', $params));
		}
		
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('list', $projects['list']);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('module', 'project.manage');
		$this->tpl->assign('args', $args);
		$this->tpl->assign('so', $so);
		$this->tpl->display( $this->get_tpl('diary/project_manage.html') );
	}
	
	public function active(){
		
		$id = $this->gr('id');
		
		$project = false;
	 	
		if($id == '')
		{	// 添加模式
			$this->tpl->assign('module', 'project.add');
		}
		else
		{	// 编辑模式
			
			$rurl = $this->gr('r');
			$this->load->model('diary/diary_project_model');
			$project = $this->diary_project_model->get("select id, address, sheng, city, town, community, type, home_type, area, budget, deco_username, deco_name from diary_project where id = '". $id ."'");
			
			
			$this->tpl->assign('rurl', $rurl);
			
			// 读取项目子日志数据
			$this->load->model('diary/diary_model');
			$this->diary_model->assign_diaries($project);
			
			$this->tpl->assign('module', 'project.edit');
		}
		
		$this->tpl->assign('project', $project);
		
		
		
		
		
		$this->tpl->display( $this->get_tpl('diary/project_active.html') );
		
	}
	
	// 项目提交
	public function handler(){
		$info = $this->get_form_data();
		$this->load->model('diary/diary_project_model');
		
		$rurl = $info['rurl'];
		unset($info['rurl']);
		
		try{
			
			if( $info['id'] == '' ){
				$info['addtime'] = date('Y-m-d H:i:s');
				$this->diary_project_model->add($info);
				$this->alert('提交成功', $this->get_complete_url('/diary/project/manage'));
			} else {
				
				$this->diary_project_model->edit($info);
				$this->alert('提交成功', $rurl);
			}
			
		}catch(Exception $e){
			throw new Exception( $e->getMessage() );
		}
		
	}
	
	// 统计当前日记中所有的项目信息，提交到项目表中
	public function extract_diary_info(){
	}
	
	// ajax 获取项目资料
	public function get(){
		
		// 保留键值的获取方式
		$data = $this->get_form_data(array('retain_key'=>true));
		
		$this->load->model('diary/diary_project_model');
		
		$project = $this->diary_project_model->get("select id, address, sheng, city, town, community, type, home_type, area, budget, deco_username, deco_name from [diary_project] where address = '". $data['address'] ."'");
		
		$result = array();
		
		if( $project == false ){
			$result['type'] = 'empty';
		} else {
			$result['type'] = 'success';
			$result['project'] = $project;
		}
		
		echo( json_encode($result) );
		
	}
	
	/*
	public function sync(){
		$this->load->library('mdb');
		$res = $this->mdb->query("select * from ( select sheng, city, town, address, com_name, type, home_type, area, budget, deco_username, deco_name, addtime, num = row_number() over( partition by address order by addtime asc ) from diary ) as tmp where num <= 1 order by addtime desc");
		
		foreach($res as $item){
			$this->mdb->insert("insert into diary_project(address, sheng, city, town, community, type, home_type, area, budget, deco_username, deco_name, addtime)values('". $item['address'] ."', '". $item['sheng'] ."', '". $item['city'] ."', '". $item['town'] ."', '". $item['com_name'] ."', '". $item['type'] ."', '". $item['home_type'] ."', '". $item['area'] ."', '". $item['budget'] ."', '". $item['deco_username'] ."', '". $item['deco_name'] ."', '". $item['addtime'] ."')");
		}
		
		echo('done');
		
	}
	*/
	
}

?>