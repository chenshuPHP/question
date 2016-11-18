<?php

class dev extends MY_Controller {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		if( $this->_get_cookie() )
		{
			echo "当前是测试数据库, 使用<a href='/dev/logout'>正式版</a>";
		}
		else
		{
			echo "当前是正式版数据库, 使用<a href='/dev/login'>测式版</a>";
		}
	}
	
	private function _get_cookie()
	{
		if( ! isset( $_COOKIE['is_shzh_developer'] ) ) return FALSE;
		if( $_COOKIE['is_shzh_developer'] != 1 )  return FALSE;
		return TRUE;
	}

	public function login()
	{
		setcookie('is_shzh_developer', '1', time() + 3600 * 8, '/', 'shzh.net');
		$this->load->helper('url');
		redirect( 'http://assist.shzh.net/dev/index' );
	}
	
	public function logout()
	{
		setcookie('is_shzh_developer', NULL, -1, '/', 'shzh.net');
		$this->load->helper('url');
		redirect( 'http://assist.shzh.net/dev/index' );
	}
	
}

?>