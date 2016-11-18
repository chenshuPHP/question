<?php

	include 'ArticleBase.php';

	class Fengshui extends ArticleBaseController {
		
		function __construct(){
			parent::__construct();
		}
		
		/*
			风水常识首页
		*/
		function index($dev=''){
			
			if( $dev == '' ){
				$this->tpl->caching = true;
				$this->tpl->cache_lifetime = 600;
				$cache_dir = $this->tpl->cache_dir . 'article/index/';	// 设置这个模版的缓存目录
				$this->tpl->cache_dir = $cache_dir;
				$tpl = 'article/fengshui/index.tpl';
			} else {
				error_reporting(E_ALL);
				echo('<!-- dev env -->');
				$tpl = 'article/fengshui/index_test.html';
			}
			
			
			
			if(! $this->tpl->isCached($tpl) ){
			
				$id = 55;
				// 需要用到的模块
				if(! isset($this->mdb)) $this->load->library('mdb');
				$this->load->model('article/Article', 'article');
				$this->load->model('article/Category', 'category');
				$this->load->model('photo/PhotoCategory','photoCategory');
				$this->load->model('company/Company', 'company');
				
				// 需要用到的附加支持类库
				$this->load->library('Thumb', 'thumb');				// 生成缩略图
				
				$status = array();
				$this->category->getCatStatus($id, $status);		// 分类ID的结构
				$root = $this->category->getRoot($id);
				$childs = $this->category->getChilds($root['id']);
				$this->tpl->assign('status', $status);
				$this->tpl->assign('root', $root);
				$this->tpl->assign('childs', $childs);
				
				$allChildCats = array();
				$this->category->getAllChilds($id, $allChildCats);  // 获取到频道下所有分类，赋值给$allChildCats变量
				$allChildCatsId = $this->category->getCatsId( $allChildCats );	// 将获取到的分类转换为只有ID的数组
				
				
				// 定义资讯不重复数组
				$not_arr = array();
				
				
				// 获取滑动广告信息
				$slideImageArts = $this->article->getArtList(array('clsid'=>$allChildCatsId,'size'=>5,'page'=>1,'img'=>1));
				foreach($slideImageArts as $key=>$value){
					// 生成缩略图
					$slideImageArts[$key]['thumb_image'] = $this->thumb->crop($value['img'], 388, 208);
				}
				$this->tpl->assign('slides',$slideImageArts);
				$not_arr = array_merge($not_arr, $slideImageArts);
				
				// 获取风水装修公司列表
				$company_list = $this->company->getKouBeiList(array('num'=>8));
				$this->tpl->assign('company_list', $company_list);
				
				// ########################### 局部空间风水( id = 57 ) ###########################
				$partSpaceId = 57;
				
				// 设置标题
				$partSpaceCat = $this->category->getCurrCat($partSpaceId);	// 获取分类标题
				$this->tpl->assign('partSpaceCat', $partSpaceCat);
				
				// 获取当前分类的所有子类ID数组， 包括当前分类ID本身
				$partSpaceChilds = array();
				$this->category->getAllChilds($partSpaceId, $partSpaceChilds);
				$partSpaceChildsId = $this->category->getCatsId($partSpaceChilds);
				
				// 获取最新一篇推荐文章(页面上大图)
				$partSpaceRmdArt = $this->article->getArtList(array('size'=>1, 'page'=>1, 'clsid'=>$partSpaceChildsId, 'img'=>true, 'rmd'=>true, 'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $partSpaceRmdArt);
				
				$partSpaceRmdArt = $partSpaceRmdArt[0];
				$partSpaceRmdArt['thumb_image'] = $this->thumb->crop($partSpaceRmdArt['img'], 234, 247);	//缩略图
				$this->tpl->assign('partSpaceRmdArt', $partSpaceRmdArt);
				
				// 获取资讯列表
				$partSpaceNewsList = $this->article->getArtList(array('size'=>41,'page'=>1,'clsid'=>$partSpaceChildsId,'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				
				$not_arr = array_merge($not_arr, $partSpaceNewsList);
				usort($partSpaceNewsList, array($this->article, 'sortable'));	// 将资讯按照分类进行排序
				$this->tpl->assign('partSpaceNewsList', $partSpaceNewsList);
				// 获取图片资讯
				$partSpaceImageNewsList = $this->article->getArtList(array('size'=>7,'page'=>1,'clsid'=>$partSpaceChildsId,'img'=>true,'fields'=>array('id','title','clsid','imgpath as img'),'not'=>$not_arr));
				// 生成图片资讯的缩略图
				for($i=0; $i < 4; $i++){
					$partSpaceImageNewsList[$i]['thumb_image'] = $this->thumb->crop($partSpaceImageNewsList[$i]['img'], 130, 95);
				}
				for($i=4; $i < 7; $i++){
					$partSpaceImageNewsList[$i]['thumb_image'] = $this->thumb->crop($partSpaceImageNewsList[$i]['img'], 126, 101);
				}
				$this->tpl->assign('partSpaceImageNewsList', $partSpaceImageNewsList);
				
				// ######################################### 婚房风水 ( id = 79 ) ##################################
				// 设置标题
				$marryId = 79;
				
				$not_arr = array();
				
				$marryCat = $this->category->getCurrCat($marryId);	// 获取分类标题
				$this->tpl->assign('marryCat', $marryCat);
				// 获取当前分类的所有子类ID数组， 包括当前分类ID本身
				$marryChilds = array();
				$this->category->getAllChilds($marryId, $marryChilds);
				$marryChildsId = $this->category->getCatsId($marryChilds);
				// 获取最新一篇推荐文章(页面上大图)
				$marryRmdArt = $this->article->getArtList(array('size'=>1, 'page'=>1, 'clsid'=>$marryChildsId, 'rmd'=>true, 'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $marryRmdArt);
				$marryRmdArt = $marryRmdArt[0];
				$marryRmdArt['thumb_image'] = $this->thumb->crop($marryRmdArt['img'], 234, 247);	//缩略图
				$this->tpl->assign('marryRmdArt', $marryRmdArt);
				
				// 获取图片资讯
				$marryImageNewsList = $this->article->getArtList(array('size'=>3,'page'=>1,'clsid'=>$marryChildsId,'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $marryImageNewsList);
				// 生成缩略图 
				$marryImageNewsList[0]['thumb_image'] = $this->thumb->crop($marryImageNewsList[0]['img'], 142, 126);
				$marryImageNewsList[1]['thumb_image'] = $this->thumb->crop($marryImageNewsList[1]['img'], 142, 126);
				$marryImageNewsList[2]['thumb_image'] = $this->thumb->crop($marryImageNewsList[2]['img'], 199, 138);
				$this->tpl->assign('marryImageNewsList', $marryImageNewsList);
				// 获取资讯列表
				$marryNewsList = $this->article->getArtList(array('size'=>12,'page'=>1,'clsid'=>$marryChildsId,'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$this->tpl->assign('marryNewsList', $marryNewsList);
				
				// #################################### 户型风水(id=94) ###########################
				// 设置标题
				$huxingId = 94;
				
				$not_arr = array();
				
				$huxingCat = $this->category->getCurrCat($huxingId);	// 获取分类标题
				$this->tpl->assign('huxingCat', $huxingCat);
				// 获取当前分类的所有子类ID数组， 包括当前分类ID本身
				$huxingChilds = array();
				$this->category->getAllChilds($huxingId, $huxingChilds);
				$huxingChildsId = $this->category->getCatsId($huxingChilds);
				// 获取最新一篇推荐文章(页面上大图)
				$huxingRmdArt = $this->article->getArtList(array('size'=>1, 'page'=>1, 'clsid'=>$huxingChildsId, 'img'=>true, 'rmd'=>true, 'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $huxingRmdArt);
				$huxingRmdArt = $huxingRmdArt[0];
				$huxingRmdArt['thumb_image'] = $this->thumb->crop($huxingRmdArt['img'], 234, 247);	//缩略图
				$this->tpl->assign('huxingRmdArt', $huxingRmdArt);
				
				// 获取图片资讯
				$huxingImageNewsList = $this->article->getArtList(array('size'=>3,'page'=>1, 'img'=>true, 'clsid'=>$huxingChildsId,'not'=>$not_arr));
				$not_arr = array_merge($not_arr, $huxingImageNewsList);
				// 生成缩略图 
				$huxingImageNewsList[0]['thumb_image'] = $this->thumb->crop($huxingImageNewsList[0]['img'], 142, 126);
				$huxingImageNewsList[1]['thumb_image'] = $this->thumb->crop($huxingImageNewsList[1]['img'], 142, 126);
				$huxingImageNewsList[2]['thumb_image'] = $this->thumb->crop($huxingImageNewsList[2]['img'], 199, 138);
				$this->tpl->assign('huxingImageNewsList', $huxingImageNewsList);
				// 获取资讯列表
				$huxingNewsList = $this->article->getArtList(array('size'=>12,'page'=>1,'clsid'=>$huxingChildsId,'fields'=>array('id','title','clsid'),'not'=>$not_arr));
				$this->tpl->assign('huxingNewsList', $huxingNewsList);
				
			}
			
			$this->tpl->display($tpl);
		}
		
		/*
			列表页
			@param $id 列表页分类id
			@param $page 页码
		*/
		function lister($id, $page=1){
			
			$this->tpl->caching = true;
			$this->tpl->cache_lifetime = 600;	// 2 小时
			$cache_dir = $this->tpl->cache_dir . 'article/list/';	// 设置这个模版的缓存目录
			$this->tpl->cache_dir = $cache_dir;
			
			$cache_id = implode(',',array($id, $page));
			$tpl = 'article/fengshui/list.tpl';
			
			//$this->tpl->clearCache($tpl, $cache_id);
			
			if(! $this->tpl->isCached($tpl, $cache_id) ){
			
				if(! isset($this->mdb)) $this->load->library('mdb');
				// 需要用到的模块
				$this->load->model('article/Article', 'article');
				$this->load->model('article/Category', 'category');
				$this->load->model('photo/PhotoCategory','photoCategory');
				$this->load->model('company/Company', 'company');
				$this->load->model('photo/Photo', 'photo');					// 图库图片&相册
				
				// 需要用到的附加支持类库
				$this->load->library('Pagination', 'pagination');	// 加载分页类
				$this->load->library('Thumb', 'thumb');				// 生成缩略图
				
				$status = array();
				
				$this->category->getCatStatus($id, $status);	//分类ID的结构
				
				if( ! $status[0] ){
					$this->load->helper('url');
					redirect('404_override', 'location', 404);
				}
				
				$root = $this->category->getRoot($id);
				$childs = $this->category->getChilds($root['id']);
				$currentCat = $this->category->getCurrCat( $id );
				
				// 得到子类ID数组
				$currChilds = $this->category->getChilds($id);
				$childsId = array($id);
				foreach($currChilds as $item){
					array_push($childsId, $item['id']);
				}
				
				$this->tpl->assign('currentCat', $currentCat);
				$this->tpl->assign('status', $status);
				$this->tpl->assign('root', $root);
				$this->tpl->assign('childs', $childs);
				
				$settings = array(
					'clsid'=>$childsId,
					'size'=>10,
					'page'=>$page
				);
				// 获取列表页面文章数据
				$list = $this->article->getArtList( $settings );
				
				// 获取列表页面文章总数量
				$count = $this->article->getArtListCount( $childsId );
				
				$this->pagination->baseUrl = $currentCat['link'];
				$this->pagination->currentPage = $page;
				$pageCount = $this->pagination->getPageCount($settings['size'], $count);
				//$pageCount = $count % $settings['size'] == 0 ? $count / $settings['size'] : floor( $count / $settings['size'] ) + 1;
				$this->pagination->pageCount = $pageCount;
				$pagination = $this->pagination->toString(true);
				
				foreach($list as $key=>$val){
					
					if(! empty($val['img']) ){
						$val['img'] = $this->thumb->crop($val['img'], 126, 91);
					}
					
					$list[$key] = $val;
				}
				$this->tpl->assign('list', $list);
				$this->tpl->assign('pagination', $pagination);
				
				$latestNews = $this->article->getLatestNews($childsId, 10);
				$this->tpl->assign('latest', $latestNews);
				
				$hotHomePhotoCats = $this->photoCategory->getHotCategory(0, 'J');
				$hotGongPhotoCats = $this->photoCategory->getHotCategory(0, 'G');
				$this->tpl->assign('hotHomePhotoCats', $hotHomePhotoCats);
				$this->tpl->assign('hotGongPhotoCats', $hotGongPhotoCats);
				
				$koubeiCompanys = $this->company->getKouBeiList();
				
				$this->tpl->assign('koubeiCompanys', $koubeiCompanys);
				
				// 生成相册的缩略图
				$albums = $this->photo->latestAlbum(6);
				for($i=0; $i < count($albums); $i++){
					$albums[$i]['thumb_image'] = $this->thumb->crop($albums[$i]['fm'], 120, 100);	// 生成缩略图
				}
				$this->tpl->assign('albums', $albums);
			
			}
			$this->tpl->display($tpl, $cache_id);
			
		}











		function view($id){
			
			$this->tpl->caching = true;
			$this->tpl->cache_lifetime = 600;	// 24 小时
			$cache_dir = $this->tpl->cache_dir . 'article/view/';	// 设置这个模版的缓存目录
			$this->tpl->cache_dir = $cache_dir;
			
			if(! $this->tpl->isCached('article/fengshui/view.tpl', $id) ){
				
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
			}
			
			$this->tpl->display('article/fengshui/view.tpl', $id);
		}
		
	}


?>