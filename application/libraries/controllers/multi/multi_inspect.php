<?php

class multi_inspect extends multi_base {
	
	private $indexs = NULL;
	
	public function __construct(){
		parent::__construct();
		$this->indexs = array(
			array('key'=>1, 'name'=>'上海住宅装饰装修验收标准'),
			array('key'=>2, 'name'=>'前言'),
			array('key'=>3, 'name'=>'范围'),
			array('key'=>4, 'name'=>'规范性引用文件'),
			array('key'=>5, 'name'=>'基本规定'),
			array('key'=>6, 'name'=>'给排水管道'),
			array('key'=>7, 'name'=>'电气'),
			array('key'=>8, 'name'=>'抹灰'),
			array('key'=>9, 'name'=>'镶贴'),
			array('key'=>10, 'name'=>'木制品'),
			array('key'=>11, 'name'=>'门窗'),
			array('key'=>12, 'name'=>'吊顶与分隔'),
			array('key'=>13, 'name'=>'花饰'),
			array('key'=>14, 'name'=>'涂装'),
			array('key'=>15, 'name'=>'裱糊'),
			array('key'=>16, 'name'=>'卫浴设备'),
			array('key'=>17, 'name'=>'室内空气质量'),
			array('key'=>18, 'name'=>'质量验收及判定'),
			array('key'=>19, 'name'=>'装修质量保证'),
			array('key'=>20, 'name'=>'附录A')
		);
	}
	
	private function _get_index($id){
		foreach($this->indexs as $item){
			if( $item['key'] == $id ) return $item;
		}
	}
	
	public function __remap($args = array()){
		
		if( count($args) == 1 && $args[0] == 'home' ){
			$args[0] = 1;
		}
		
		if( preg_match('/^menu_\d+$/', $args[0]) ){
			$args[0] = strtolower($args[0]);
			$args[0] = str_replace('menu_', '', $args[0]);
		}
		
		if( count($args) == 1 && preg_match('/^\d+$/', $args[0]) ){
			$this->_detail($args);
		}
		
	}
	
	private function _detail($args){
		$id = $args[0];
		$index = $this->_get_index($id);
		$this->tpl->assign('indexs', $this->indexs);
		$this->tpl->assign('index', $index);
		$this->tpl->assign('content', $this->_get_contents($id));
		
		// 协会问吧
		$this->load->model('sida/ask_model');
		$ask_latest = $this->ask_model->get_isans_latest(1);
		
		$ans = $ask_latest->ans[0];
		$ask_latest->ans = $this->encode->removeHtml($ans->detail);
		
		$asks = $this->ask_model->get_asks(
			array(
				'top'=>8,
				'noids'=>implode(', ', array($ask_latest->id)),
				'sort'=>'show_count'
			)
		);
		
		
		// 监理日记
		$this->load->model('diary/diary_model');
		$diary_images = $this->diary_model->get_list("select top 2 id, title, address, detail from diary order by id desc");
		$diary_images = $diary_images['list'];
		foreach($diary_images as $key=>$val){
			$content = $this->encode->htmldecode($val['detail']);
			$diary_images[$key]['image'] = $this->diary_model->get_thumb_image( $content );
			unset($diary_images[$key]['detail']);
		}
		$this->tpl->assign('diary_images', $diary_images);
		$diarys = $this->diary_model->get_list("select top 4 id, title, address from diary where id not in (". $diary_images[0]['id'] .",". $diary_images[1]['id'] .") order by id desc");
		$this->tpl->assign('diarys', $diarys['list']);


		
		$this->tpl->assign('ask_latest', $ask_latest);
		$this->tpl->assign('asks', $asks->list);
		
		
		$this->tpl->display('multi/inspect/template.html');
	}
	
	private function _get_contents($id){
		$content = $this->tpl->fetch('multi/inspect/contents/menu_'. $id .'.txt');
		$content = str_replace('src="images/', 'src="'. $this->res_multi_url .'inspect/images/', $content);
		return $content;
	}
	
}

?>