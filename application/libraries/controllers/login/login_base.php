<?php

class login_base extends MY_Controller {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function get_tpl( $path )
	{
		return 'login/' . ltrim( $path, '/' );
	}
	
}

?>