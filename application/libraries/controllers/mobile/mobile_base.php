<?php 


if(!defined('BASEPATH')) exit('禁止直接浏览');

class mobile_base extends MY_Controller {
	
	public $mobile_url = '';
	public $mobile_res_url = '';
	public $template_directory = '';
	
	public function __construct(){
		parent::__construct();
		$this->load->model('mobile/mobile_url_model');
		$this->mobile_url = $this->mobile_url_model->get_base_url();
		
		
		// 2016-03-23
		$urls = $this->config->item('url');
		$this->mobile_res_url = rtrim($urls['res'], '/') . '/mobile/2016/';
		// $this->mobile_res_url = 'http://www.shzh.net/include/test2016/';
		$this->template_directory = 'mobile/';
		
		$this->tpl->assign('page_name', 'untitle');
		
		$this->tpl->assign('mobile_url', $this->mobile_url);
		$this->tpl->assign('mobile_res_url', $this->mobile_res_url);
		$this->tpl->assign('template_directory', $this->template_directory);
		
		$this->tpl->registerPlugin('modifier', 'get_mobile_url', array($this, 'get_mobile_url'));
		$this->tpl->registerPlugin('modifier', 'mobile_res_url', array($this, 'get_mobile_res_url'));
		
	}
	
	// 返回完整的模板路径
	protected function get_tpl($tpl){
		return 'mobile/' . ltrim($tpl, '/');
	}
	
	// 返回完整的缓存目录路径
	protected function get_cache_dir($dir = ''){
		$smarty_cache_dir = rtrim( $this->tpl->cache_dir, "\\" );
		if( $dir != '' ){
			$dir = trim(str_replace("/", "\\", $dir), "\\");
			$dir .= "\\";
		}
		return $smarty_cache_dir . "\\mobile\\" . $dir;
	}
	
	public function get_mobile_url($url){
		return rtrim($this->mobile_url, '/') . '/' . ltrim($url, '/');
	}
	
	public function get_mobile_res_url( $path = '' )
	{
		return rtrim($this->mobile_res_url, '/') . '/' . ltrim($path, '/');
	}
	
}


?>