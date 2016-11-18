<?php

class mobile_complaint_index extends mobile_complaint_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		
		$tpl = $this->get_tpl('complaint/home.html');
		
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>30
		);
		
		$this->load->model('sida/TousuModel', 'complaint_model');
		$this->load->model('mobile/mobile_url_model');
		$this->load->model('sida/tousu_config_model');
		
		$_cfg = array(
			'page'		=> $args['page'],
			'size'		=> $args['size'],
			'fields'	=> explode(',', 'id, username, title, danwei, puttime, status'),
			'where'		=> array()
		);
		
		$so = array(
			'st'		=> $this->gr('st')
		);
		
		$params = array();
		
		if( ! empty( $so['st'] ) )
		{
			$_cfg['where'][] = "status = '". $so['st'] ."'";
			$params[] = "st=" . $so['st'];
		}
		
		
		$result = $this->complaint_model->getList( $_cfg );
		$result['list'] = $this->mobile_url_model->format_batch_complaint( $result['list'] );
		$result['list'] = $this->tousu_config_model->attr_assign( $result['list'] );
		
		// 四个状态 
		$sts = $this->tousu_config_model->get_status_opts();
		$this->mobile_url_model->assign_complaint_status_url( $sts );
		// 获取已经解决的投诉统计
		$complete_count = $this->complaint_model->get_status_count('complete');
		
		// 分页
		$this->load->library('pagination');
		$_pgn_cfg = array(
			'currentPage'			=> $args['page'],
			'recordCount'			=> $result['count'],
			'pageSize'				=> $args['size'],
			'url_template'			=> $this->get_mobile_url('complaint?page=<{page}>'),
			'url_template_first'	=> $this->get_mobile_url('complaint')
		);
		if( count( $params ) != 0 )
		{
			$_pgn_cfg['url_template'] = $this->get_mobile_url('complaint?page=<{page}>&' . implode('&', $params));
			$_pgn_cfg['url_template_first'] = $this->get_mobile_url('complaint?' . implode('&', $params));
		}
		$pagination = $this->pagination->build($_pgn_cfg, array(
			'simple'	=> true,
			'opt'		=> array('select'	=> TRUE)
		));
		
		
		//var_dump2( $sts );
		//  已受理总数 status_count
		$this->tpl->assign('status_count',$this->complaint_model->get_all_count());
		$this->tpl->assign('sts', $sts);
		$this->tpl->assign('so', $so);
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('page_name', '投诉受理');
		$this->tpl->assign('title', '投诉受理');
		$this->tpl->assign('complete_count', $complete_count);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->display( $tpl );
			
	}
	
	
}


?>