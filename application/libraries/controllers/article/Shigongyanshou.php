<?php if( !defined('BASEPATH') ) exit('禁止访问 Controller: Shigongyanshou');

include('ArticleBase.php');
class Shigongyanshou extends ArticleBaseController {
	
	var $channel_name_en = 'yanshou';
	var $channel_id = 1;
	var $channel_name = '施工验收';
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _remap($params = array()){
		$params = explode('-', $params);
		$method = array_shift($params);
		$this->$method($params);
	}
	
	private function index($params = array()){
		error_reporting(E_ALL);
		$tpl = "article/shigongyanshou/home.html";
		
		$this->load->model('article/article', 'article_model');
		$this->load->model('article/category', 'category_model');
		$this->load->library('thumb');
		
		
		$crumbs = array();
		$this->category_model->getCatStatus($this->channel_id, $crumbs);		// 面包屑导航
		$childs = $this->category_model->getChilds($this->channel_id);		// 二级分类
		
		// {id:4, name:'材料进场验收'}
		$module_04_catId = 4;
		$module_04_cat = $this->category->getCurrCat( $module_04_catId );
		$this->tpl->assign('module_04_cat', $module_04_cat);
		$sql = "select top 10 id, title, clsid from art_art where clsid=4 order by addtime desc";
		$module_04_arts = $this->article_model->get_list($sql);
		$this->tpl->assign('module_04_arts', $module_04_arts['list']);
		//var_dump($module_04_arts);
		$module_05_catId = 5;
		$module_05_cat = $this->category->getCurrCat( $module_05_catId );
		$this->tpl->assign('module_05_cat', $module_05_cat);
		$sql = "select top 10 id, title, clsid from art_art where clsid=5 order by addtime desc";
		$module_05_arts = $this->article_model->get_list($sql);
		$this->tpl->assign('module_05_arts', $module_05_arts['list']);
		
		
		// 页面基本信息(频道标签，频道名称)
		$this->tpl->assign('channel_info', array('name'=>$this->channel_name, 'label'=>$this->channel_name_en, 'id'=>$this->channel_id));
		$this->tpl->assign('status', $crumbs);
		$this->tpl->assign('childs', $childs);
		$this->tpl->display($tpl);
	}
	
	private function view($params = array()){
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 600;									// 10m
		$cache_dir = $this->tpl->cache_dir . 'article/shigongyanshou/';		// 设置这个模版的缓存目录
		$this->tpl->cache_dir = $cache_dir;
		
		$cache_id = $params[0];
		$tpl = 'article/shigongyanshou/view.html';
		
		if(! $this->tpl->isCached($tpl, $cache_id) ){
		
		//error_reporting(E_ALL);
		$article_id = $params[0];
		$this->load->model('article/Category', 'category_model');
		$this->load->model('article/Article', 'article_model');
		$object = $this->article_model->getArt($article_id);
		$cate_childs = $this->category_model->getChilds($this->channel_id);
		$status = array();	// 定义面包屑导航数组
		$this->category_model->getCatStatus($object['clsid'], $status);	// 获取面包屑导航
		$prev = $this->article_model->getPrev($object['id']);
		$next = $this->article_model->getNext($object['id']);
		$latest = $this->article_model->getLatestNews($object['clsid'], 10, array($object['id']));	// 相关文章
		
		$this->load->library('thumb');
		
		// 最新装修公司案例
		$this->load->model('company/usercase', 'user_case');
		$cases = $this->user_case->get_distinct_user_cases(array('count'=>6));
		foreach($cases as $key=>$val){
			$cases[$key]['thumb'] = $this->thumb->crop($val['fm_image'], 140, 100);
		}
		
		// 精美装修图片
		$this->load->model('photo/Photo', 'photo_model');
		$this->load->model('photo/PhotoCategory', 'photo_category_model');
		$enum_photo_cat_id = array(139, 141, 42, 46, 137);
		$photo_labels = $this->photo_category_model->getItem($enum_photo_cat_id);
		foreach($photo_labels as $key=>$val){
			$val['images'] = $this->photo_model->getImages(array(
				'size'=>6,
				'classes'=>array($val['name']),
				'fields'=>array('id', 'imagePath as path', 'albumid', 'cats', 'class')
			));
			$val['images'] = $val['images']['list'];
			
			foreach($val['images'] as $key2=>$val2){
				$val['images'][$key2]['thumb'] = $this->thumb->crop($val2['path'], 125, 90);
			}
			$photo_labels[$key] = $val;
		}
		$this->tpl->assign('channel', $this->channel_name_en);			// 所属频道
		$this->tpl->assign('status', $status);							// 面包屑导航
		$this->tpl->assign('childs', $cate_childs);						// 频道一级子分类
		$this->tpl->assign('object', $object);							// 文章对象分配
		$this->tpl->assign('prev', $prev);								// 上下页
		$this->tpl->assign('next', $next);
		$this->tpl->assign('latest', $latest);
		$this->tpl->assign('cases', $cases);							// 会员装修案例
		$this->tpl->assign('photo_labels', $photo_labels);				// 推荐图库
		
		//seo
		$this->tpl->assign('keywords', implode(',', $object['keyword']));
		$this->tpl->assign('description', $object['description']);
		
		$this->tpl->display($tpl, $cache_id);
		} else {
			
			$this->tpl->display($tpl, $cache_id);
			echo('<!-- cached -->');
		}

	}
	
}




?>