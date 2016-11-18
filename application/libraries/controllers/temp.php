<?php

// 测试页面
class temp extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		
		$this->load->library('send_message');
		
		// $res = $this->send_message->send('您的验证码是 489623 如有疑问请拨打 4007285580', '15002122397');
		
		var_dump( $res );
		
	}
	
}
?>