<?php

class article_sheji extends article_base {
	
	public $channel_id = 6;		// 6 大类的 ID
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('channel_id', $this->channel_id);
	}
	
	// 装修设计首页
	public function home(){
		
		$this->tpl->caching = true;
		
		$this->tpl->cache_lifetime = 60*60*3;	// 缓存3个小时
		$cache_dir = $this->tpl->cache_dir . 'article/article_sheji';
		$this->tpl->cache_dir = $cache_dir;
		
		$tpl = 'article/article_sheji/home.html';
		if( ! $this->tpl->isCached($tpl) ){
		
		
		$this->load->model('article/category');
		$this->load->model('article/article');
		$this->load->library('thumb');
		$roots = $this->category->getChannels();
		$childs = $this->category->getChilds($this->channel_id);
		
		// =========== 装修风格 ===========
		$c7 = array('id'=>7);
		$c7['cate'] = $this->category->getCurrCat($c7['id']);
		$c7['childs'] = $this->category->getChilds($c7['id'], 9);
		
			// 装修风格 => 西班牙风格(c264)最新文章
			$c264 = array('id'=>264);
			$c264['cate'] = $this->category->getCurrCat($c264['id']);
			$c264['art'] = $this->article->get_cate_topics($c264['id'], 1, 'id, title, cid, clsid, description, imgpath, keyword');
			$c264['art']['thumb'] = $this->thumb->crop($c264['art']['imgpath'], 340, 211);
			$c264['art']['keyword'] = $this->article->add_keywords_link($c264['art']['keyword']);
			
			// 装修风格 => 地中海风格(c52)
			$c52 = array('id'=>52);
			$c52['cate'] = $this->category->getCurrCat($c52['id']);
			$c52['image_art'] = $this->article->get_cate_topics($c52['id'], 1, 'id, title, cid, clsid, imgpath');
			$c52['image_art']['thumb'] = $this->thumb->crop($c52['image_art']['imgpath'], 120, 145);
			$c52['arts'] = $this->article->getAttrNews($c52['id'], 5, array($c52['image_art']['id']));
			
			// 装修风格 => 欧式风格(c49)
			$c49 = array('id'=>49);
			$c49['cate'] = $this->category->getCurrCat($c49['id']);
			$c49['image_art'] = $this->article->get_cate_topics($c49['id'], 1, 'id, title, cid, clsid, imgpath');
			$c49['image_art']['thumb'] = $this->thumb->crop($c49['image_art']['imgpath'], 120, 145);
			$c49['arts'] = $this->article->getAttrNews($c49['id'], 5, array($c49['image_art']['id']));
			
			// 装修风格 => 日韩风格(c323)
			$c323 = array('id'=>323);
			$c323['cate'] = $this->category->getCurrCat($c323['id']);
			$c323['arts'] = $this->article->getAttrNews($c323['id'], 6);
			
			// 装修风格 => 新古典(c51)
			$c51 = array('id'=>51);
			$c51['cate'] = $this->category->getCurrCat($c51['id']);
			$c51['arts'] = $this->article->getAttrNews($c51['id'], 6);
			
		// 装饰设计
		$deco_childs = $this->category->getCats( array(9, 19, 271, 284, 294) );
			
			// 装修话题c271
			$c271 = array('id'=>271);
			$c271['image_art'] = $this->article->getAttrNews(
				$this->category->get_all_childs_id($c271['id']), 1, array(), 'count', 'id, title, imgpath, clsid'
			);
			$c271['image_art']['thumb'] = $this->thumb->crop($c271['image_art']['imgpath'], 340, 364);
			$c271['arts'] = $this->article->getAttrNews(
				$this->category->get_all_childs_id($c271['id']), 4, array($c271['image_art']['id']), 'count', 'id, title, clsid'
			);
			// 装修户型c19
			$c19 = array('id'=>19);
			$c19['cate'] = $this->category->getCurrCat($c19['id']);
			$c19['childs'] = $this->category->getChilds($c19['id'], 5);
			foreach($c19['childs'] as $key=>$item){
				$item['image_art'] = $this->article->get_cate_topics($item['id'], 1, 'id, title, cid, clsid, imgpath');
				$item['image_art']['thumb'] = $this->thumb->crop($item['image_art']['imgpath'], 120, 120);
				$item['arts'] = $this->article->getAttrNews($item['id'], 4, array($item['image_art']['id']));
				$c19['childs'][$key] = $item;
			}
			// 装修用途c9
			$c9 = array('id'=>9);
			$c9['cate'] = $this->category->getCurrCat($c9['id']);
			$c9['childs'] = $this->category->getChilds($c9['id'], 4);
			foreach($c9['childs'] as $key=>$item){
				$item['image_art'] = $this->article->get_cate_topics($item['id'], 1, 'id, title, cid, clsid, imgpath');
				$item['image_art']['thumb'] = $this->thumb->crop($item['image_art']['imgpath'], 120, 145);
				$item['arts'] = $this->article->getAttrNews($item['id'], 5, array($item['image_art']['id']));
				$c9['childs'][$key] = $item;
			}
			// 装修构造c284
			$c284 = array('id'=>284);
			$c284['cate'] = $this->category->getCurrCat($c284['id']);
			$c284['childs'] = $this->category->getChilds($c284['id'], 4);
			foreach($c284['childs'] as $key=>$item){
				$item['image_art'] = $this->article->get_cate_topics($item['id'], 1, 'id, title, cid, clsid, imgpath');
				$item['image_art']['thumb'] = $this->thumb->crop($item['image_art']['imgpath'], 120, 120);
				$item['arts'] = $this->article->getAttrNews($item['id'], 4, array($item['image_art']['id']));
				$c284['childs'][$key] = $item;
			}
			// 装修报价c294
			$c294 = array('id'=>294);
			$c294['cate'] = $this->category->getCurrCat($c294['id']);
			$c294['arts'] = $this->article->get_cate_topics($this->category->get_all_childs_id($c294['id']), 6);
		
		// 装修色彩 c155
		$c155 = array('id'=>155);
		$c155['cate'] =$this->category->getCurrCat($c155['id']);
		$c155['childs'] = $this->category->getChilds($c155['id']);
		
			// 白色系列 c156
			$c156 = array('id'=>156);
			$c156['cate'] = $this->category->getCurrCat($c156['id']);
			$c156['art'] = $this->article->get_cate_topics($c156['id'], 1, 'id, title, cid, clsid, description, imgpath, keyword');
			$c156['art']['thumb'] = $this->thumb->crop($c156['art']['imgpath'], 340, 211);
			$c156['art']['keyword'] = $this->article->add_keywords_link($c156['art']['keyword']);
			
			// 橙色(c262)
			$c262 = array('id'=>262);
			$c262['cate'] = $this->category->getCurrCat($c262['id']);
			$c262['image_art'] = $this->article->get_cate_topics($c262['id'], 1, 'id, title, cid, clsid, imgpath');
			$c262['image_art']['thumb'] = $this->thumb->crop($c262['image_art']['imgpath'], 120, 145);
			$c262['arts'] = $this->article->getAttrNews($c262['id'], 5, array($c262['image_art']['id']));
			
			// 紫色 (c162)
			$c162 = array('id'=>162);
			$c162['cate'] = $this->category->getCurrCat($c162['id']);
			$c162['image_art'] = $this->article->get_cate_topics($c162['id'], 1, 'id, title, cid, clsid, imgpath');
			$c162['image_art']['thumb'] = $this->thumb->crop($c162['image_art']['imgpath'], 120, 145);
			$c162['arts'] = $this->article->getAttrNews($c162['id'], 5, array($c162['image_art']['id']));
			
			// 绿色系列 (c160)
			$c160 = array('id'=>160);
			$c160['cate'] = $this->category->getCurrCat($c160['id']);
			$c160['arts'] = $this->article->getAttrNews($c160['id'], 6);
			
			// 红色系列 => 新古典(c159)
			$c159 = array('id'=>159);
			$c159['cate'] = $this->category->getCurrCat($c159['id']);
			$c159['arts'] = $this->article->getAttrNews($c159['id'], 6);
			
		// 报价
		$this->load->model('budget/budget_model');
		$this->load->model('budget/budget_config_model');
		$budgets = $this->budget_model->get_list("select top 5 id, home_type, cate, bao from budget order by addtime desc");
		$budgets['list'] = $this->budget_config_model->cfg_assign_budgets($budgets['list']);
		
		// 装修公司
		$this->load->model('company/company');
		$decos = $this->company->getKouBeiList(array('num'=>5));
					
		$this->tpl->assign('roots', $roots);
		$this->tpl->assign('childs', $childs);
		$this->tpl->assign('c7', $c7);
		
		$this->tpl->assign('c264', $c264);
		
		$this->tpl->assign('c52', $c52);
		$this->tpl->assign('c49', $c49);
		$this->tpl->assign('c51', $c51);
		$this->tpl->assign('c323', $c323);
		
		$this->tpl->assign('deco_childs', $deco_childs);
		$this->tpl->assign('c271', $c271);
		$this->tpl->assign('c19', $c19);
		$this->tpl->assign('c9', $c9);
		$this->tpl->assign('c284', $c284);
		$this->tpl->assign('c294', $c294);
		$this->tpl->assign('c155', $c155);
		$this->tpl->assign('c156', $c156);
		$this->tpl->assign('c262', $c262);
		$this->tpl->assign('c162', $c162);
		$this->tpl->assign('c160', $c160);
		$this->tpl->assign('c159', $c159);
		
		$this->tpl->assign('budgets', $budgets['list']);
		$this->tpl->assign('decos', $decos);
		
		$this->tpl->display($tpl);
		
		} else {
			$this->tpl->display($tpl);
			echo('<!-- from the cache -->');
		}
	}
	
}

?>