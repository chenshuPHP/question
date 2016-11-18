<?php

// 装修公司 案例管理
class member_project_case extends member_project_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'project.case');
		
		$this->load->model('company/usercase', 'case_model');
		
	}
	
	public function manage(){
		
		$cases = $this->case_model->getUserCases($this->base_user, array(
			'fields'=>'id, username, casename as name, fm_image as fm',
			'fm_opt'=>false
		));
		
		$cases = $this->case_model->image_count_assign_case($cases);
		
		$info = $this->case_model->case_count_assign_users( $this->userinfo );
		
		$this->tpl->assign('cases', $cases);
		$this->tpl->assign('page_module', 'manage');
		$this->tpl->assign('count', $info['case_count']);
		
		$this->tpl->display( $this->get_tpl('project/case_manage.html') );	
	}
	
	// 添加案例
	public function add(){
		
		// 施工人员
		$this->load->model('company/userteam', 'employee_model');
		
		$emps = $this->employee_model->gets("select id, true_name from user_team_member where username = '". $this->base_user ."' and recycle = 0 order by sort_id asc");
		$emps = $emps['list'];
		
		// 修改
		$id = $this->gr('id');
		if( ! empty($id) ){
			$case = $this->case_model->getCase($id, array(
				'fields'=>'id, username, casename as name, sheng, city, town, build_type_1, build_type_2, style_name, budget, area, sdate, edate, fm_image as fm, mobile, address',
				'username'=>$this->base_user
			));
			
			$employees = $this->employee_model->gets("select id, username, true_name as name from user_team_member where id in ( select eid from user_case_employee where cid = '". $case['id'] ."' ) and recycle = 0");
			$case['employees'] = $employees['list'];
			
			$this->tpl->assign('case', $case);
			
			$this->tpl->assign('rurl', $this->gr('r'));
			
		}
		
		$this->tpl->assign('emps', $emps);
		$this->tpl->assign('page_module', 'add');
		
		$urls = $this->config->item('url');
		$this->tpl->assign('project_url', $urls['project']);
		if( $this->gr('debug') == 1 ){
			 $this->tpl->display('member/project/case_add2.html');
		}else{
			$this->tpl->display('member/project/case_add.html');
		}
		
		
		
	}
	
	// 添加 & 修改提交
	public function active_handler(){
		
		$case = array(
			'name'			=> $this->gf('name'),
			'sheng'			=> $this->gf('sheng'),
			'city'			=> $this->gf('city'),
			'town'			=> $this->gf('town'),
			'type1'			=> $this->gf('build_type_1'),
			'type2'			=> $this->gf('build_type_2'),
			'style'			=> $this->gf('style_name'),
			'area'			=> $this->gf('area'),
			'budget'		=> $this->gf('budget'),
			'sdate'			=> $this->gf('sdate'),
			'edate'			=> $this->gf('edate'),
			'mobile'		=> $this->gf('mobile'),
			'employee'		=> $this->gf('employee'),
			'addtime'		=> date('Y-m-d H:i:s'),
			'username'		=> $this->base_user,
			'id'			=> $this->gf('id'),
			'address'		=> $this->gf('address')
		);
		
		try{
			
			if( empty($case['id']) ){
				
				$cid = $this->case_model->case_add($case);
				
				// 增加口碑值
				$this->load->model('company/company_koubei_model');
				$this->company_koubei_model->project($this->base_user, $cid, array(
					'description'		=> '添加案例项目 - ' . $case['name']
				));
				
				$this->alert('提交成功', $this->get_complete_url('/project/case/stage?cid=' . $cid));
			} else {
				
				$cid = $this->case_model->case_edit($case);
				$this->alert('修改提交成功', $this->gf('r'));
			}
			
			// 设置店铺更新日期
			$this->deco_model->company_update($this->base_user);
			
		}catch(Exception $e){
			$this->alert( $e->getMessage() );
		}
	}
	
	// 删除案例
	public function delete(){
		
		$id = $this->gf('id');
		$case = $this->case_model->getCase($id, array(
			'username'=>$this->base_user,
			'fields'=>'id'
		));
		
		$case = $this->case_model->image_count_assign_case($case);
		if( $case['image_count'] > 0 ){
			echo('图片数量不为0');
		} else {
			try{
				$this->case_model->case_delete($id, $this->base_user);
				// 设置店铺更新日期
				$this->deco_model->company_update($this->base_user);
				echo( 'success' );
			}catch(Exception $e){
				echo( $e->getMessage() );
			}
		}
		
		
	}
	
	// 案例排序
	public function case_sort_handler(){
		$data = $this->gf('data');
		try{
			$this->case_model->case_sort($data, $this->base_user);
			// 设置店铺更新日期
			$this->deco_model->company_update($this->base_user);
			echo( 'success' );
		}catch(Exception $e){
			echo( $e->getMessage() );
		}
		
	}
	
	// 图片分组
	public function stage(){
		
		$cid = $this->gr('cid');
		$r = $this->gr('r');
		
		
		$case = $this->case_model->getCase($cid, array(
			'username'=>$this->base_user,
			'fields'=>'id, username, casename as name, fm_image as fm'
		));
		
		if( ! $case ) exit('没有找到需要修改的项目');
		
		
		$stages = $this->case_model->get_stages(array(
			'cid'=>$cid
		));
		
		$stages = $this->case_model->image_assign_stages($stages, array('fields'=>'id, jieduanid as sid, imgpath as path, title'));
		
		$this->tpl->assign('stages', $stages);
		$this->tpl->assign('case', $case);
		$this->tpl->assign('rurl', $r);
		$this->tpl->assign('page_module', 'stage');
		$this->tpl->display('member/project/case_stage.html');
		
	}
	
	// 图片组添加 界面
	public function stage_add(){
		
		$cid = $this->gr('cid');
		$r = $this->gr('r');
		
		$sid = $this->gr('sid');
		
		
		$case = $this->case_model->getCase($cid, array(
			'username'=>$this->base_user,
			'fields'=>'id, username, casename as name'
		));
		
		if( ! $case ) exit('没有找到需要修改的项目');
		
		if( ! empty($sid) ){
			$stage = $this->case_model->get_stage($sid, array(
				'cid'=>$cid
			));
			$this->tpl->assign('stage', $stage);
		}
		
		
		
		$this->tpl->assign('case', $case);
		$this->tpl->assign('rurl', $r);
		$this->tpl->assign('page_module', 'stage');
		$this->tpl->display('member/project/case_stage_add.html');
	}
	
	// 组添加 & 修改
	public function stage_handler(){
		
		$r 		= $this->gf('r');
		
		// 2016-09-13 新增 ajax 提交支持
		$ajax 	= $this->gf('ajax');
		
		
		$stage = array(
			'id'		=> $this->gf('sid'),
			'cid'		=> $this->gf('cid'),
			'name'		=> $this->gf('stage_name')
		);
		
		$errors = array();
		$success = '';
		
		if( empty($stage['name']) ){
			$errors[] = '请输入组名';
		}
		
		if( count( $errors ) == 0 )
		{
			// 检测案例是否为自己的
			$case = $this->case_model->getCase($stage['cid'], array('username'=>$this->base_user, 'fields'=>'id'));
			if( ! $case ) $errors[] = '没有找到需要修改的项目';
		}
		
		if( count( $errors ) == 0 )
		{
			if( empty($stage['id']) ){
				
				try{
					// 添加组
					$sid = $this->case_model->stage_add($stage);
					// 设置店铺更新日期
					$this->deco_model->company_update($this->base_user);
					$success = '添加完成';
				}catch(Exception $e){
					$errors[] = $e->getMessage();
				}
				
			} else {
				try{
					// 修改组
					$this->case_model->stage_edit($stage);
					$sid = $stage['id'];
					// 设置店铺更新日期	
					$this->deco_model->company_update($this->base_user);
					$success = '修改完成';
				}catch(Exception $e){
					$errors[] = $e->getMessage();
				}
			}
		}
		
		if( count( $errors ) == 0 )
		{
			if( $ajax == 1 )
			{
				echo( json_encode( array(
					'type'			=> 'success',
					'sid'			=> $sid,
					'cid'			=> $case['id'],
					'stage'			=> $stage['name'],
					'message'		=> $success
				) ) );
			}
			else
			{
				$this->alert($success, $r);
			}
		}
		else
		{
			if( $ajax == 1 )
			{
				echo( json_encode( array(
					'type'			=> 'error',
					'message'		=> $errors
				) ) );
			}
			else
			{
				$this->alert(implode(',', $errors));
			}
		}
		
	}
	
	// 图片上传
	public function image_add(){
		
		$mode = $this->gr('m');		// 上传模式
		$r = $this->gr('r');		// 返回页
		
		$stage_id = $this->gr('sid');	// 阶段ID
		$case_id = $this->gr('cid');	// 项目ID
		
		$case = $this->case_model->getCase($case_id, array(
			'fields'=>'id, casename as name, username',
			'username'=>$this->base_user
		));
		
		if( ! $case ) exit('没找到案例');
		
		$stage = $this->case_model->get_stage($stage_id, array(
			'cid'=>$case_id
		));
		
		$this->tpl->assign('case', $case);
		$this->tpl->assign('stage', $stage);
		$this->tpl->assign('mode', $mode);
		$this->tpl->assign('page_module', 'stage');
		$this->tpl->assign('rurl', $r);
		$this->tpl->display('member/project/image_add.html');
		
		
	}
	
	// 图片提交(批量)
	public function image_add_handler(){
		
		$r = $this->gf('r');
		
		$info = array(
			'cid'=>$this->gf('cid'),
			'sid'=>$this->gf('sid'),
			'paths'=>$this->gf('image_path'),
			'names'=>$this->gf('image_name'),
			'descs'=>$this->gf('image_desc'),
			'username'=>$this->base_user
		);
		
		$error = '';
		
		$case = $this->case_model->getCase($info['cid'], array(
			'fields'=>'id, username, fm_image as fm',
			'username'=>$this->base_user
		));
		
		if( ! $case )
		{
			$error = '找不到案例';
		}
		 
		if( $error == '' )
		{
			$stage = $this->case_model->get_stage($info['sid'], array(
				'cid'=>$info['cid']
			));
			if( ! $stage )
			{
				$error = '找不到阶段';
			}
		}
		
		if( $error == '' )
		{
			// 转移图片
			$this->load->model('tempfile_move_model');
			foreach($info['paths'] as $key=>$path){
				// $target = $this->tempfile_move_model->get_format_path($path);
				// $this->tempfile_move_model->tempfile_move($path, $target, true);
				$target = $this->tempfile_move_model->tempfile_move($path);
				//echo($path);
				//echo($target);
				$info['paths'][$key] = $target;
			}
			
			try{
				$image_ids = $this->case_model->case_image_add($info);
				// 设置店铺更新日期
				$this->deco_model->company_update($this->base_user);
			}catch(Exception $e){
				$error = $e->getMessage();
			}
		}
		
		
		if( $error == '' )
		{
			// 顺带设置封面
			if( empty($case['fm']) ){
				$this->case_model->set_front_cover(
					array(
						'image_id'=>$image_ids[0],
						'case_id'=>$case['id'],
						'username'=>$this->base_user
					)
				);
			}
		}
		
		if( $this->gf('ajax') == 1 )
		{
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
		else
		{
			if( $error == '' )
			{
				$this->alert('上传成功', $r);
			}
			else
			{
				$this->alert( $error );
			}
		}
		
		
		
	}
	
	// 图片修改
	public function image_edit(){
		
		$info = array(
			'id'=>$this->gr('id'),
			'sid'=>$this->gr('sid'),
			'cid'=>$this->gr('cid')
		);
		
		$rurl = $this->gr('r');
		
		$ajax = $this->gr('ajax');
		
		$image = $this->case_model->get_image($info['id'], array(
			'fields'=>'id, jieduanid as sid, imgpath as path, title, description, fengmian as fm',
			'format'=>false
		));
		if( ! $image ) exit('找不到图片');
		
		$stage = $this->case_model->get_stage($image['sid'], array(
			'fields'=>'id, caseid as cid'
		));
		if( ! $stage ) exit('找不到图片组');
		
		$case = $this->case_model->getCase($stage['cid'], array(
			'fields'=>'id, fm_image as fm',
			'username'=>$this->base_user
		));
		if( ! $case ) exit('找不到案例');
		
		if( $ajax == 1 )
		{
			$image['src'] = $this->upload_complete_url( $image['path'] );
			$_arr = array(
				'project'		=> $case,
				'stage'			=> $stage,
				'image'			=> $image,
				'rurl'			=> $rurl,
				'page_module'	=> 'stage'
			);
			json_echo( $_arr );
		}
		else
		{
			$this->tpl->assign('case', $case);
			$this->tpl->assign('stage', $stage);
			$this->tpl->assign('image', $image);
			$this->tpl->assign('rurl', $this->gr('r'));
			$this->tpl->assign('page_module', 'stage');
			$this->tpl->display('member/project/image_edit.html');
		}
		
	}
	
	// 图片修改提交
	public function image_edit_handler(){
		
		
		$info = array(
			'id'			=> $this->gf('id'),
			'sid'			=> $this->gf('sid'),
			'cid'			=> $this->gf('cid'),
			'new_path'		=> strtolower( $this->gf('image_path') ),
			'old_path'		=> strtolower( $this->gf('old_path') ),
			'name'			=> $this->gf('name'),
			'description'	=> $this->gf('description'),
			'fm'			=> $this->gf('fm'),
			'username'		=> $this->base_user
		);
		
		$error = '';
		
		if( empty($info['name']) ){
			$error = '请输入图片名称';
		}
		
		if( $error == '' )
		{
			$case = $this->case_model->getCase($info['cid'], array(
				'fields'		=> 'id, fm_image as fm',
				'username'		=> $this->base_user
			));
			if( ! $case ) $error = '找不到所属案例';
		}
		
		if( $error == '' )
		{
			$stage = $this->case_model->get_stage($info['sid'], array(
				'fields'=>'id, caseid as cid'
			));
			if( ! $stage ) $error = '找不到所属阶段';
		}
		
		if( $error == '' )
		{
			if( $stage['cid'] != $info['cid'] )
			{
				$error = '数据匹配错误';
			}
		}
		
		if( $error == '' )
		{
			$info['path'] = $info['old_path'];
			if( ! empty( $info['new_path'] ) ){
				$this->load->model('tempfile_move_model');
				$this->tempfile_move_model->tempfile_move($info['new_path'], $info['old_path'], true);
			}
			try{
				$this->case_model->image_edit( $info );
				// 设置店铺更新日期
				$this->deco_model->company_update($this->base_user);
				// $this->alert('修改完成', $this->gf('r'));
			}catch(Exception $e){
				//$this->alert($e->getMessage());
				$error = $e->getMessage();
			}
		}
		
		if( $error == '' )
		{
			// 封面设置
			if( $info['fm'] == 1 ){
				
				$this->case_model->set_front_cover(array(
					'image_id'		=> $info['id'],
					'case_id'		=> $info['cid'],
					'username'		=> $this->base_user
				));
				
			} else {
				// 取消封面的情况
				// 如果当前是封面就设置案例中的第一张图片为封面
				// 如果当前不是封面则不管
				if( strtolower($case['fm']) == strtolower($info['path']) ){
					$this->case_model->set_case_default_fm($info['cid']);	// 为案例设置一个默认的封面
				}
			}
		}
		
		if( $this->gf('ajax') == 1 )
		{
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
		else
		{
			if( $error == '' )
			{
				$this->alert('修改完成', $this->gf('r'));
			}
			else
			{
				$this->alert( $error );
			}
		}
		
	}
	
	// 图片排序
	public function image_sort_handler(){
		
		$sid = $this->gf('sid');
		$data = $this->gf('data');
		
		// data: array([id, sort], [id, sort], ...)
		try{
			$this->case_model->image_sort($sid, $data, $this->base_user);
			// 设置店铺更新日期
			$this->deco_model->company_update($this->base_user);
			echo('success');
		}catch(Exception $e){
			exit($e->getMessage());
		}
		
	}
	
	public function image_delete(){
		
		$id = $this->gf('id');
		
		try{
			$this->case_model->case_image_delete(array(
				'image_id'=>$id,
				'username'=>$this->base_user
			));
			// 设置店铺更新日期
			$this->deco_model->company_update($this->base_user);
			echo('success');
		} catch(Exception $e) {
			echo( $e->getMessage() );
		}
	}
	
	public function stage_delete(){
		
		$id = $this->gf('sid');
		
		try{
			$this->case_model->case_stage_delete(array(
				'id'=>$id,
				'username'=>$this->base_user
			));
			// 设置店铺更新日期
			$this->deco_model->company_update($this->base_user);
			echo('success');
		} catch(Exception $e) {
			echo( $e->getMessage() );
		}
		
	}
	
	// 阶段排序
	public function stage_sort_handler(){
		
		$cid = $this->gf('cid');
		$data = $this->gf('data');
		
		try{
			$this->case_model->stage_sort(array(
				'cid'=>$cid,
				'data'=>$data,
				'username'=>$this->base_user
			));
			// 设置店铺更新日期
			$this->deco_model->company_update($this->base_user);
			echo('success');
		}catch(Exception $e){
			echo( $e->getMessage() );
		}
		
		
		
	}
	
	// ajax刷新参与人员数据
	public function get_refresh_employee(){
		
		$this->load->model('company/employee_category_model');
		$this->load->model('company/userteam', 'employee_model');
		
		$cates = $this->employee_category_model->gets("select id, job_name as name from user_team_job_type where username = '". $this->base_user ."' order by sort_id desc");
		$cates = $this->employee_model->employee_assign($cates, array(
			'primary_key'		=> 'id',
			'foreign_key'		=> 'job_id',
			'fields'			=> 'id, job_id, true_name as name'
		));
		
		echo( json_encode($cates) );
		
		
		
	}
	
	
	
}







































?>