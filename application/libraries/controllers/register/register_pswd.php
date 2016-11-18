<?php

// 密码服务

class register_pswd extends register_base {

	public function __construct(){
		parent::__construct();
	}
	
	// 找回密码
	public function get_pswd(){
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: http://www.shzh.net/main/zhmm.asp");
	}

}

?>