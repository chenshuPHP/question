<?php

// 装修预约
class publish_deco extends publish_base {

	public function __construct(){
		parent::__construct();
	}
	
	public function validate(){
		
		$name = strtolower( $this->gr('n') );
		
		session_start();
		
		$this->load->library('kaocode');
		$this->kaocode->doImg();
		
		$_SESSION[$name] = $this->kaocode->getCode();
		
	}
	
	// 统计数据
	public function get_total_count()
	{
		$this->load->model('publish/pubModel', 'pub_model');
		$this->load->model('download/res_download_model');
		
		$info = $this->pub_model->get_total_count();
		$total = $info['count2'];
		
		$download_total = $this->res_download_model->get_download_count( array(
			'type'		=> array('contract', 'budget')
		) );
		
		echo( json_encode( array(
			'yy_total'			=> $total,
			'download_total'		=> $download_total
		) ) );
		
	}
}


?>