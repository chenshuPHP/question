<?php

class cust_index extends cust_base {
	
	public function home()
	{
		$this->load->helper('url');
		redirect( $this->get_cust_url('/userinfo/home') );
	}
	
	
	
}

?>