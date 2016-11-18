<?php

class mobile_shop_project extends mobile_shop_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('module', 'project');
		
		$this->load->model('company/usercase', 'project_model');
		$this->load->model('company/project_category_model');
		$this->load->model('company/userteam', 'employee_model');
		
		
	}
	
	// 当调用 detail-100 这样的URL时使用
	public function _remap($method_name, $args = array())
	{
		if( preg_match('/^detail\-\d+$/', strtolower( $method_name )) )
		{
			$_tmp = explode('-', $method_name);
			$this->detail( $_tmp[1] );
		}
	}
	
	public function home()
	{
		$tpl = $this->get_tpl('shop/project/list.html');

		$args = array(
			'page'		=> $this->encode->get_page(),
			'size'		=> 8,
		);
		
		$ajax = $this->gr('ajax');
		
		$where = "username = '". $this->username ."' and fm_image <> '' and recycle = 0";
		
		$sql = "select * from ( select id, username, casename, fm_image, build_type_1, build_type_2, style_name, area, budget, ".
		"num = row_number() over( order by sort_id asc ) from [". $this->project_model->table_name ."] where ". $where ." ) as tmp ".
		"where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		
		$result = $this->project_model->get_case_list($sql);
		$result = $result['list'];
		$result = $this->project_category_model->assign_to_project( $result );
		$result = $this->employee_model->employee_assign_project( $result, array(
			'fields'	=> 'id, job_id, username, true_name, face_image, recycle',
			'size'		=> 3
		) );
		$result = $this->project_model->image_count_assign_case($result);
		
		$this->mobile_url_model->assign_sp_project_url( $result );
		
		// 员工头像缩略图  
		foreach($result as $key=>$value)
		{
			if( $value['employees'] )
			{
				foreach($value['employees'] as $k=>$v)
				{
					if( ! empty( $v['face_image'] ) ) $value['employees'][$k]['thumb'] = $this->thumb->crop($v['face_image'], 120, 120);
				}
			}
			$result[$key] = $value;
		}
		
		/*
			time    		2016-11-8   13：00
			author			段
			description 	获取团队员工是否离职 		
		*/
		/*foreach($result as $key=>$value)
		{
			if( $value['employees'] )
			{
				foreach($value['employees'] as $k=>$v)
				{
					if( ! empty( $v['face_image'] ) ) $value['employees'][$k]['thumb'] = $this->thumb->crop($v['face_image'], 120, 120);
					if( $v['recycle'] == 1 )
					{
						$value['employees'][$k]['mlink'] = 'javascript:;';	
						$value['employees'][$k]['true_name'] = $v['true_name'] . '已离职';			
					}
				}
			}
		}*/		
		
		$this->tpl->assign('args', $args);

		//var_dump2($result);		
		
		$this->tpl->assign('project', $result);
		
		$html = $this->tpl->fetch( $this->get_tpl('shop/project/list_content.inc.html') );
		
		if( $ajax == 1 )
		{
			echo( $html );
		}
		else
		{
			$this->tpl->assign('html', $html);
			$this->tpl->display( $tpl );
		}
		
	}
	
	public function detail( $project_id )
	{
		$tpl = $this->get_tpl('shop/project/detail.html');
		
		$project = $this->project_model->getCase($project_id, array(
			'username'	=> $this->username
		));
		
		$project = $this->project_category_model->assign_to_project( $project );
		$project = $this->employee_model->employee_assign_project( $project, array(
			'fields'	=> 'id, job_id, username, true_name, face_image'
		) );
		$project = $this->project_model->image_count_assign_case($project);
		$this->mobile_url_model->assign_sp_project_url( $project );
		
		// 员工头像缩略图
		if( $project['employees'] )
		{
			foreach($project['employees'] as $k=>$v)
			{
				if( ! empty( $v['face_image'] ) ) $project['employees'][$k]['thumb'] = $this->thumb->crop($v['face_image'], 120, 120);
			}
		}
		
		$stages = $this->project_model->get_stages( array(
			'cid'		=> $project['id']
		) );
		
		$stages = $this->project_model->image_assign_stages( $stages );
		
		$project['stages'] = $stages;
		
		//获取 上一个和下一个案例的 地址
		/*$username = $project['username'];
		$fields = array(
				'sort_last'		=>	'max(sort_id) as num',
				'sort_next'		=>	'min(sort_id) as num',
				'lastnext'		=>	'caseName,address,sort_id,id,username'
			);
		$where = array(
				'sort_last'		=>	"fm_image <> '' and username='$username' and sort_id < (select sort_id from user_case where id=$project_id)",
				'sort_next'		=>	"fm_image <> '' and username='$username' and sort_id > (select sort_id from user_case where id=$project_id)",
				'lastnext'		=>	"and username='$username' and sort_id="
			); 
		
		$lastnext = $this->project_model->getLastNext($fields,$where);*/
		
		
		
		$lastnext = $this->project_model->getLastNext($project_id,$project['username']);
		
		// 如果 排序是第一个和最后一个案例就不 生成url，手写
		if($lastnext['last'] == ''){
			$lastnext['last']['caseName'] = '没有了';
			$lastnext['last']['link'] = 'javascript:;';
		}else{
			$this->mobile_url_model->assign_sp_project_url( $lastnext['last'] );
		}
		if($lastnext['next'] == ''){
			$lastnext['next']['caseName'] = '没有了';
			$lastnext['next']['link'] = 'javascript:;';
		}else{
			$this->mobile_url_model->assign_sp_project_url( $lastnext['next'] );
		}
		
		$this->tpl->assign('lastnext', $lastnext);
		$this->tpl->assign('project', $project);
		
		$this->tpl->display( $tpl );
	}
	
	
}



























?>