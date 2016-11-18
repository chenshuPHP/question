<?php

class mobile_complaint_detail extends mobile_complaint_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		
		//if( $this->gr('v') == '2016' ){
			
			$tpl = $this->get_tpl('complaint/detail.html');
			
			$id = $this->gr('id');
			$this->load->model('sida/TousuModel', 'complaint_model');
			$this->load->model('sida/tousu_recordset_model');
			$this->load->model('sida/tousu_image_model');
			$this->load->library('thumb');
			
			$complaint = $this->complaint_model->getSingle($id, array(
				'fields'=>'id, title, username, tel, danwei, mobile, content, classname, puttime, address, fangtype, mianji, zaojia, he, xie, xingshi'
			));
			
			// 获取 投诉的图片 ，返回数组 array 
			$img = array(
					'fields'	=>	'id,tsid,path',
					'num'		=>	0,
					'key_name'	=>	'images'
				);
			$this->tousu_image_model->getImages($complaint,$img);
			
			//  获取图片 详细 信息
			if( ! empty( $complaint['images'] ) )
			{
				foreach( $complaint['images'] as $k => $v ){
					$complaint['images'][$k]['info'] = $this->thumb->getFileInfo($v['path']);
				}
			}else{ $complaint['images'] = array(); }
			
			var_dump2($complaint);
			
			$this->tpl->assign('complaint', $complaint);
			
			$this->tpl->assign('page_name', '投诉受理');
			$this->tpl->assign('title', $complaint['title']);
			
			// 本站处理结果
			$recordset = $this->tousu_recordset_model->get_recordset($complaint['id']);
			$this->tpl->assign('recordset', $recordset);
			
			// 从 WWW 加载留言
			$this->load->library('sync');
			$forum = $this->sync->ajax(array(
				'url'		=> $this->get_complete_url( '/sida/tsforum/gets?tsid=' . $complaint['id'] . '&size=4&style=mobile' ),
				'type'		=> 'GET',
				'dataType'	=> 'string'
			));
			
			$this->tpl->assign('forum', $forum);
			$this->tpl->display($tpl);
			
	}
	
	public function getforum()
	{
		$id = $this->gf('id');
		$page = $this->gf('page');
		
		// 从 WWW 加载留言
		$this->load->library('sync');
		$forum = $this->sync->ajax(array(
			'url'		=> $this->get_complete_url( '/sida/tsforum/gets?tsid=' . $id . '&page='. $page .'&size=4&style=mobile' ),
			'type'		=> 'GET',
			'dataType'	=> 'string'
		));
		
		echo( $forum );
		
	}
	
	
}


?>