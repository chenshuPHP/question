<?php

// header("Access-Control-Allow-Origin:*");

class mobile_tmp extends mobile_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function upload()
	{
		$this->tpl->display( $this->get_tpl('tmp/upload.html') );
	}
	
	public function handler()
	{
		
		var_dump( $_POST );
		var_dump( $_FILES );
		
	}
	
}


?>