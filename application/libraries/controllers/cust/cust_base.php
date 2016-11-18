<?php

// 业主中心
class cust_base extends MY_Controller {
	
	
	protected $cust_url = '';
	protected $cust_res_url = '';
	protected $template_directory = '';
	
	protected $user = NULL;
	
	public function __construct()
	{
		// var_dump( $_COOKIE );
		
		session_start();
		
		parent::__construct();
		
		$urls = config_item('url');
		
		$this->cust_url = $urls['cust'];
		$this->cust_res_url = rtrim($urls['res'], '/') . '/member/cust/';
		$this->template_directory = 'member/cust/';
		
		$this->tpl->assign('cust_url', $this->cust_url);
		$this->tpl->assign('cust_res_url', $this->cust_res_url);
		$this->tpl->assign('template_directory', $this->template_directory);
		
		$this->tpl->registerPlugin('modifier', 'get_cust_url', array($this, 'get_cust_url'));
		$this->tpl->registerPlugin('modifier', 'cust_res_url', array($this, 'cust_res_url'));
		
		if( isset( $_SESSION['CUST_USER'] ) )
		{
			$this->user = $_SESSION['CUST_USER'];
		}
		else
		{
			$this->load->model('cust/cust_model');
			try
			{
				$_SESSION['CUST_USER'] = $this->cust_model->get_user();
			}
			catch(Exception $e)
			{
				//echo( $e->getMessage() );
				//echo(' <a href="'. $urls['www'] .'">返回主页</a>');
				//exit();
				$this->load->helper( 'url' );
				redirect( rtrim( $urls['www'], '/' ) . '/login.html' );
			}
			
			$this->user = $_SESSION['CUST_USER'];
		}
		
		$this->_format_user();	// 可能需要对数据进行格式化
		
		$this->tpl->assign('user', $this->user);
		
		
		$this->tpl->assign('title', '用户中心');
		$this->tpl->assign('keywords', '上海装潢网');
		$this->tpl->assign('description', '上海装潢网');
		// $this->tpl->assign('53kf', '1');
		$this->tpl->assign('decorate', '1');
	}
	
	private function _format_user()
	{
		if( ! preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $this->user['company_date']) )
		{
			$this->user['company_date'] = '';
		}
	}
	
	protected function get_tpl($tpl)
	{
		return 'member/cust/' . ltrim($tpl, '/');
	}
	
	public function cust_res_url($path = '')
	{
		return rtrim($this->cust_res_url, '/') . '/' . ltrim($path, '/');
	}
	
	public function get_cust_url($uri)
	{
		return rtrim($this->cust_url, '/') . '/' . ltrim($uri, '/');
	}
	
}

?>