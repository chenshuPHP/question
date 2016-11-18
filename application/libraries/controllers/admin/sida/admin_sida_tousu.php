<?php

// 2015-10-30
// 投诉管理
class admin_sida_tousu extends admin_base {
	
	public function __construct(){
		parent::__construct();
		$this->load->model('sida/tousuModel', 'tousu_model');
		$this->load->model('sida/tousu_config_model');
	}
	
	// 投诉列表
	public function manage(){
		
		$args = array(
			'size'=>20,
			'page'=>$this->encode->get_page(),
			'recycle'=>$this->gr('recycle')
		);
		
		$so = array(
			'key'		=> $this->gr('key'),
			'status'	=> $this->gr('status')
		);
		
		$where = '1=1';
		$params = array();
	 	
		if( ! empty($so['key']) ){
			$so['key'] = iconv("gbk", "utf-8", $so['key']);
			$where .= " and (username = '". $so['key'] ."' or title like '%". $so['key'] ."%' or danwei like '%". $so['key'] ."%')";
			$params[] = 'key=' . $so['key'];
		}
		
		if( ! empty( $so['status'] ) )
		{
			$where .= " and status = '". $so['status'] ."'";
			$params[] = 'status=' . $so['status'];
		}
		
		
		if( empty($args['recycle']) ){
			$where .= ' and recycle = 0';
			$this->tpl->assign('module', 'manage');
		} else {
			$where .= ' and recycle = 1';
			$params[] = 'recycle=' . $args['recycle'];
			$this->tpl->assign('module', 'recycle');
		}
		
		
		$sql = "select * from ( select id, username, title, classname, danwei, puttime, source, status, num = row_number() over( order by puttime desc ) from [sendmessage1] where ". $where ." ) as temp where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from [sendmessage1] where ". $where;
		
		$result = $this->tousu_model->get_list($sql, $sql_count, array(
			'format'						=> true,
			'format_display_truename'		=> true		// 是否显示真实姓名
		));
		
		var_dump2( $result );
		
		$result['list'] = $this->tousu_config_model->attr_assign($result['list']);
		
		
		
		
		// 附加短信发送数量
		$this->load->model('sida/tousu_letter_model');
		$this->tousu_letter_model->count_assign($result['list']);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->pageSize = $args['size'];
		if( count($params) == 0 ){
			$this->pagination->url_template = $this->get_complete_url('/sida/tousu/manage?page=<{page}>');
			$this->pagination->url_template_first = $this->get_complete_url('/sida/tousu/manage');
		} else {
			$this->pagination->url_template = $this->get_complete_url('/sida/tousu/manage?page=<{page}>&' . implode('&', $params));
			$this->pagination->url_template_first = $this->get_complete_url('/sida/tousu/manage?' . implode('&', $params));
		}
		
		$this->tpl->assign('pagination', $this->pagination->toString(true));
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('args', $args);
		$this->tpl->assign('so', $so);
		$this->tpl->assign('status', $this->tousu_config_model->get_status_opts());
		
		$this->tpl->display( $this->get_tpl('sida/tousu/manage.html') );
		
	}
	
	// 投诉信息编辑
	public function active(){
		
		$id = $this->gr('id');
		$tousu = $this->tousu_model->getSingle($id, array(
			'fields'		=> 'id, username, title, content, classname, puttime, tel, mobile, address, fangtype, mianji, xingshi, zaojia, danwei, rejion, he, xie, backContent, source, status, recycle, puttime, revok, revoke_info, showcount_base, showcount',
			'format'		=> false
		));
		$tousu['content'] = $this->encode->htmldecode( $tousu['content'] );
		
		// 附加短信发送数量
		$this->load->model('sida/tousu_letter_model');
		$this->tousu_letter_model->count_assign($tousu);
		
		// var_dump2( $tousu );
		
		$this->tpl->assign('tousu', $tousu);
		$this->tpl->assign('types', $this->tousu_config_model->get_types());
		$this->tpl->assign('hetong_opts', $this->tousu_config_model->get_hetong_opts());
		$this->tpl->assign('tiaojie_opts', $this->tousu_config_model->get_tiaojie_opts());
		$this->tpl->assign('bao_opts', $this->tousu_config_model->get_bao_opts());
		$this->tpl->assign('status_opts', $this->tousu_config_model->get_status_opts());
		
		$this->tpl->assign('module', 'active');
		$this->tpl->assign('rurl', $this->encode->get_rurl('r'));
		
		// 投诉处理记录
		$this->load->model('sida/tousu_recordset_model');
		$recordset = $this->tousu_recordset_model->get_recordset($tousu['id']);
		
		$this->tpl->assign('recordset', $recordset);
		$this->tpl->display( $this->get_tpl('sida/tousu/active.html') );
	}
	
