<?php

	class Shipin extends MY_Controller {
		function __construct(){
			parent::__construct();
		}
		function index($page=1){
			
			$this->tpl->caching = true;
			$this->tpl->cache_lifetime = 2*60*60;	// 2 小时
			$cache_dir = $this->tpl->cache_dir . 'shipin/list/';	// 设置这个模版的缓存目录
			$this->tpl->cache_dir = $cache_dir;
			$cache_id = $page;
			$tpl = 'shipin/index.html';
			
			if(! $this->tpl->isCached($tpl, $cache_id) ){
			
				$this->load->model('Shipin/ShipinModel', 'spm');
				
				$settings = array(
					'size'=>20,
					'page'=>$page
				);
				
				$res = $this->spm->getList($settings);
				if( !$res['list'] ){
					show_404();
				}
				$count = $res['count'];
				$this->load->library('pagination');
				$this->pagination->baseUrl = $this->config->item('curr_base_url') . '/video/index.html';
				$this->pagination->currentPage = $settings['page'];
				$pageCount = $this->pagination->getPageCount($settings['size'], $count);
				$this->pagination->pageCount = $pageCount;
				$pagination = $this->pagination->toString(true);
				
				$this->tpl->assign('pagination', $pagination);
				$this->tpl->assign('list', $res['list']);
				$this->tpl->display($tpl, $cache_id);
			
			} else {
				$this->tpl->display($tpl, $cache_id);
				echo('<!-- cached -->');
			}
				
			
		}
		
		function view($id){
			
			
			$this->tpl->caching = true;
			$this->tpl->cache_lifetime = 10*60*60;	// 10 小时
			$cache_dir = $this->tpl->cache_dir . 'shipin/view/';	// 设置这个模版的缓存目录
			$this->tpl->cache_dir = $cache_dir;
			$cache_id = $id;
			$tpl = 'shipin/view.html';
			if(! $this->tpl->isCached($tpl, $cache_id) ){
			
				$this->load->model('Shipin/ShipinModel', 'spm');
				$object = $this->spm->getObject($id);	// 获取对象的详细信息
				if( !$object ){ show_404(); }
				$this->tpl->assign('object', $object);
				
				// 获取随机视频
				$rnd_list = $this->spm->getRndList($id, 10);
				$this->tpl->assign('rnd_list', $rnd_list);
				$this->tpl->display($tpl, $cache_id);
			} else {
				$this->tpl->display($tpl, $cache_id);
				echo('<!-- cached -->');
			}
			
		}
		
	}







