<?php

	class Search extends MY_Controller {
		function __construct(){
			parent::__construct();
		}
		
		
		
		function index(){
			
			// exit();
			
			$key = $_REQUEST['key'];
			$type = $_REQUEST['t'];
			$page = !isset( $_REQUEST['page'] ) ? 1 : $_REQUEST['page'];
			if( !preg_match('/^\d+$/', $page) ){ $page = 1; }
			if( $page <= 0 ){ $page = 1; }
			
			//phpinfo();
			//exit();
			//echo( mb_detect_encoding($key, array('GB2312', 'UTF-8')) );
			//exit();
			//if( mb_detect_encoding($key, array('GB2312', 'UTF-8')) == 'EUC-CN' ){
			//	$key = mb_convert_encoding($key, 'UTF-8', 'GB2312');
			//}
			
			//$key = str_replace('/\'/g', '', $key);
			
			//
			// 分页
			$this->load->library('Pagination', 'pagination');	// 加载分页类
			$this->pagination->dynamic = true;
			$this->pagination->baseUrl = 'http://www.shzh.net/search/app.asp?key=' . $key . '&t=' . $type;
			$this->pagination->currentPage = $page;
			
			$type_cn = '';		// 搜索类型中文字
			switch($type){
				case 'art':
					
					$pagesize = 20;
					
					$type_cn = '文章';
					
					$this->load->model('article/Article', 'article');
					
					
					$sql = "select * from ( select id, title, description, sContent as content, cid, addtime, clsid, num=row_number() over( order by addtime desc ) from art_art where Contains((title, keyword), '\"". $key ."\"') ) as temp where num between ". (($page - 1) * $pagesize + 1) ." and " . ( $page * $pagesize );
					
					
					//$sql = "select top ". $pagesize ." id, title, description, sContent as content, cid, addtime, clsid from art_art where Contains((title,keyword), '\"". $key ."\"') and id not in (".
					//"select top ". $pagesize * ( $page-1 ) ." id from art_art where Contains((title, keyword), '\"". $key ."\"') order by id desc) order by id desc";
					//echo( $sql );
					
					$list = $this->article->getCustomNews( $sql );
					$count = $this->article->getCustomNews("select count(*) as icount from art_art where Contains((title, keyword), '\"". $key ."\"')", false);
					if( $count ){
						$count = $count[0]['icount'];
					} else {
						$count = 0;
					}
					
					$this->tpl->assign('count', $count);
					$this->tpl->assign('list', $list);
					
					$latests = $this->article->getLatestNews();
					$this->tpl->assign('latests', $latests);
					$hots = $this->article->getShowCountNews();
					$this->tpl->assign('hots', $hots);
					
					break;
				case 'cmp':
					$type_cn = '公司';
					
					$pagesize = 20;
					
					$this->load->model('company/company', 'company');
					$res = $this->company->search(array('size'=>$pagesize, 'page'=>$page, 'key'=>$key));
					$count = $res['count'];
					$list = $res['list'];
					$this->tpl->assign('count', $count);
					$this->tpl->assign('list', $list);
					break;
				case 'img':
				
					$type_cn = '图片';
					
					$pagesize = 20;
					
					$this->load->model('photo/Photo', 'photo');
					$settings = array(
						'size'=>$pagesize,
						'page'=>$page,
						'cats'=>$key,
						'fields'=>array('id', 'class', 'imagePath as path', 'cats', 'albumid')
					);
					$res = $this->photo->getImages( $settings );
					
					$list = $res['list'];
					$count = $res['count'];
					$this->load->library('thumb');
					foreach($list as $k=>$val){
						$list[$k]['thumb'] = $this->thumb->crop($val['path'], 195, 150);
					}
					$this->tpl->assign('count', $count);
					$this->tpl->assign('list', $list);
					break;
				default:
					$type_cn = '文章';
					break;
			}
			
			// 分页配置
			$pageCount = $this->pagination->getPageCount($pagesize, $count);
			$this->pagination->pageCount = $pageCount;
			$pagination = $this->pagination->toString(true);
			$this->tpl->assign('pagination', $pagination);
			
			$this->tpl->assign('type', $type);
			$this->tpl->assign('key', $key);
			$this->tpl->assign('type_cn',$type_cn);
			$this->tpl->display('search.html');
			
		}
	}



?>