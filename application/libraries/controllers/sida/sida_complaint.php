<?php

class sida_complaint extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 从协会新增投诉信息
	public function sync(){
		
		$data = $this->get_form_data();
		
		// 需要添加协会通信 key 作为安全身份， 这里暂未添加
		$this->load->model('sida/tousuModel', 'tousu_model');
		
		$result = array();
		
		try{
			$id = $this->tousu_model->insert($data);
			$result['state'] = 'success';
			$result['id'] = $id;
		}catch(Exception $e){
			$result['state'] = 'error';
			$result['message'] = $e->getMessage();
		}
		
		echo( json_encode($result) );
		
	}
	
	// 列表传输到协会
	public function sync_collect(){
		
		$data = $this->get_form_data();
		
		$data = array_merge(array(
			'fields'		=> 'id, username, title, classname, content, puttime, status',
			'size'			=> 20,
			'page'			=> 1,
			'ext_desc'		=> TRUE
		), $data);

		$this->load->model('sida/TousuModel', 'tousu_model');
		$this->load->model('sida/tousu_config_model');

		// 2016-09-29 新增支持传递读取字段
		$fields = $data['fields'];

		$sql = "select * from ( select ". $fields .", num = row_number() over(order by puttime desc) from [sendmessage1] where recycle = 0 ) as temp where num between ". ( ($data['page'] - 1) * $data['size'] + 1 ) ." and " . ( $data['page'] * $data['size'] );
		$sql_count = "select count(*) as icount from [sendmessage1] where recycle = 0";
		
		$result = $this->tousu_model->get_list($sql, $sql_count, array(
			'format_display_truename'=>true
		));
		
		// 附加投诉处理状态
		$result['list'] = $this->tousu_config_model->attr_assign($result['list'], array(
			'type'=>'status'
		));
		
		if( $data['ext_desc'] == TRUE )
		{
			foreach($result['list'] as $key=>$value){
				if( isset( $value['content'] ) )
				{
					$value['desc'] = $this->encode->get_text_description($value['content'], 35);
					unset($value['content']);
				}
				$result['list'][$key] = $value;
			}
	
		}
		
		echo(json_encode($result));
		
	}
	
	// 传输单个投诉信息到协会
	public function sync_single(){
		$data = $this->get_form_data();
		$this->load->model('sida/TousuModel', 'tousu_model');
		$this->load->model('sida/tousu_config_model');
		$this->load->model('sida/tousu_recordset_model');
		$complaint = $this->tousu_model->getSingle($data['id'], array(
			'fields'=>$data['fields']
		));
		
		// 附加投诉处理状态
		$complaint = $this->tousu_config_model->attr_assign($complaint, array(
			'type'=>'status'
		));
		
		if( $data['recordset'] ){
			$recordset = $this->tousu_recordset_model->get_recordset($data['id']);
			$complaint['recordset'] = $recordset;
		}
		
		
		echo( json_encode($complaint) );
	}
	
	// 将协会数据库中投诉信息 转移 到装潢网数据库
	// 上次执行 2016-03-10 因为有部分协会手机端没有同步
	/*
	public function move(){
		$this->load->library('mdb');
		// 读取协会投诉信息
		$this->mdb->select_db('xiehui');
		$sql = "select id, title, name, tel, deco_name, deco_tel, detail, type, addtime from [sz_tousu] where recycle = 0 and addtime > '2016-03-10'";
		$res = $this->mdb->query($sql);
		$this->mdb->select_db('shzh');
		foreach($res as $key=>$value){
			if( empty($value['title']) ){
				$value['title'] = '投诉 ' . $value['deco_name'] . ' ' . $value['type'];
			}
			
			$sql = "insert into sendmessage1(username, title, content, classname, tel, mobile, danwei, source, relation_id, puttime)".
			"values('". $value['name'] ."', '". $value['title'] ."', '". $value['detail'] ."', '". $value['type'] ."', '". $value['tel'] ."', '"
			. $value['deco_tel'] ."', '". $value['deco_name'] ."', 'snzsxh_mobile', '". $value['id'] ."', '". $value['addtime'] ."')";
			
			$this->mdb->insert($sql);
		}
		echo('done');
	}
	*/
	
	// 已经浏览记录
	public function viewed()
	{
		$tsid = $this->gf('id');
		
		// if( empty( $tsid ) ) $tsid = $this->gr('id');
		
		
		if( ! preg_match('/^\d+$/', $tsid) ) exit('参数错误');
		
		$cookie_name = 'tousu_viewed_ids';
		
		if( isset( $_COOKIE['tousu_viewed_ids'] ) )
		{
			$ids = $_COOKIE['tousu_viewed_ids'];
			$ids = explode(',', $ids);
		}
		else
		{
			$ids = array();
		}
		
		if( in_array($tsid, $ids) ) return;
		
		$this->load->model('sida/tousuModel', 'tousu_model');
		try
		{
			$this->tousu_model->viewed( $tsid );
			$ids[] = $tsid;
			$urls = config_item('url');
			setcookie($cookie_name, implode(',', $ids), time() + 3600, '/', $urls['domain']);
		}
		catch(Exception $e)
		{
			exit( $e->getMessage() );
		}
	}
	
}

?>