	public function handler(){
		$info = $this->get_form_data();
		$rurl = $this->encode->rurlencode($info['rurl']);
		unset($info['rurl']);
		
		if( ! isset( $info['revok'] ) ) $info['revok'] = 0;
		
		$this->load->model('sida/tousuModel', 'tousu_model');
		try{
			$this->tousu_model->edit($info);
			$this->alert('修改成功', $rurl);
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
	}
	
	// 记录集
	/*
	public function recordset(){
		$info = array(
			'id'=>$this->gr('id'),
			'rurl'=>$this->gr('r')
		);
		$this->tpl->assign('module', 'recordset');
		$this->tpl->display( $this->get_tpl('sida/tousu/recordset.html') );
	}
	*/
	
	// 记录提交
	public function recordset_handler(){
		$info = array(
			'content'=>$this->gf('record_content'),
			'addtime'=>date('Y-m-d H:i:s'),
			'admin'=>$this->admin_username,
			'tsid'=>$this->gf('tsid'),
			'rurl'=>$this->gf('rurl'),
			'status'=>$this->gf('status')
		);
		
		$this->load->model('sida/tousu_recordset_model');
		$this->load->model('sida/tousuModel', 'tousu_model');
		
		try {
			$this->tousu_recordset_model->add($info);	// 添加一条反馈记录
			
			// 更新投诉状态 
			$this->tousu_model->edit(array(
				'id'=>$info['tsid'],
				'status'=>$info['status']
			));
			
			$this->alert('提交成功', $info['rurl']);
		} catch(Exception $e) {
			$this->alert($e->getMessage());
		}
	}
	
	// 记录删除
	public function record_delete(){
		$info = array(
			'id'=>$this->gr('id'),
			'rurl'=>$this->gr('r')
		);
		$this->load->model('sida/tousu_recordset_model');
		try{
			$this->tousu_recordset_model->delete( $info['id'] );
			$this->alert('', $info['rurl']);
		}catch(Exception $e){
			$this->alert( $e->getMessage() );
		}
	}
	
	// 编辑记录
	public function record_edit(){
		
		$info = array(
			'id'=>$this->gr('id'),
			'rurl'=>$this->gr('r')
		);
		$this->load->model('sida/tousu_recordset_model');
		$record = $this->tousu_recordset_model->get_record($info['id'], array(
			'fields'=>'id, tsid, detail, addtime, admin, recycle'
		));
		
		$this->tpl->assign('record', $record);
		$this->tpl->assign('info', $info);
		$this->tpl->assign('module', 'record_edit');
		
		$this->tpl->display( $this->get_tpl('sida/tousu/record_edit.html') );
		
	}
	
	public function record_edit_handler(){
		$info = $this->get_form_data();
		$this->load->model('sida/tousu_recordset_model');
		try{
			$this->tousu_recordset_model->edit($info);
			$this->alert('提交成功', $info['rurl']);
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
	}
	
	// 投诉信息删除到回收站
	public function delete(){
		
		$info = array(
			'id'=>$this->gr('id'),
			'rurl'=>$this->gr('r')
		);
		
		try{
			$this->tousu_model->move_to_recycle($info['id']);
			$this->alert('', $info['rurl']);
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
		
	}
	
	// 从回收站还原
	public function reduction(){
		$info = array(
			'id'=>$this->gr('id'),
			'rurl'=>$this->gr('r')
		);
		
		try{
			$this->tousu_model->reduction($info['id']);
			$this->alert('', $info['rurl']);
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
	}
	
}

?>