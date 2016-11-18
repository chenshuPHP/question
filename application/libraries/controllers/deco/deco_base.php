<?php

class deco_base extends MY_Controller {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function get_tpl( $tpl )
	{
		return 'company/' . ltrim($tpl, '/');
	}
	
}

?>