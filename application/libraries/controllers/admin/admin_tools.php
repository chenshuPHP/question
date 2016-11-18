<?php
class admin_tools extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 短信用量
	public function mobile_message(){
		$this->load->library('send_message');
		$overage = trim( $this->send_message->get_overage() );
		$overage = simplexml_load_string($overage);
		//var_dump($overage);
		if( $overage->error == 0 ){
			$count = $overage->message;
			$count = (float)$count;
			echo("短信剩余". ($count*10) ."条");
		} else {
			echo("错误：" . $overage->message);
		}
		//$count = $overage->message;
	}
	
}
?>