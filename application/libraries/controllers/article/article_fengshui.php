<?php

class article_fengshui extends article_base {
	
	public $channel_id = 55;
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('channel_id', $this->channel_id);
	}
	
	public function home(){
		
		// Smarty 模版数据缓存
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60*60*3;	// 缓存三小时
		$cache_dir = $this->tpl->cache_dir . 'article/article_fengshui';
		$this->tpl->cache_dir = $cache_dir;
		
		$tpl = 'article/article_fengshui/home.html';
		
		if( ! $this->tpl->isCached($tpl) ){
			
		
		$this->load->model('article/category');
		$this->load->model('article/article');
		$this->load->library('thumb');
		
		$roots = $this->category->getChannels();
		$childs = $this->category->getChilds($this->channel_id);
		
		
		// 局部空间风水 c57
		$c57 = array('id'=>57);
		$c57['cate'] = $this->category->getCurrCat($c57['id']);
		$c57['childs'] = $this->category->getChilds($c57['id'], 10);
			
			// 玄关风水 c83
			$c83 = array('id'=>83);
			$c83['cate'] = $this->category->getCurrCat($c83['id']);
			$c83['images'] = $this->article->get_cate_topics($c83['id'], 1, 'id, title, cid, clsid, description, imgpath, short_title');
			$c83['images']['thumb'] = $this->thumb->crop($c83['images']['imgpath'], 80, 80);
			
			// 儿童房风水 c96
			$c96 = array('id'=>96);
			$c96['cate'] = $this->category->getCurrCat($c96['id']);
			$c96['images'] = $this->article->get_cate_topics($c96['id'], 1, 'id, title, cid, clsid, description, imgpath, short_title');
			$c96['images']['thumb'] = $this->thumb->crop($c96['images']['imgpath'], 80, 80);
			
			
			// 客厅风水 c84
			$c84 = array('id'=>84);
			$c84['cate'] = $this->category->getCurrCat($c84['id']);
			$c84['arts'] = $this->article->get_cate_topics($c84['id'], 6, 'id, title, cid, clsid, short_title');
			
			// 餐厅风水 c85
			$c85 = array('id'=>85);
			$c85['cate'] = $this->category->getCurrCat($c85['id']);
			$c85['image'] = $this->article->get_cate_topics($c85['id'], 1, 'id, title, cid, clsid, imgpath, short_title');
			$c85['image']['thumb'] = $this->thumb->crop($c85['image']['imgpath'], 121, 157);
			$c85['arts'] = $this->article->getAttrNews($c85['id'], 6, array($c85['image']['id']));
			
			// 卧室风水 c87
			$c87 = array('id'=>87);
			$c87['cate'] = $this->category->getCurrCat($c87['id']);
			$c87['image'] = $this->article->get_cate_topics($c87['id'], 1, 'id, title, cid, clsid, imgpath, short_title');
			$c87['image']['thumb'] = $this->thumb->crop($c87['image']['imgpath'], 121, 157);
			$c87['arts'] = $this->article->getAttrNews($c87['id'], 6, array($c87['image']['id']));
			
			// 书房风水 c88
			$c88 = array('id'=>88);
			$c88['cate'] = $this->category->getCurrCat($c88['id']);
			$c88['image'] = $this->article->get_cate_topics($c88['id'], 1, 'id, title, cid, clsid, imgpath, short_title');
			$c88['image']['thumb'] = $this->thumb->crop($c88['image']['imgpath'], 244, 175);
			$c88['arts'] = $this->article->getAttrNews($c88['id'], 1, array($c88['image']['id']));
			
			// 楼梯风水 c89
			$c89 = array('id'=>89);
			$c89['cate'] = $this->category->getCurrCat($c89['id']);
			$c89['arts'] = $this->article->get_cate_topics($c89['id'], 2, 'id, title, cid, clsid');
			
			// 卫生间风水 c90
			$c90 = array('id'=>90);
			$c90['cate'] = $this->category->getCurrCat($c90['id']);
			$c90['arts'] = $this->article->get_cate_topics($c90['id'], 2, 'id, title, cid, clsid');
			
			// 阳台风水 c91
			$c91 = array('id'=>91);
			$c91['cate'] = $this->category->getCurrCat($c91['id']);
			$c91['arts'] = $this->article->get_cate_topics($c91['id'], 1, 'id, title, cid, clsid');
			
		// 婚房风水 c79
		$c79 = array('id'=>79);
		$c79['exists'] = array();
		$c79['cate'] = $this->category->getCurrCat($c79['id']);
		$c79['image'] = $this->article->get_cate_topics($c79['id'], 1, 'id, title, cid, clsid, imgpath, short_title, description');
		$c79['image']['thumb'] = $this->thumb->crop($c79['image']['imgpath'], 121, 152);
		array_push($c79['exists'], $c79['image']['id']);
		$c79['arts'] = $this->article->getAttrNews($c79['id'], 3, $c79['exists']);
		
		$this->article->id_to_array($c79['arts'], $c79['exists']);
		
		$c79['hot_keys'] = array('婚房装修', '婚纱照', '床位', '桃花', '幸福');
		$c79['hot_keys'] = $this->article->add_keywords_link($c79['hot_keys'], $c79['id']);
		
		// 婚房图片
		$this->load->model('photo/photo');
		
		$c79['albums'] = $this->photo->get_albums("select id, name, fm_image from photo_album where id in ( select distinct top 3 albumid from photo_album_image where class = '新婚房' and albumid <> 0 order by albumid desc ) order by id desc");
		foreach($c79['albums'] as $key=>$value){
			$c79['albums'][$key]['thumb'] = $this->thumb->crop($value['fm_image'], 124, 91);
		}
		$c79['image2'] = $this->article->getAttrNews($c79['id'], 1, $c79['exists'], 'latest', 'id, title, cid, clsid, imgpath');
		$c79['image2']['thumb'] = $this->thumb->crop($c79['image2']['imgpath'], 244, 175);
		array_push($c79['exists'], $c79['image2']['id']);
		$c79['arts2'] = $this->article->getAttrNews($c79['id'], 6, $c79['exists']);
		$this->article->id_to_array($c79['arts2'], $c79['exists']);
		
		$c79['main_image'] = $this->article->getAttrNews($c79['id'], 1, $c79['exists'], 'latest', 'id, title, cid, clsid, imgpath, keyword');
		$c79['main_image']['thumb'] = $this->thumb->crop($c79['main_image']['imgpath'], 280, 268);
		$c79['main_image']['keyword'] = $this->article->add_keywords_link($c79['main_image']['keyword'], $c79['id']);
		
		
		
		// 办公室风水 c197
		$c197 = array('id'=>197);
		$c197['exists'] = array();
		$c197['cate'] = $this->category->getCurrCat($c197['id']);
		$c197['image'] = $this->article->get_cate_topics($c197['id'], 1, 'id, title, cid, clsid, imgpath, short_title, description');
		$c197['image']['thumb'] = $this->thumb->crop($c197['image']['imgpath'], 121, 152);
		array_push($c197['exists'], $c197['image']['id']);
		$c197['arts'] = $this->article->getAttrNews($c197['id'], 3, $c197['exists']);
		
		$this->article->id_to_array($c197['arts'], $c197['exists']);
		
		$c197['hot_keys'] = array('总经理办公室', '招财', '植物');
		$c197['hot_keys'] = $this->article->add_keywords_link($c197['hot_keys'], $c197['id']);
		
		// 办公室图片
		$this->load->model('photo/photo');
		
		$c197['albums'] = $this->photo->get_albums("select id, name, fm_image from photo_album where id in ( select distinct top 3 albumid from photo_album_image where class = '经理办公室' and albumid <> 0 order by albumid desc ) order by id desc");
		foreach($c197['albums'] as $key=>$value){
			$c197['albums'][$key]['thumb'] = $this->thumb->crop($value['fm_image'], 124, 91);
		}
		$c197['image2'] = $this->article->getAttrNews($c197['id'], 1, $c197['exists'], 'latest', 'id, title, cid, clsid, imgpath');
		$c197['image2']['thumb'] = $this->thumb->crop($c197['image2']['imgpath'], 244, 175);
		array_push($c197['exists'], $c197['image2']['id']);
		$c197['arts2'] = $this->article->getAttrNews($c197['id'], 6, $c197['exists']);
		$this->article->id_to_array($c197['arts2'], $c197['exists']);
		
		$c197['main_image'] = $this->article->getAttrNews($c197['id'], 1, $c197['exists'], 'latest', 'id, title, cid, clsid, imgpath, keyword');
		$c197['main_image']['thumb'] = $this->thumb->crop($c197['main_image']['imgpath'], 280, 268);
		$c197['main_image']['keyword'] = $this->article->add_keywords_link($c197['main_image']['keyword'], $c197['id']);
		
		
		$this->tpl->assign('c57', $c57);
		$this->tpl->assign('c83', $c83);
		$this->tpl->assign('c96', $c96);
		$this->tpl->assign('c84', $c84);
		$this->tpl->assign('c85', $c85);
		$this->tpl->assign('c87', $c87);
		
		$this->tpl->assign('c88', $c88);
		$this->tpl->assign('c89', $c89);
		$this->tpl->assign('c90', $c90);
		$this->tpl->assign('c91', $c91);
		
		$this->tpl->assign('c79', $c79);
		$this->tpl->assign('c197', $c197);
		
		$this->tpl->assign('roots', $roots);
		$this->tpl->assign('childs', $childs);
		
		$this->tpl->display($tpl);
		
		
		} else {
			$this->tpl->display($tpl);
			echo('<!-- from the cache -->');
		}

	}
	
}

?>