<?php

// 多元服务基类
	
class multi_base extends MY_Controller {
	
	public $multi_url;
	public $res_multi_url;
	
	public function __construct($args = array()){
		
		$settings = array(
			'decos'=>1,			// 是否加载装修公司
			'diaries'=>1		// 是否获取监理日记
		);
		
		$settings = array_merge($settings, $args);
		
		
		parent::__construct();
		$this->load->model('multi/multi_info_model');
		
		$url = $this->multi_info_model->get_url_info();
		
		
		if( $settings['decos'] == 1 ){
			$decos = $this->multi_info_model->get_koubei_decos();
			// 热门装修公司
			$this->tpl->assign('decos', $decos);
		}
		
		if( $settings['diaries'] == 1 ){
			$diarys = $this->multi_info_model->get_diarys();
			// 监理日记
			$this->tpl->assign('diarys', $diarys['list']);
		}


		
		$this->multi_url = $url['multi_url'];
		$this->res_multi_url = $url['res_multi_url'];
		
		$this->tpl->assign('multi_url', $this->multi_url);
		$this->tpl->assign('res_multi_url', $this->res_multi_url);
		
		
		
		// 隐藏客服
		// $this->tpl->assign('hide_kf', '1');
		
	}
	
}

?>