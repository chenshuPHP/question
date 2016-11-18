<?php

	include 'ArticleBase.php';

	class Sheji extends ArticleBaseController {
		
		function __construct(){
			parent::__construct();
		}
	
		
		/*
			装修设计首页
		*/
		function index(){
			
			
			$this->tpl->caching = true;
			$this->tpl->cache_lifetime = 600;	// 10m
			$cache_dir = $this->tpl->cache_dir . 'article/sheji/';	// 设置这个模版的缓存目录
			$this->tpl->cache_dir = $cache_dir;
			
			$tpl = 'article/sheji/index.tpl';
			
			if(! $this->tpl->isCached($tpl) ){
			
				$id = 6;
				// 需要用到的模块
				if(! isset($this->mdb)) $this->load->library('mdb');
				$this->load->model('article/Article', 'article');
				$this->load->model('article/Category', 'category');
				$this->load->model('company/Company', 'company');
				// 需要用到的附加支持类库
				$this->load->library('Thumb', 'thumb');				// 生成缩略图
				
				$status = array();
				$this->category->getCatStatus($id, $status);// 分类ID的结构
				
				$root = $this->category->getRoot($id);// 根分类
				$childs = $this->category->getChilds($root['id']);//二级分类
					
				$threeChilds = array();
				$this->category->getAllChilds($id, $threeChilds);//所有子分类
				$threeChildsId = $this->category->getCatsId($threeChilds);// 所有子分类ID
				
				$this->tpl->assign('status', $status);
				$this->tpl->assign('root', $root);
				$this->tpl->assign('childs', $childs);
				//$this->tpl->assign('threeChilds', $threeChilds);
				
				$not_arr = array();
				
				$leftArts = $this->article->getArtList(array('clsid'=>$threeChildsId,'size'=>14,'fields'=>array('id','title','clsid'),'page'=>1,'img'=>1,'rmd'=>true));
				$this->tpl->assign('leftArts', $leftArts);
				$not_arr = array_merge($not_arr, $leftArts);
				
				// 获取滑动广告信息
				$slideImageArts = $this->article->getArtList(array('clsid'=>$threeChildsId,'size'=>5,'page'=>1,'img'=>1,'rmd'=>true, 'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $slideImageArts);
				foreach($slideImageArts as $key=>$value){
					// 生成缩略图
					$slideImageArts[$key]['thumb_image'] = $this->thumb->crop($value['img'], 402, 250);
				}
				$this->tpl->assign('slides',$slideImageArts);
				// 获取风水装修公司列表
				$company_list = $this->company->getKouBeiList(array('num'=>14));
				$this->tpl->assign('company_list', $company_list);
				
				// ########################### 装修风格 ( id = 7 ) ###########################
				$module_07_catId = 7;
				$module_07_cat = $this->category->getCurrCat( $module_07_catId );
				$this->tpl->assign('module_07_cat', $module_07_cat);
				$module_07_childs = $this->category->getChilds( $module_07_catId );
				$this->tpl->assign('module_07_childs', $module_07_childs);
				$module_07_list01 = $this->article->getArtList(array('size'=>5,'page'=>1,'clsid'=>array($module_07_childs[0]['id']),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_07_list01);
				$this->tpl->assign('module_07_list01', $module_07_list01);
				
				$module_07_imglist01 = $this->article->getArtList(array('size'=>2,'page'=>1,'clsid'=>array($module_07_childs[0]['id']),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_07_imglist01);
				$module_07_imglist01[0]['thumb_image'] = $this->thumb->crop($module_07_imglist01[0]['img'], 112, 112);
				$module_07_imglist01[1]['thumb_image'] = $this->thumb->crop($module_07_imglist01[1]['img'], 112, 98);
				$this->tpl->assign('module_07_imglist01', $module_07_imglist01);
				
				$module_07_list02 = $this->article->getArtList(array('size'=>5,'page'=>1,'clsid'=>array($module_07_childs[3]['id']),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_07_list02);
				$this->tpl->assign('module_07_list02', $module_07_list02);
				$module_07_imglist02 = $this->article->getArtList(array('size'=>2,'page'=>1,'clsid'=>array($module_07_childs[3]['id']),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_07_imglist02);
				$module_07_imglist02[0]['thumb_image'] = $this->thumb->crop($module_07_imglist02[0]['img'], 112, 98);
				$module_07_imglist02[1]['thumb_image'] = $this->thumb->crop($module_07_imglist02[1]['img'], 112, 112);
				$this->tpl->assign('module_07_imglist02', $module_07_imglist02);
				
				$module_07_list03 = $this->article->getArtList(array('size'=>5,'page'=>1,'clsid'=>array($module_07_childs[4]['id']),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_07_list03);
				$this->tpl->assign('module_07_list03', $module_07_list03);
				
				$module_07_list04 = $this->article->getArtList(array('size'=>5,'page'=>1,'clsid'=>array($module_07_childs[5]['id']),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_07_list04);
				$this->tpl->assign('module_07_list04', $module_07_list04);
				
				// ############################ 装修用途( id = 9 ) #################################
				$module_09_catId = 9;
				$module_09_cat = $this->category->getCurrCat( $module_09_catId );
				$this->tpl->assign('module_09_cat', $module_09_cat);
				$module_09_childs = $this->category->getChilds( $module_09_catId );
				$this->tpl->assign('module_09_childs', $module_09_childs);
				
				$module_09_list01 = $this->article->getArtList(array('size'=>4,'page'=>1,'clsid'=>array($module_09_childs[0]['id']),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_09_list01);
				
				$module_09_imglist01 = $this->article->getArtList(array('size'=>1,'page'=>1,'clsid'=>array($module_09_childs[0]['id']),'not'=>$not_arr));
				$module_09_imglist01[0]['thumb_image'] = $this->thumb->crop($module_09_imglist01[0]['img'], 191, 129);
				$not_arr = array_merge($not_arr, $module_09_imglist01);
				
				$module_09_list02 = $this->article->getArtList(array('size'=>4,'page'=>1,'clsid'=>array($module_09_childs[1]['id']),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_09_list02);
				
				$module_09_imglist02 = $this->article->getArtList(array('size'=>1,'page'=>1,'clsid'=>array($module_09_childs[1]['id']),'not'=>$not_arr));
				$module_09_imglist02[0]['thumb_image'] = $this->thumb->crop($module_09_imglist02[0]['img'], 191, 129);
				$not_arr = array_merge($not_arr, $module_09_imglist02);
				
				$module_09_list03 = $this->article->getArtList(array('size'=>4,'page'=>1,'clsid'=>array($module_09_childs[2]['id']),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_09_list03);
				
				$module_09_imglist03 = $this->article->getArtList(array('size'=>1,'page'=>1,'clsid'=>array($module_09_childs[2]['id']),'not'=>$not_arr));
				$module_09_imglist03[0]['thumb_image'] = $this->thumb->crop($module_09_imglist03[0]['img'], 191, 129);
				$not_arr = array_merge($not_arr, $module_09_imglist01);
				
				$module_09_list04 = $this->article->getArtList(array('size'=>4,'page'=>1,'clsid'=>array($module_09_childs[3]['id']),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_09_list04);
				
				$module_09_imglist04 = $this->article->getArtList(array('size'=>1,'page'=>1,'clsid'=>array($module_09_childs[3]['id']),'not'=>$not_arr));
				$module_09_imglist04[0]['thumb_image'] = $this->thumb->crop($module_09_imglist04[0]['img'], 191, 129);
				$not_arr = array_merge($not_arr, $module_09_imglist01);
				
				$this->tpl->assign('module_09_list01', $module_09_list01);
				$this->tpl->assign('module_09_list02', $module_09_list02);
				$this->tpl->assign('module_09_list03', $module_09_list03);
				$this->tpl->assign('module_09_list04', $module_09_list04);
				$this->tpl->assign('module_09_imglist01', $module_09_imglist01);
				$this->tpl->assign('module_09_imglist02', $module_09_imglist02);
				$this->tpl->assign('module_09_imglist03', $module_09_imglist03);
				$this->tpl->assign('module_09_imglist04', $module_09_imglist04);
				
				// ############################ 装修户型( id = 19 ) #################################
				$module_19_catId = 19;
				$module_19_cat = $this->category->getCurrCat( $module_19_catId );
				$this->tpl->assign('module_19_cat', $module_19_cat);
				$module_19_childs = $this->category->getChilds( $module_19_catId );
				$this->tpl->assign('module_19_childs', $module_19_childs);
				
				// 右侧大图
				$module_19_bigimg = $this->article->getArtList(array('size'=>1,'page'=>1,'clsid'=>$this->category->getCatsId($module_19_childs),'img'=>true,'rmd'=>true,'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_19_bigimg);
				$module_19_bigimg[0]['thumb_image'] = $this->thumb->crop($module_19_bigimg[0]['img'], 199, 242);
				$this->tpl->assign('module_19_bigimg', $module_19_bigimg);
				
				$module_19_imglist01 = $this->article->getArtList(array('size'=>1,'page'=>1,'img'=>true,'clsid'=>array($module_19_childs[0]['id']),'not'=>$not_arr));
				$module_19_imglist01[0]['thumb_image'] = $this->thumb->crop($module_19_imglist01[0]['img'], 135, 104);
				$not_arr = array_merge($not_arr, $module_19_imglist01);
				
				$module_19_list01 = $this->article->getArtList(array('size'=>5,'page'=>1,'clsid'=>array($module_19_childs[0]['id']),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_19_list01);
				
				$this->tpl->assign('module_19_list01', $module_19_list01);
				$this->tpl->assign('module_19_imglist01', $module_19_imglist01);
				
				$module_19_imglist02 = $this->article->getArtList(array('size'=>1,'page'=>1,'img'=>true,'clsid'=>array($module_19_childs[1]['id']),'not'=>$not_arr));
				$module_19_imglist02[0]['thumb_image'] = $this->thumb->crop($module_19_imglist02[0]['img'], 135, 104);
				$not_arr = array_merge($not_arr, $module_19_imglist02);
				
				$module_19_list02 = $this->article->getArtList(array('size'=>5,'page'=>1,'clsid'=>array($module_19_childs[1]['id']),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_19_list02);
				
				$this->tpl->assign('module_19_list02', $module_19_list02);
				$this->tpl->assign('module_19_imglist02', $module_19_imglist02);
				
				
				$module_19_imglist03 = $this->article->getArtList(array('size'=>1,'page'=>1,'img'=>true,'clsid'=>array($module_19_childs[2]['id']),'not'=>$not_arr));
				$module_19_imglist03[0]['thumb_image'] = $this->thumb->crop($module_19_imglist03[0]['img'], 135, 104);
				$not_arr = array_merge($not_arr, $module_19_imglist03);
				
				$module_19_list03 = $this->article->getArtList(array('size'=>5,'page'=>1,'clsid'=>array($module_19_childs[2]['id']),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_19_list03);
				
				$this->tpl->assign('module_19_list03', $module_19_list03);
				$this->tpl->assign('module_19_imglist03', $module_19_imglist03);
				
				
				$module_19_imglist04 = $this->article->getArtList(array('size'=>1,'page'=>1,'img'=>true,'clsid'=>array($module_19_childs[3]['id']),'not'=>$not_arr));
				$module_19_imglist04[0]['thumb_image'] = $this->thumb->crop($module_19_imglist04[0]['img'], 135, 104);
				$not_arr = array_merge($not_arr, $module_19_imglist04);
				
				$module_19_list04 = $this->article->getArtList(array('size'=>5,'page'=>1,'clsid'=>array($module_19_childs[3]['id']),'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_19_list04);
				
				$this->tpl->assign('module_19_list04', $module_19_list04);
				$this->tpl->assign('module_19_imglist04', $module_19_imglist04);
				
				// ############################ 局部空间 ( id = 20 ) #################################
				$module_20_catId = 20;
				$module_20_cat = $this->category->getCurrCat( $module_20_catId );
				$module_20_childs = $this->category->getChilds( $module_20_catId );
				$module_20_childs_id = $this->category->getCatsId( $module_20_childs );
				$this->tpl->assign('module_20_cat', $module_20_cat);
				$this->tpl->assign('module_20_childs', $module_20_childs);
				
				$module_20_imglist01 = $this->article->getArtList(array('size'=>5,'page'=>1,'img'=>true,'clsid'=>$module_20_childs_id,'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_20_imglist01);
				for($i=0; $i < 4; $i++){
					$module_20_imglist01[$i]['thumb_image'] = $this->thumb->crop($module_20_imglist01[$i]['img'], 100, 79);
				}
				$module_20_imglist01[4]['thumb_image'] = $this->thumb->crop($module_20_imglist01[4]['img'], 198, 188);
				
				$module_20_list01 = $this->article->getArtList(array('size'=>6,'page'=>1,'clsid'=>$module_20_childs_id,'fields'=>array('id','title','clsid','keyword'),'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_20_list01);
				
				$this->tpl->assign('module_20_list01', $module_20_list01);
				$this->tpl->assign('module_20_imglist01', $module_20_imglist01);
				
				// ############################ 装修色彩 ( id = 155 ) #################################
				$module_155_catId = 155;
				$module_155_cat = $this->category->getCurrCat( $module_155_catId );
				$module_155_childs = $this->category->getChilds( $module_155_catId );
				$module_155_childs_id = $this->category->getCatsId( $module_155_childs );
				$this->tpl->assign('module_155_cat', $module_155_cat);
				$this->tpl->assign('module_155_childs', $module_155_childs);
				
				// 右侧大图
				$module_155_bigimg = $this->article->getArtList(array('size'=>1,'page'=>1,'clsid'=>$module_155_childs_id,'img'=>true,'rmd'=>true,'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_155_bigimg);
				$module_155_bigimg[0]['thumb_image'] = $this->thumb->crop($module_155_bigimg[0]['img'], 199, 188);
				$this->tpl->assign('module_155_bigimg', $module_155_bigimg);
				
				$module_155_imglist01 = $this->article->getArtList(array('size'=>4,'page'=>1,'img'=>true,'clsid'=>$module_155_childs_id,'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $module_155_imglist01);
				for($i=0; $i < count($module_155_imglist01); $i++){
					$module_155_imglist01[$i]['thumb_image'] = $this->thumb->crop($module_155_imglist01[$i]['img'], 119, 76);
				}
				$this->tpl->assign('module_155_imglist01', $module_155_imglist01);
				
				$module_155_list01 = $this->article->getArtList(array('size'=>14, 'page'=>1,'clsid'=>$module_155_childs_id,'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$this->tpl->assign('module_155_list01', $module_155_list01);
			
				$this->tpl->display($tpl);
			
			} else {
				
				$this->tpl->display($tpl);
				echo('<!--cache-->');
			}
			
			
		
		}
		
		
		/*
			列表页
			@param $id 列表页分类id
			@param $page 页码
		*/
		function lister($id, $page=1){
			
			$this->tpl->caching = true;
			$this->tpl->cache_lifetime = 10*60;	// 10 分钟
			$cache_dir = $this->tpl->cache_dir . 'article/list/';	// 设置这个模版的缓存目录
			$this->tpl->cache_dir = $cache_dir;
			
			$cache_id = implode(',',array($id, $page));
			$tpl = 'article/sheji/list.tpl';
			
			//$this->tpl->clearCache($tpl, $cache_id);
			
			if(! $this->tpl->isCached($tpl, $cache_id) ){
			
				if(! isset($this->mdb)) $this->load->library('mdb');
				
				// 需要用到的模块
				$this->load->model('article/Article', 'article');			//资讯
				$this->load->model('article/Category', 'category');			// 资讯分类
				$this->load->model('photo/PhotoCategory','photoCategory');	// 图库分类
				$this->load->model('photo/Photo', 'photo');					// 图库图片&相册
				
				// 需要用到的附加支持类库
				$this->load->library('Thumb', 'thumb');				// 缩略图类
				$this->load->library('Pagination', 'pagination');	// 加载分页类
				
				
				
				$status = array();
				$this->category->getCatStatus($id, $status);	//分类ID的结构
				$root = $this->category->getRoot($id);
				$childs = $this->category->getChilds($root['id']);	// 子导航
				
				$currentCat = $this->category->getCurrCat( $id );	// 当前分类信息
				
				$threeChilds = $this->category->getChilds( $status[1]['id'] );	// 三级分类
				
				$this->tpl->assign('currChilds', $threeChilds);
				
				// 当前分类的子分类包括当前分类
				$currentChilds = array();
				$this->category->getAllChilds($id, $currentChilds);
				$currentChildsId = $this->category->getCatsId($currentChilds);
				
				$this->tpl->assign('currentCat', $currentCat);
				$this->tpl->assign('status', $status);
				$this->tpl->assign('root', $root);
				$this->tpl->assign('childs', $childs);
				
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
				
				
				
				// 生成相册的缩略图
				$albums = $this->photo->latestAlbum(6);
				for($i=0; $i < count($albums); $i++){
					$albums[$i]['thumb_image'] = $this->thumb->crop($albums[$i]['fm'], 120, 100);	// 生成缩略图
				}
				$this->tpl->assign('albums', $albums);
				$this->tpl->display($tpl, $cache_id);
			} else {
			
				$this->tpl->display($tpl, $cache_id);
				echo('<!--cache-->');
			
			}
			
			
			
		}
		
		
		
		
		
		
		
		
		function view($id){
			
			$this->tpl->caching = true;
			$this->tpl->cache_lifetime = 600;	// 10m
			$cache_dir = $this->tpl->cache_dir . 'article/view/';	// 设置这个模版的缓存目录
			$this->tpl->cache_dir = $cache_dir;
			
			$tpl = 'article/sheji/view.tpl';
			
			if(! $this->tpl->isCached($tpl, $id) ){
				
				if(! isset($this->mdb)) $this->load->library('mdb');
				
				$this->load->model('article/Article', 'article');
				$this->load->model('article/Category', 'category');
				
				$oArt = $this->article->getArt($id);
				
				$status = array();
				$this->category->getCatStatus($oArt['clsid'], $status);	//分类ID的结构
				
				$root = $this->category->getRoot($oArt['clsid']);
				$childs = $this->category->getChilds($root['id']);
				
				// 相关分类
				$aboutCats = $this->category->getSiblings($oArt['clsid']);
				
				
				$prev = $this->article->getPrev( $oArt['id'] );
				$next = $this->article->getNext( $oArt['id'] );
				
				$latest = $this->article->getLatestNews($oArt['clsid'], 10, array($oArt['id']));
				
				$this->load->model('photo/Photo', 'photo');
				$this->load->model('photo/PhotoCategory','photoCategory');
				$hotHomePhotoCats = $this->photoCategory->getHotCategory(0, 'J');
				$hotGongPhotoCats = $this->photoCategory->getHotCategory(0, 'G');
				//print_r( $hotPhotoCats );
				$this->load->library('Thumb', 'thumb');
				$albums = $this->photo->latestAlbum(4);
				for($i=0; $i < count($albums); $i++){
					$albums[$i]['thumb_image'] = $this->thumb->crop($albums[$i]['fm'], 135, 90);	// 生成缩略图
				}
				
				$this->tpl->assign('aboutCats', $aboutCats);
				$this->tpl->assign('status', $status);
				$this->tpl->assign('keyword', implode(',',$oArt['keyword']));
				$this->tpl->assign('root', $root);
				$this->tpl->assign('childs', $childs);
				$this->tpl->assign('prev', $prev);
				$this->tpl->assign('next', $next);
				$this->tpl->assign('oArt', $oArt);
				$this->tpl->assign('latest', $latest);
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