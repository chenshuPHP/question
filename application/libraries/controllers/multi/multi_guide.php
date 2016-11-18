<?php

class multi_guide extends multi_base {
	
	private $indexs = NULL;
	
	public function __construct(){
		parent::__construct();
		$this->indexs = array(
			array('key'=>1, 'name'=>'装修施工规范'),
			array('key'=>2, 'name'=>'总则'),
			array('key'=>3, 'name'=>'术语'),
			array('key'=>4, 'name'=>'基本规定'),
			array('key'=>5, 'name'=>'防火安全'),
			array('key'=>6, 'name'=>'室内环境污染控制'),
			array('key'=>7, 'name'=>'防水工程'),
			array('key'=>8, 'name'=>'抹灰工程'),
			array('key'=>9, 'name'=>'吊顶工程'),
			array('key'=>10, 'name'=>'轻质隔墙工程'),
			array('key'=>11, 'name'=>'门窗工程'),
			array('key'=>12, 'name'=>'细部工程'),
			array('key'=>13, 'name'=>'墙面铺装工程'),
			array('key'=>14, 'name'=>'涂料工程'),
			array('key'=>15, 'name'=>'地面铺装工程'),
			array('key'=>16, 'name'=>'卫生器具及管道安装工程'),
			array('key'=>17, 'name'=>'电器安装工程'),
			array('key'=>18, 'name'=>'附录A本规范用词说明')
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
		
		
		$this->tpl->display('multi/guide/menu_1.html');
	}
	
	private function _get_contents($id){
		return $this->tpl->fetch('multi/guide/contents/'. $id .'.txt.txt');
	}
	
	
	
	
}







































?>