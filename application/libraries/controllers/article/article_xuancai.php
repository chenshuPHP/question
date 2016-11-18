<?php

class article_xuancai extends article_base {
	
	public $channel_id = 97;		// 97 大类的 ID
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('channel_id', $this->channel_id);
	}
	
	public function home(){
		
		
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60*60*3;	// 缓存3个小时
		$cache_dir = $this->tpl->cache_dir . 'article/article_xuancai';
		$this->tpl->cache_dir = $cache_dir;
		
		$tpl = 'article/article_xuancai/home.html';
		
		if( ! $this->tpl->isCached($tpl) ){
			
		
		$this->load->model('article/category');
		$this->load->model('article/article');
		$this->load->library('thumb');
		$roots = $this->category->getChannels();
		$childs = $this->category->getChilds($this->channel_id);
		
		// 装修选材
		$xuancai = array('name'=>'装修选材');
		$xuancai['childs'] = $this->category->getCats(array(98,99,100,101,102,103,104));
		$xuancai['a'] = array();
		$xuancai['a']['cate'] =$xuancai['childs'][0];
		$xuancai['a']['images'] = $this->article->get_cate_topics($xuancai['a']['cate']['id'], 3, 'id, title, cid, clsid, imgpath, description');
		foreach($xuancai['a']['images'] as $key=>$value){
			$xuancai['a']['images'][$key]['thumb'] = $this->thumb->crop($value['imgpath'], 255, 153);
			$xuancai['a']['images'][$key]['thumb2'] = $this->thumb->crop($value['imgpath'], 78, 73);
		}
		$xuancai['b'] = array();
		$xuancai['b']['cate'] =$xuancai['childs'][1];
		$xuancai['b']['image'] = $this->article->get_cate_topics($xuancai['b']['cate']['id'], 1, 'id, title, cid, clsid, imgpath');
		$xuancai['b']['image']['thumb'] = $this->thumb->crop($xuancai['b']['image']['imgpath'], 117, 150);
		$xuancai['b']['arts'] = $this->article->getAttrNews($xuancai['b']['cate']['id'], 6, array($xuancai['b']['image']['id']));
		
		$xuancai['c'] = array();
		$xuancai['c']['cate'] =$xuancai['childs'][2];
		$xuancai['c']['image'] = $this->article->get_cate_topics($xuancai['c']['cate']['id'], 1, 'id, title, cid, clsid, imgpath');
		$xuancai['c']['image']['thumb'] = $this->thumb->crop($xuancai['c']['image']['imgpath'], 117, 150);
		$xuancai['c']['arts'] = $this->article->getAttrNews($xuancai['c']['cate']['id'], 6, array($xuancai['c']['image']['id']));
		
		$xuancai['d'] = array();
		$xuancai['d']['cate'] = $xuancai['childs'][3];
		$xuancai['d']['images'] = $this->article->get_cate_topics($xuancai['d']['cate']['id'], 3, 'id, title, cid, clsid, imgpath, description');
		foreach($xuancai['d']['images'] as $key=>$value){
			$xuancai['d']['images'][$key]['thumb'] = $this->thumb->crop($value['imgpath'], 101, 68);
		}
		
		// 软装选材
		$ruanzhuang = array('name'=>'软装选材');
		$ruanzhuang['childs'] = $this->category->getCats(array(105, 106, 107, 108, 257));
		$ruanzhuang['a'] = array();
		$ruanzhuang['a']['cate'] = $ruanzhuang['childs'][0];
		$ruanzhuang['a']['images'] = $this->article->get_cate_topics($ruanzhuang['a']['cate']['id'], 3, 'id, title, cid, clsid, imgpath, description');
		foreach($ruanzhuang['a']['images'] as $key=>$value){
			$ruanzhuang['a']['images'][$key]['thumb'] = $this->thumb->crop($value['imgpath'], 255, 153);
			$ruanzhuang['a']['images'][$key]['thumb2'] = $this->thumb->crop($value['imgpath'], 78, 73);
		}
		$ruanzhuang['b'] = array();
		$ruanzhuang['b']['cate'] = $ruanzhuang['childs'][1];
		$ruanzhuang['b']['image'] = $this->article->get_cate_topics($ruanzhuang['b']['cate']['id'], 1, 'id, title, cid, clsid, imgpath');
		$ruanzhuang['b']['image']['thumb'] = $this->thumb->crop($ruanzhuang['b']['image']['imgpath'], 117, 150);
		$ruanzhuang['b']['arts'] = $this->article->getAttrNews($ruanzhuang['b']['cate']['id'], 6, array($ruanzhuang['b']['image']['id']));
		
		$ruanzhuang['c'] = array();
		$ruanzhuang['c']['cate'] = $ruanzhuang['childs'][2];
		$ruanzhuang['c']['image'] = $this->article->get_cate_topics($ruanzhuang['c']['cate']['id'], 1, 'id, title, cid, clsid, imgpath');
		$ruanzhuang['c']['image']['thumb'] = $this->thumb->crop($ruanzhuang['c']['image']['imgpath'], 117, 150);
		$ruanzhuang['c']['arts'] = $this->article->getAttrNews($ruanzhuang['c']['cate']['id'], 6, array($ruanzhuang['c']['image']['id']));
		
		$ruanzhuang['d'] = array();
		$ruanzhuang['d']['cate'] = $ruanzhuang['childs'][3];
		$ruanzhuang['d']['images'] = $this->article->get_cate_topics($ruanzhuang['d']['cate']['id'], 3, 'id, title, cid, clsid, imgpath, description');
		foreach($ruanzhuang['d']['images'] as $key=>$value){
			$ruanzhuang['d']['images'][$key]['thumb'] = $this->thumb->crop($value['imgpath'], 101, 68);
		}
		
		
		// 家居饰品
		$jiaju = array('name'=>'家具选材');
		$jiaju['childs'] = $this->category->getCats(array(258, 259, 261, 267));
		$jiaju['a'] = array();
		$jiaju['a']['cate'] = $jiaju['childs'][0];
		$jiaju['a']['images'] = $this->article->get_cate_topics($jiaju['a']['cate']['id'], 3, 'id, title, cid, clsid, imgpath, description');
		foreach($jiaju['a']['images'] as $key=>$value){
			$jiaju['a']['images'][$key]['thumb'] = $this->thumb->crop($value['imgpath'], 255, 153);
			$jiaju['a']['images'][$key]['thumb2'] = $this->thumb->crop($value['imgpath'], 78, 73);
		}
		$jiaju['b'] = array();
		$jiaju['b']['cate'] = $jiaju['childs'][1];
		$jiaju['b']['image'] = $this->article->get_cate_topics($jiaju['b']['cate']['id'], 1, 'id, title, cid, clsid, imgpath');
		$jiaju['b']['image']['thumb'] = $this->thumb->crop($jiaju['b']['image']['imgpath'], 117, 150);
		$jiaju['b']['arts'] = $this->article->getAttrNews($jiaju['b']['cate']['id'], 6, array($jiaju['b']['image']['id']));
		
		$jiaju['c'] = array();
		$jiaju['c']['cate'] = $jiaju['childs'][2];
		$jiaju['c']['image'] = $this->article->get_cate_topics($jiaju['c']['cate']['id'], 1, 'id, title, cid, clsid, imgpath');
		$jiaju['c']['image']['thumb'] = $this->thumb->crop($jiaju['c']['image']['imgpath'], 117, 150);
		$jiaju['c']['arts'] = $this->article->getAttrNews($jiaju['c']['cate']['id'], 6, array($jiaju['c']['image']['id']));
		
		$jiaju['d'] = array();
		$jiaju['d']['cate'] = $jiaju['childs'][3];
		$jiaju['d']['images'] = $this->article->get_cate_topics($jiaju['d']['cate']['id'], 3, 'id, title, cid, clsid, imgpath, description');
		foreach($jiaju['d']['images'] as $key=>$value){
			$jiaju['d']['images'][$key]['thumb'] = $this->thumb->crop($value['imgpath'], 101, 68);
		}
		
		$this->tpl->assign('roots', $roots);
		$this->tpl->assign('childs', $childs);
		$this->tpl->assign('xuancai', $xuancai);
		$this->tpl->assign('ruanzhuang', $ruanzhuang);
		$this->tpl->assign('jiaju', $jiaju);
		$this->tpl->display($tpl);
		
		} else {
			$this->tpl->display($tpl);
			echo('<!-- from the cache -->');
		}
		
	}
	
}

?>