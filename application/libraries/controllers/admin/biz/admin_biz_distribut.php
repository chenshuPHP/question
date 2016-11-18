<?php

// 订单派发控制器
class admin_biz_distribut extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 订单派发界面
	public function manage(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		$this->load->model('biz/biz_model');
		$this->load->model('company/company', 'deco_model');
		
		$vips = $this->deco_model->getSpecial('vip', array(
			'fields'=>array('username', 'company', 'shortname')
		));

		$biz = $this->biz_model->get_biz($id);
		$biz = $this->biz_model->company_assign_biz($biz);
		
		
		foreach($vips as $key => $value){
			$vips[$key]['exists'] = false;
			foreach($biz['distribut'] as $k=>$v){
				if( $v['user_info']['username'] == $value['username'] ){
					$vips[$key]['exists'] = true;
					break;
				}
			}
		}

		//var_dump($vips);
		
		$this->tpl->assign('biz', $biz);
		
		$this->tpl->assign('module', 'distribut');
		$this->tpl->assign('r', $r);

		$this->tpl->assign('vips', $vips);
		$this->tpl->display('admin/business/biz_distribut.html');
		
	}
	
	// 订单派发提交处理
	public function distribut(){
		$args = array(
			'users'			=> $this->gf('users'),
			'id'			=> $this->gf('id'),
			'addtime'		=> date('Y-m-d H:i:s'),
			'adduser'		=> $this->admin_username
		);

		//var_dump($args);
		//exit();

		$this->load->model('biz/biz_model');
		$result = array();
		try{
			
			$_result = $this->biz_model->distribut($args);
			// 给派发单位发短信, 二次派发不会发送
			$this->_distribut_mobile_message($args['id'], $_result['success_users']);
			$result['type'] = 'success';
			
		}catch(Exception $e){
			$result['type'] = 'error';
			$result['message'] = $e->getMessage();
		}
		echo(json_encode($result));
	}
	
	// 派发短信提醒
	private function _distribut_mobile_message($bizid, $users)
	{
		
		$this->load->model('company/company', 'company_model');
		
		$this->load->model('publish/deco_orders_model');
		
		$sql = "select fullname, mobile from [". $this->deco_orders_model->table_name ."] where id = ( select bizid from [business] where id = '". $bizid ."' )";
		$order = $this->deco_orders_model->get( $sql, array(
			'format'		=> FALSE
		) );
		if( ! $order ) return;
		if( ! preg_match('/^1\d{10}$/', $order['mobile']) ) return;
		$message = '新客户'. $order['fullname'] .'装修订单已派发，请火速联系业主并更新后台订单状态填写联系情况【上海装潢网】';
		$decos = $this->company_model->get_list("select username, mobile from [company] where username in ('". implode("','", $users) ."')");
		if( ! $decos ) return;
		$decos = $decos['list'];
		$this->load->library('send_message');
		$this->load->model('sms_model');
		foreach($decos as $item)
		{
			if( preg_match('/^1\d{10}$/', $item['mobile']) )
			{
				if( $this->send_message->send($message, $item['mobile']) === TRUE )
				{
					$this->sms_model->add( array(
						'category'		=> 'biz',
						'tid'			=> $bizid,
						'message'		=> $message,
						'addtime'		=> date('Y-m-d H:i:s'),
						'mobile'		=> $item['mobile'],
						'validate_code'	=> '',
						'username'		=> $this->admin_username
					) );
				}
			}
		}
	}
	
	// 取消派发
	public function cancel_distribut(){
		
		$args = array(
			'id'=>$this->gf('id'),
			'admin'=>$this->admin_username
		);
		
		$this->load->model('biz/biz_model');
		$result = array();
		try{
			$this->biz_model->cancel_distribut($args);
			$result['type'] = 'success';
		}catch(Exception $e){
			$result['type'] = 'error';
			$result['message'] = $e->getMessage();
		}
		
		echo(json_encode($result));
		
	}
	
	// 设置订单签约和返点信息
	public function rebate(){
		$info = array(
			'id'=>$this->gf('id'),
			'agree_date'=>$this->gf('agree_date'),
			'rebate'=>$this->gf('rebate')
		);
		$this->load->model('biz/biz_model');
		$result = array();
		try{
			$this->biz_model->set_distribut_rebate($info);
			$result['type'] = 'success';
		}catch(Exception $e){
			$result['type'] = 'error';
			$result['message'] = $e->getMessage();
		}
		echo( json_encode($result) );
	}
	
}

?>