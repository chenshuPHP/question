<?php

class article_yanshou extends article_base {
	
	public $channel_id = 1;		// 1 大类的 ID
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('channel_id', $this->channel_id);
	}
	
	public function home(){
		
		// Smarty 模版数据缓存
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60*60*3;	// 缓存三小时
		$cache_dir = $this->tpl->cache_dir . 'article/article_yanshou';
		$this->tpl->cache_dir = $cache_dir;
		
		$tpl = 'article/article_yanshou/home.html';
		
		if( ! $this->tpl->isCached($tpl) ){
		
		$this->load->model('article/category');
		$this->load->model('article/article');
		$this->load->library('thumb');
		$roots = $this->category->getChannels();
		$childs = $this->category->getChilds($this->channel_id);
		
		// 材料进场验收
		$c4 = array('id'=>4);
		$c4['cate'] = $this->category->getCurrCat($c4['id']);
		$c4['image'] = $this->article->get_cate_topics($c4['id'], 1, 'id, title, cid, clsid, imgpath');
		$c4['image']['thumb'] = $this->thumb->crop($c4['image']['imgpath'], 264, 219);
		$c4['arts'] = $this->article->getAttrNews($c4['id'], 3, array($c4['image']['id']));
		
		// 隐蔽工程（水电工）施工验收
		$c5 = array('id'=>5);
		$c5['cate'] = $this->category->getCurrCat($c5['id']);
		$c5['image'] = $this->article->get_cate_topics($c5['id'], 1, 'id, title, cid, clsid, imgpath');
		$c5['image']['thumb'] = $this->thumb->crop($c5['image']['imgpath'], 112, 144);
		$c5['arts'] = $this->article->getAttrNews($c5['id'], 5, array($c5['image']['id']));
		
		// 泥瓦工施工验收
		$c14 = array('id'=>14);
		$c14['cate'] = $this->category->getCurrCat($c14['id']);
		$c14['image'] = $this->article->get_cate_topics($c14['id'], 1, 'id, title, cid, clsid, imgpath');
		$c14['image']['thumb'] = $this->thumb->crop($c14['image']['imgpath'], 112, 144);
		$c14['arts'] = $this->article->getAttrNews($c14['id'], 5, array($c14['image']['id']));
		
		// 木工
		$c15 = array('id'=>16);
		$c15['cate'] = $this->category->getCurrCat($c15['id']);
		$c15['image'] = $this->article->get_cate_topics($c15['id'], 1, 'id, title, cid, clsid, imgpath');
		$c15['image']['thumb'] = $this->thumb->crop($c15['image']['imgpath'], 229, 84);
		$c15['arts'] = $this->article->getAttrNews($c15['id'], 8, array($c15['image']['id']));
		
		// 整体
		$c18 = array('id'=>18);
		$c18['cate'] = $this->category->getCurrCat($c18['id']);
		$c18['image'] = $this->article->get_cate_topics($c18['id'], 1, 'id, title, cid, clsid, imgpath');
		$c18['image']['thumb'] = $this->thumb->crop($c18['image']['imgpath'], 264, 219);
		$c18['arts'] = $this->article->getAttrNews($c18['id'], 3, array($c18['image']['id']));
		
		// 家装施工步骤
		$c122 = array('id'=>122);
		$c122['cate'] = $this->category->getCurrCat($c122['id']);
		$c122['image'] = $this->article->get_cate_topics($c122['id'], 1, 'id, title, cid, clsid, imgpath');
		$c122['image']['thumb'] = $this->thumb->crop($c122['image']['imgpath'], 112, 144);
		$c122['arts'] = $this->article->getAttrNews($c122['id'], 5, array($c122['image']['id']));
		
		//  违规曝光 
		$c273 = array('id'=>273);
		$c273['cate'] = $this->category->getCurrCat($c273['id']);
		$c273['image'] = $this->article->get_cate_topics($c273['id'], 1, 'id, title, cid, clsid, imgpath');
		$c273['image']['thumb'] = $this->thumb->crop($c273['image']['imgpath'], 112, 144);
		$c273['arts'] = $this->article->getAttrNews($c273['id'], 5, array($c273['image']['id']));

		// 装修验收必读
		$c280 = array('id'=>280);
		$c280['cate'] = $this->category->getCurrCat($c280['id']);
		$c280['image'] = $this->article->get_cate_topics($c280['id'], 1, 'id, title, cid, clsid, imgpath');
		$c280['image']['thumb'] = $this->thumb->crop($c280['image']['imgpath'], 229, 84);
		$c280['arts'] = $this->article->getAttrNews($c280['id'], 8, array($c280['image']['id']));

		$this->tpl->assign('roots', $roots);
		$this->tpl->assign('childs', $childs);
		
		$this->tpl->assign('c4', $c4);
		$this->tpl->assign('c5', $c5);
		$this->tpl->assign('c14', $c14);
		$this->tpl->assign('c15', $c15);
		$this->tpl->assign('c18', $c18);
		
		$this->tpl->assign('c122', $c122);
		$this->tpl->assign('c273', $c273);
		$this->tpl->assign('c280', $c280);
		$this->tpl->display($tpl);
			
		} else {
			$this->tpl->display($tpl);
			echo('<!-- from the cache -->');
		}
		
		
	}
	
	// 列表页面
	//public function arts($args){
	//	var_dump($args);
	//}
	
	
	
	
	
}

?>