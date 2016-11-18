<?php
class video_view extends video_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home($args = array()){
		
		$id = $args[0];
		
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 10 * 60 * 60;					// 10 小时
		$cache_dir = $this->tpl->cache_dir . 'shipin/view/';	// 设置这个模版的缓存目录
		$this->tpl->cache_dir = $cache_dir;
		$cache_id = $id;
		
		$tpl = 'shipin/detail.html';
		
		if(! $this->tpl->isCached($tpl, $cache_id) ){
			
			$this->load->model('shipin/shipinmodel', 'video_model');
			$video = $this->video_model->get($id, array(
				'fields'=>'id, title, shorttitle, description, introduce, videourl, localoption, content, showcount, addtime, thumbimg'
			));
			
			if( ! $video ){
				show_404();
				exit();
			}
			
			$this->tpl->assign('video', $video);
			$list = $this->video_model->getRndList($id, 10);
			
			//seo
			$this->tpl->assign('title', $video['title']);
			$this->tpl->assign('description', $video['introduce']);
			
			$this->tpl->assign('list', $list);
			
			$this->tpl->display( $tpl, $cache_id );
		
		} else {
			$this->tpl->display($tpl, $cache_id);
			echo('<!-- cached -->');
		}
		
	}
	
	public function update_show_count(){
		
		$id = $this->gr('id');
		
		if( ! preg_match('/^\d+$/', $id) ) return;
		
		$this->load->model('shipin/shipinmodel', 'video_model');
		
		
		try{
			$this->video_model->update_show_count( $id );
			echo('success');
		}catch(Exception $e){
			echo($e->getMessage());
		}
		
	}
	
	
}

?>