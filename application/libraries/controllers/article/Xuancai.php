<?php

	include 'ArticleBase.php';

	class Xuancai extends ArticleBaseController {
		
		function __construct(){
			parent::__construct();
			
			error_reporting(E_ALL);
			//error_reporting(0);
		}
		
		function index(){
			
			
			
			$this->tpl->caching = true;
			$this->tpl->cache_lifetime = 600;
			$cache_dir = $this->tpl->cache_dir . 'article/xuancai/';	// 设置这个模版的缓存目录
			$this->tpl->cache_dir = $cache_dir;
			
			$tpl = 'article/xuancai/index.tpl';
			
			if(! $this->tpl->isCached($tpl) ){
			
				$id = 97;
				// 需要用到的模块
				if(! isset($this->mdb)) $this->load->library('mdb');
				$this->load->model('article/Article', 'article');
				$this->load->model('article/Category', 'category');
				
				$this->load->model('help/Calculator', 'calculator');
				
				// 需要用到的附加支持类库
				$this->load->library('Thumb', 'thumb');				// 生成缩略图
				
				
				$status = array();
				$this->category->getCatStatus($id, $status);// 分类ID的结构
				
				$root = $this->category->getRoot($id);// 根分类
				$childs = $this->category->getChilds($root['id']);//二级分类
				$childs_id = $this->category->getCatsId( $childs );
					
				$this->tpl->assign('status', $status);
				$this->tpl->assign('root', $root);
				$this->tpl->assign('childs', $childs);
				
				// 装修计算器
				$cuts = $this->calculator->getList();
				$this->tpl->assign('cuts', $cuts);
				
				$not_arr = array();
				
				// 左上文章列表
				$latestArts = $this->article->getArtList(array('clsid'=>$childs_id,'size'=>6,'fields'=>array('id','title','clsid'),'page'=>1));
				$not_arr = array_merge($not_arr, $latestArts);
				$this->tpl->assign('latestArts', $latestArts);
				
				// 滚动广告文章
				$slides = $this->article->getArtList(array('clsid'=>$childs_id,'fields'=>array('id','title','clsid','imgPath as img'),'size'=>5,'page'=>1,'img'=>1,'rmd'=>1,'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $slides);
				foreach($slides as $key=>$value){
					$slides[$key]['thumb_image'] = $this->thumb->crop($slides[$key]['img'], 395, 264);
				}
				$this->tpl->assign('slides', $slides);
				
				// ================ 如何选购材料 (id=110) ==============
				$module_110_catId = 110;
				$module_110_cat = $this->category->getCurrCat( $module_110_catId );
				$module_110_list01 = $this->article->getArtList(array('size'=>8,'page'=>1,'clsid'=>array($module_110_catId),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_110_list01);
				$this->tpl->assign('module_110_cat', $module_110_cat);
				$this->tpl->assign('module_110_list01', $module_110_list01);
				
				// ================= 瓷砖地砖 (id=98) =================
				$module_98_catId = 98;
				$module_98_cat = $this->category->getCurrCat( $module_98_catId );
				$module_98_imglist01 = $this->article->getArtList(array('size'=>2,'page'=>1,'clsid'=>array($module_98_catId),'img'=>1,'not'=>$not_arr));
				foreach($module_98_imglist01 as $key=>$val){
					$module_98_imglist01[$key]['thumb_image'] = $this->thumb->crop($module_98_imglist01[$key]['img'], 113, 82);
				}
				$not_arr = array_merge($not_arr, $module_98_imglist01);
				$module_98_list01 = $this->article->getArtList(array('size'=>6,'page'=>1,'clsid'=>array($module_98_catId),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_98_list01);
				
				$this->tpl->assign('module_98_cat', $module_98_cat);
				$this->tpl->assign('module_98_imglist01', $module_98_imglist01);
				$this->tpl->assign('module_98_list01', $module_98_list01);
				
				// ============= 橱柜 (id=99) ================
				$module_99_catId = 99;
				$module_99_cat = $this->category->getCurrCat( $module_99_catId );
				$module_99_imglist01 = $this->article->getArtList(array('size'=>1,'page'=>1,'clsid'=>array($module_99_catId),'img'=>1,'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_99_imglist01);
				$module_99_imglist01[0]['thumb_image'] = $this->thumb->crop($module_99_imglist01[0]['img'], 113, 82);
				$module_99_list01 = $this->article->getArtList(array('size'=>2,'page'=>1,'clsid'=>array($module_99_catId),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_99_list01);
				
				$this->tpl->assign('module_99_cat', $module_99_cat);
				$this->tpl->assign('module_99_imglist01', $module_99_imglist01);
				$this->tpl->assign('module_99_list01', $module_99_list01);
				
				// ============== 五浴及五金(id=100) ==============
				$module_100_catId = 100;
				$module_100_cat = $this->category->getCurrCat( $module_100_catId );
				$module_100_imglist01 = $this->article->getArtList(array('size'=>1,'page'=>1,'clsid'=>array($module_100_catId),'img'=>1,'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_100_imglist01);
				$module_100_imglist01[0]['thumb_image'] = $this->thumb->crop($module_100_imglist01[0]['img'], 113, 82);
				$module_100_list01 = $this->article->getArtList(array('size'=>2,'page'=>1,'clsid'=>array($module_100_catId),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_100_list01);
				
				$this->tpl->assign('module_100_cat', $module_100_cat);
				$this->tpl->assign('module_100_imglist01', $module_100_imglist01);
				$this->tpl->assign('module_100_list01', $module_100_list01);
				
				// ============== 油漆涂料墙纸 (id=101) ===================
				$module_101_catId = 101;
				$module_101_cat = $this->category->getCurrCat( $module_101_catId );
				$module_101_list01 = $this->article->getArtList(array('size'=>3,'page'=>1,'clsid'=>array($module_101_catId),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$this->tpl->assign('module_101_cat', $module_101_cat);
				$this->tpl->assign('module_101_list01', $module_101_list01);
				// ============== 板材石材 (id=102) ===================
				$module_102_catId = 102;
				$module_102_cat = $this->category->getCurrCat( $module_102_catId );
				$module_102_list01 = $this->article->getArtList(array('size'=>4,'page'=>1,'clsid'=>array($module_102_catId),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$this->tpl->assign('module_102_cat', $module_102_cat);
				$this->tpl->assign('module_102_list01', $module_102_list01);
				// ============== 门窗五金 (id=103) ===================
				$module_103_catId = 103;
				$module_103_cat = $this->category->getCurrCat( $module_103_catId );
				$module_103_list01 = $this->article->getArtList(array('size'=>3,'page'=>1,'clsid'=>array($module_103_catId),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$this->tpl->assign('module_103_cat', $module_103_cat);
				$this->tpl->assign('module_103_list01', $module_103_list01);
				// ============== 地板地毯 (id=104) ===================
				$module_104_catId = 104;
				$module_104_cat = $this->category->getCurrCat( $module_104_catId );
				$module_104_imglist01 = $this->article->getArtList(array('size'=>1,'page'=>1,'clsid'=>array($module_104_catId),'img'=>1,'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_104_imglist01);
				$module_104_imglist01[0]['thumb_image'] = $this->thumb->crop($module_104_imglist01[0]['img'], 178, 129);
				$module_104_list01 = $this->article->getArtList(array('size'=>10,'page'=>1,'clsid'=>array($module_104_catId),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_104_list01);
				$this->tpl->assign('module_104_cat', $module_104_cat);
				$this->tpl->assign('module_104_imglist01', $module_104_imglist01);
				$this->tpl->assign('module_104_list01', $module_104_list01);
				// ============== 窗帘布艺 (id=105) ===================
				$module_105_catId = 105;
				$module_105_cat = $this->category->getCurrCat( $module_105_catId );
				$module_105_imglist01 = $this->article->getArtList(array('size'=>1,'page'=>1,'clsid'=>array($module_105_catId),'img'=>1,'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_105_imglist01);
				$module_105_imglist01[0]['thumb_image'] = $this->thumb->crop($module_105_imglist01[0]['img'], 178, 129);
				$module_105_list01 = $this->article->getArtList(array('size'=>10,'page'=>1,'clsid'=>array($module_105_catId),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_105_list01);
				$this->tpl->assign('module_105_cat', $module_105_cat);
				$this->tpl->assign('module_105_imglist01', $module_105_imglist01);
				$this->tpl->assign('module_105_list01', $module_105_list01);
				// ============== 灯具 (id=106) ===================
				$module_106_catId = 106;
				$module_106_cat = $this->category->getCurrCat( $module_106_catId );
				$module_106_imglist01 = $this->article->getArtList(array('size'=>1,'page'=>1,'clsid'=>array($module_106_catId),'img'=>1,'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_106_imglist01);
				$module_106_imglist01[0]['thumb_image'] = $this->thumb->crop($module_106_imglist01[0]['img'], 146, 106);
				$module_106_list01 = $this->article->getArtList(array('size'=>5,'page'=>1,'clsid'=>array($module_106_catId),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_106_list01);
				$this->tpl->assign('module_106_cat', $module_106_cat);
				$this->tpl->assign('module_106_imglist01', $module_106_imglist01);
				$this->tpl->assign('module_106_list01', $module_106_list01);
				// ============== 家具软装 (id=107) ===================
				$module_107_catId = 107;
				$module_107_cat = $this->category->getCurrCat( $module_107_catId );
				$module_107_imglist01 = $this->article->getArtList(array('size'=>2,'page'=>1,'fields'=>array('id','title','clsid','imgPath as img'),'clsid'=>array($module_107_catId),'img'=>1,'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_107_imglist01);
				$module_107_imglist01[0]['thumb_image'] = $this->thumb->crop($module_107_imglist01[0]['img'], 79, 79);
				$module_107_imglist01[1]['thumb_image'] = $this->thumb->crop($module_107_imglist01[1]['img'], 79, 79);
				$module_107_list01 = $this->article->getArtList(array('size'=>6,'page'=>1,'clsid'=>array($module_107_catId),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_107_list01);
				$this->tpl->assign('module_107_cat', $module_107_cat);
				$this->tpl->assign('module_107_imglist01', $module_107_imglist01);
				$this->tpl->assign('module_107_list01', $module_107_list01);
				// ============== 吊顶 (id=108) ===================
				$module_108_catId = 108;
				$module_108_cat = $this->category->getCurrCat( $module_108_catId );
				$module_108_imglist01 = $this->article->getArtList(array('size'=>1,'page'=>1,'clsid'=>array($module_108_catId),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_108_imglist01);
				$module_108_imglist01[0]['thumb_image'] = $this->thumb->crop($module_108_imglist01[0]['img'], 114, 82);
				$module_108_list01 = $this->article->getArtList(array('size'=>3,'page'=>1,'clsid'=>array($module_108_catId),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_108_list01);
				$this->tpl->assign('module_108_cat', $module_108_cat);
				$this->tpl->assign('module_108_imglist01', $module_108_imglist01);
				$this->tpl->assign('module_108_list01', $module_108_list01);
				// ============== 开关 插座 (id=108) ===================
				$module_109_catId = 109;
				$module_109_cat = $this->category->getCurrCat( $module_109_catId );
				$module_109_imglist01 = $this->article->getArtList(array('size'=>1,'page'=>1,'clsid'=>array($module_109_catId),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_109_imglist01);
				$module_109_imglist01[0]['thumb_image'] = $this->thumb->crop($module_109_imglist01[0]['img'], 114, 82);
				$module_109_list01 = $this->article->getArtList(array('size'=>3,'page'=>1,'clsid'=>array($module_109_catId),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_109_list01);
				$this->tpl->assign('module_109_cat', $module_109_cat);
				$this->tpl->assign('module_109_imglist01', $module_109_imglist01);
				$this->tpl->assign('module_109_list01', $module_109_list01);
			
				$this->tpl->display($tpl);
			
			} else {
				$this->tpl->display($tpl);
				echo('<!--cache-->');
			}
			
			
			
			
		}
		
		
		function lister($id, $page=1){
		
			$this->tpl->caching = true;
			$this->tpl->cache_lifetime = 600;
			$cache_dir = $this->tpl->cache_dir . 'article/list/';	// 设置这个模版的缓存目录
			$this->tpl->cache_dir = $cache_dir;
			
			$cache_id = implode(',',array($id, $page));
			$tpl = 'article/xuancai/list.tpl';
			
			// $this->tpl->clearCache($tpl, $cache_id);
			
			if(! $this->tpl->isCached($tpl, $cache_id) ){
		
				if(! isset($this->mdb)) $this->load->library('mdb');
			
				$this->load->model('article/Article', 'article');			//资讯
				$this->load->model('article/Category', 'category');			// 资讯分类
				$this->load->model('photo/PhotoCategory','photoCategory');	// 图库分类
				$this->load->model('company/Company','cmp');
					
				
				// 需要用到的附加支持类库
				$this->load->library('Thumb', 'thumb');				// 缩略图类
				$this->load->library('Pagination', 'pagination');	// 加载分页类
				
				$status = array();
				$this->category->getCatStatus($id, $status);	//分类ID的结构
				$root = $this->category->getRoot($id);
				$childs = $this->category->getChilds($root['id']);	// 子导航
				
				$currentCat = $this->category->getCurrCat( $id );	// 当前分类信息
				
				$this->tpl->assign('currentCat', $currentCat);
				$this->tpl->assign('status', $status);
				$this->tpl->assign('root', $root);
				$this->tpl->assign('childs', $childs);
				
				// 当前分类的子分类包括当前分类
				$currentChilds = array();
				$this->category->getAllChilds($id, $currentChilds);
				$currentChildsId = $this->category->getCatsId($currentChilds);
				
				$settings = array(
					'clsid'=>$currentChildsId,
					'size'=>10,
					'page'=>$page
				);
				// 获取列表页面文章数据
				$list = $this->article->getArtList( $settings );
				// 获取列表页面文章总数量
				$count = $this->article->getArtListCount( $currentChildsId );
				
				
				$this->pagination->baseUrl = $currentCat['link'];
				$this->pagination->currentPage = $page;
				$pageCount = $this->pagination->getPageCount($settings['size'], $count);
				//$pageCount = $count % $settings['size'] == 0 ? $count / $settings['size'] : floor( $count / $settings['size'] ) + 1;
				$this->pagination->pageCount = $pageCount;
				$pagination = $this->pagination->toString(true);
				
				// 生成文章的缩略图
				
				foreach($list as $key=>$val){
					
					if(! empty($val['img']) ){
						$val['img'] = $this->thumb->crop($val['img'], 126, 91);
					}
					//$val['keyword'] = implode($val['keyword'], ' ');
					$list[$key] = $val;
				}
				
				$this->tpl->assign('list', $list);
				$this->tpl->assign('pagination', $pagination);
				
				
				$latestNews = $this->article->getLatestNews($currentChildsId, 10);
				$this->tpl->assign('latest', $latestNews);
				
				
				$hotHomePhotoCats = $this->photoCategory->getHotCategory(0, 'J');
				$hotGongPhotoCats = $this->photoCategory->getHotCategory(0, 'G');
				$this->tpl->assign('hotHomePhotoCats', $hotHomePhotoCats);
				$this->tpl->assign('hotGongPhotoCats', $hotGongPhotoCats);
				
				// 获取风水装修公司列表
				$company_list = $this->cmp->getKouBeiList(array('num'=>10));
				$this->tpl->assign('company_list', $company_list);
		
				$this->tpl->display($tpl, $cache_id);
			} else {
				$this->tpl->display($tpl, $cache_id);
				echo('<!--cache-->');
			}
		
			
		
		
		}
		
		
		
		function view($id){
			
			$this->tpl->caching = true;
			$this->tpl->cache_lifetime = 600;
			$cache_dir = $this->tpl->cache_dir . 'article/view/';	// 设置这个模版的缓存目录
			$this->tpl->cache_dir = $cache_dir;
			
			$tpl = 'article/xuancai/view.tpl';
			
			if(! $this->tpl->isCached($tpl, $id) ){
			
				if(! isset($this->mdb)) $this->load->library('mdb');
				$this->load->model('article/Article', 'article');
				$this->load->model('article/Category', 'category');
				
				$oArt = $this->article->getArt($id);
				
				$status = array();
				$this->category->getCatStatus($oArt['clsid'], $status);	//分类ID的结构
				
				$root = $this->category->getRoot($oArt['clsid']);
				$childs = $this->category->getChilds($root['id']);
				
				$prev = $this->article->getPrev( $oArt['id'] );
				$next = $this->article->getNext( $oArt['id'] );
				
				$latest = $this->article->getLatestNews($oArt['clsid'], 10, array($oArt['id']));
				
				
				$this->tpl->assign('oArt', $oArt);
				$this->tpl->assign('status', $status);
				$this->tpl->assign('keyword', implode(',',$oArt['keyword']));
				$this->tpl->assign('root', $root);
				$this->tpl->assign('childs', $childs);
				$this->tpl->assign('prev', $prev);
				$this->tpl->assign('next', $next);
				$this->tpl->assign('latest', $latest);
				
				$this->load->model('photo/Photo', 'photo');
				$this->load->model('photo/PhotoCategory','photoCategory');
				$hotHomePhotoCats = $this->photoCategory->getHotCategory(0, 'J');
				$hotGongPhotoCats = $this->photoCategory->getHotCategory(0, 'G');
				$this->load->library('Thumb', 'thumb');
				$albums = $this->photo->latestAlbum(4);
				for($i=0; $i < count($albums); $i++){
					$albums[$i]['thumb_image'] = $this->thumb->crop($albums[$i]['fm'], 135, 90);	// 生成缩略图
				}
				
				$this->tpl->assign('albums', $albums);
				$this->tpl->assign('hotHomePhotoCats', $hotHomePhotoCats);
				$this->tpl->assign('hotGongPhotoCats', $hotGongPhotoCats);
			
				$this->tpl->display($tpl, $id);
			} else {
				$this->tpl->display($tpl, $id);
				echo('<!--cache-->');
			}
			
			
			
		}
	
	}

?>