<?php
	
	// 默认风格的模版处理控制器
	class default_skin_class extends MY_Controller {
		
		var $info_data = NULL;
		
		function __construct(){
			parent::__construct();
			
			
			$this->load->library('encode');	// 编码类
			$this->load->library('thumb');	// 缩略图类
			
			$this->load->model('sjs/info', 'infomation');
			$this->load->model('sjs/sjs_article', 'sjs_article');
			$this->load->model('sjs/sjs_case', 'sjs_case');
			$this->load->model('sjs/lyb', 'lyb');
			
		}
		
		function init($info_data){
			$this->info_data = $info_data;
		}
		
		// 博客首页
		function index(){
			
			
			$this->_assign_info();		// 基本信息
			$this->_assign_navi();			// 导航
			
			/* 文章列表 */
			
			$art_sql = "select top 5 id, username, title, cat_id, detail, addtime, showcount from sjs_article where username = '". 
				$this->info_data['username'] ."' order by addtime desc";
			$art_list = $this->sjs_article->get_article_list( $art_sql );
			if( $art_list != false ){
				foreach($art_list as $key=>$val){
					$art_list[$key]['detail'] = $this->encode->get_text_description($val['detail'], 180);
					$art_list[$key]['detail'] = nl2br($art_list[$key]['detail']);
					$art_list[$key]['detail'] = '<p>'. str_replace('<br />', '</p><p>', $art_list[$key]['detail']) .'</p>';
				}
			}
			$this->tpl->assign('art_list', $art_list);
			$this->_assign_case(2);
			
			/* 留言板 */
			$this->_assign_lyb(4);
			
			/* 访客 */
			$this->_assign_visit(12);
			
			// 首页幻灯片
			$gallery = $this->sjs_case->get_images($this->info_data['username'], 10);
			foreach($gallery as $key=>$val){
				$gallery[$key]['thumb'] = $this->thumb->crop($val['path'], 105, 75);
			}
			$this->tpl->assign('gallery', $gallery);
			$this->tpl->display('member_design/skin/default/index.html');
			
		}
		
		// 博客新闻列表页面
		function art_list($page, $cat_id=0){
			
			//echo('page=' . $page . ';cat_id=' . $cat_id);
			
			$this->_common_assign();
			
			// 文章列表
			$size = 5;
			if( $cat_id != 0 ){
				$cat_sql = " and cat_id = '" . $cat_id . "'";
			} else {
				$cat_sql = "";
			}
			
			$count = $this->sjs_article->get_article_count("select count(*) as icount from sjs_article where username = '". $this->info_data['username'] . "'" . $cat_sql ."");
			
			$sql = "select top " . $size . " id, username, title, cat_id, detail, addtime, showcount, repcount from sjs_article where".
			" id not in (select top ". $size*($page-1) ." id from sjs_article where username = '". $this->info_data['username']. "'" . $cat_sql ." order by addtime desc) and username = '". 
			$this->info_data['username'] . "'" . $cat_sql ." order by addtime desc";
			
			$art_list = $this->sjs_article->get_article_list( $sql );
			if( $art_list != false ){
				foreach($art_list as $key=>$val){
					$art_list[$key]['detail'] = $this->encode->get_text_description($val['detail'], 360);
					$art_list[$key]['detail'] = nl2br($art_list[$key]['detail']);
					$art_list[$key]['detail'] = '<p>'. str_replace('<br />', '</p><p>', $art_list[$key]['detail']) .'</p>';
				}
			}
			$this->tpl->assign('art_list', $art_list);
			
			$this->load->library('pagination');
			//$this->pagination->delimiter = '_';
			//$this->pagination->baseUrl = $this->config->item('curr_base_url') . '/member_design/article_manage.html';
			$this->pagination->currentPage = $page;
			$this->pagination->pageCount = $this->pagination->getPageCount($size, $count);
			$this->pagination->url_template_first = '/sjs/' . $this->info_data['username'] . ($cat_id == 0 ? '/art_list.html' : '/art_list_1_'. $cat_id .'.html');
			$this->pagination->url_template = '/sjs/' . $this->info_data['username'] . ($cat_id == 0 ? '/art_list_<{page}>.html' : '/art_list_<{page}>_'. $cat_id .'.html');
			$pagination = $this->pagination->toString(true);
			$this->tpl->assign('pagination', $pagination);
			$this->tpl->display('member_design/skin/default/art_list.html');
		}
		
		// 博客新闻详细显示页面
		function art_view($username, $id){
			$this->_common_assign();
			
			$object = $this->sjs_article->get_art($id);
			if( $object == false ){
				show_404();
			}
			
			//echo( $object['detail'] );
			
			//exit();
			
			$object['detail'] = $this->encode->htmldecode($object['detail']);
			$this->tpl->assign('object', $object);
			
			$prev = $this->sjs_article->get_prev($object['id'], $username);
			$next = $this->sjs_article->get_next($object['id'], $username);
			$this->tpl->assign('prev_next', array($prev, $next));
			// 获取评论信息
			$this->load->model('sjs/sjs_comment', 'sjs_comment');
			$comments = $this->sjs_comment->get_comment_list("select * from sjs_comment where tid = '" . $id . "' and type='article' order by addtime desc", true);
			foreach($comments as $key=>$val){
				if( !empty( $val['user_inf']['face_image'] ) ){
					$comments[$key]['user_inf']['face_image'] = $this->thumb->crop($comments[$key]['user_inf']['face_image'], 45, 45);
				} else {
					$comments[$key]['user_inf']['face_image'] = '/resources/member_design/blog_skin/default/images/lyb_face.png';
				}
			}
			$this->tpl->assign('comments', $comments);
			$this->tpl->assign('title', $object['title'] . '-' . $this->info_data['true_name'] . '-设计师-上海装潢网');
			$this->tpl->display('member_design/skin/default/art_view.html');
		}
		
		function case_list($page=1){
			$this->_common_assign();
			$size = 5;
			$count = $this->sjs_case->get_case_count("select count(*) as icount from sjs_case where username = '". $this->info_data['username'] ."'");
			
			$sql = "select top ". $size ." * from sjs_case where username = '". $this->info_data['username'] 
			."' and id not in (select top ". ($page-1) * $size ." id from sjs_case where username = '". $this->info_data['username'] ."' order by addtime desc) order by addtime desc";
			$case_list = $this->sjs_case->get_case_list($sql);
			if( $case_list != false ){
				foreach($case_list as $key=>$val){
					if( empty($val['fm']) ){
						$case_list[$key]['fm_thumb'] = '/resources/member_design/blog_skin/default/images/c_list_r2_c2.jpg';
					} else {
						$case_list[$key]['fm_thumb'] = $this->thumb->crop($val['fm'], 300, 200);
					}
					
					$childs = $this->sjs_case->get_case_child_image($val['id'], 3, true, $val['fm']);
					foreach($childs as $childs_key=>$childs_val){
						$childs[$childs_key]['path_thumb'] = $this->thumb->crop($childs_val['path'], 180, 120);
					}
					$case_list[$key]['childs'] = $childs;
					
				}
			}
			
			$this->load->library('pagination');
			$this->pagination->currentPage = $page;
			$this->pagination->pageCount = $this->pagination->getPageCount($size, $count);
			$this->pagination->url_template_first = '/sjs/' . $this->info_data['username'] . '/case_list.html';
			$this->pagination->url_template = '/sjs/' . $this->info_data['username'] . '/case_list_<{page}>.html';
			$pagination = $this->pagination->toString(true);
			$this->tpl->assign('pagination', $pagination);
			
			$this->tpl->assign('case_list_2', $case_list);
			$this->tpl->display('member_design/skin/default/case_list.html');
		}
		
		function case_view($username, $id){
			$this->_common_assign();
			
			$case = $this->sjs_case->get_case($id, true);
			
			if( $case == false ){
				show_404();
			}
			
			// 缩略图
			$case['fm_thumb'] = $this->thumb->crop($case['fm'], 300, 300);
			foreach($case['image_list'] as $key=>$val){
				$case['image_list'][$key]['thumb'] = $this->thumb->crop($val['path'], 180, 120);
			}
			$this->tpl->assign('case', $case);
			
			$prev = $this->sjs_case->get_prev($id, $username);
			$next = $this->sjs_case->get_next($id, $username);
			$this->tpl->assign('prev_next', array($prev, $next));
			// 获取评论信息
			$this->load->model('sjs/sjs_comment', 'sjs_comment');
			$comments = $this->sjs_comment->get_comment_list("select * from sjs_comment where tid = '" . $id . "' and type='case' order by addtime desc", true);
			foreach($comments as $key=>$val){
				if( !empty( $val['user_inf']['face_image'] ) ){
					$comments[$key]['user_inf']['face_image'] = $this->thumb->crop($comments[$key]['user_inf']['face_image'], 45, 45);
				} else {
					$comments[$key]['user_inf']['face_image'] = '/resources/member_design/blog_skin/default/images/lyb_face.png';
				}
			}
			$this->tpl->assign('comments', $comments);
			
			
			// SEO
			$this->tpl->assign('title', $case['case_name'] . '-' . $this->info_data['true_name'] . '-设计师-上海装潢网');
			$this->tpl->assign('keywords', $case['case_name'] . ',' . $this->info_data['true_name'] . ',' . $case['User_Shen'] . ',' . $case['category_01_name'] . ',' . $case['category_02_name'] . ',' . $case['category_03_name']);
			$this->tpl->assign('description', $this->info_data['true_name'] . '作品' . $case['case_name'] . $case['User_Shen'].$case['User_City'] . $case['artstyle_name'] . '风格' . $case['category_01_name'] .'/'. $case['category_02_name'] .'/' . $case['category_03_name']);
			
			$this->tpl->display('member_design/skin/default/case_view.html');
		}
		
		function intro($username){
			$this->_common_assign();
			$this->tpl->assign('wyear_enum', $this->infomation->get_wyear_enum());
			$this->tpl->assign('xueli_enum', $this->infomation->get_xueli_enum());
			$this->tpl->assign('type_enum', $this->infomation->get_type_enum());
			$this->tpl->display('member_design/skin/default/intro.html');
		}
		
		function contact($username){
			$this->_common_assign();
			$this->tpl->display('member_design/skin/default/contact.html');
		}
		
		function contact_submit($username){
			$this->_common_assign();
			
			$object = array();
			
			$object['user_shen'] = $this->encode->getFormEncode('User_Shen');
			$object['user_city'] = $this->encode->getFormEncode('User_City');
			$object['user_town'] = $this->encode->getFormEncode('User_Town');
			$object['address'] = $this->encode->getFormEncode('address');
			$object['category_b'] = $this->encode->getFormEncode('category_b');
			$object['category_s'] = $this->encode->getFormEncode('category_s');
			$object['area'] = $this->encode->getFormEncode('area');
			$object['budget'] = $this->encode->getFormEncode('budget');
			$object['detail'] = $this->encode->getFormEncode('detail');
			$object['name'] = $this->encode->getFormEncode('name');
			$object['tel'] = $this->encode->getFormEncode('tel');
			$object['blog_user'] = $username;
			$object['addtime'] = date('Y-m-d H:i:s');
			
			if( empty( $object['area'] ) || empty( $object['budget'] ) || empty($object['name']) || empty($object['tel']) ){
				$this->tpl->assign('object', $object);
				$this->tpl->assign('error', '提交信息不完整,提交失败');
				$this->contact($username);
			} else {
				try{
					$this->load->model('sjs/sjs_yuyue', 'sjs_yuyue');
					$insertId = $this->sjs_yuyue->add($object);
					echo('<script type="text/javascript">alert("预约成功，等待与您取得联系");location.href="/sjs/'. $username .'/contact.html";</script>');
				}catch(Exception $e){
					$this->tpl->assign('object', $object);
					$this->tpl->assign('error', $e->getMessage());
					$this->contact($username);
				}
			}
			
		}
		
		function comment($username, $page=1){
			// 隐藏左侧留言缩略
			$this->_common_assign(array(
				'hide_lyb'=>true
			));
			
			$size = 20;
			
			$sql = "select top ". $size ." * from sjs_lyb where username = '". $username 
			."' and id not in (select top ". ($size*($page-1)) ." id from sjs_lyb where username = '". $username ."' order by addtime desc) order by addtime desc";
			
			$list = $this->lyb->get_ly_list($sql, true);
			
			foreach($list as $key=>$val){
				if( !empty( $val['user_inf']['face_image'] ) ){
					$list[$key]['user_inf']['face_image'] = $this->thumb->crop($list[$key]['user_inf']['face_image'], 45, 45);
				} else {
					$list[$key]['user_inf']['face_image'] = '/resources/member_design/blog_skin/default/images/lyb_face.png';
				}
				
				$list[$key]['detail'] = $this->encode->htmldecode($list[$key]['detail']);
				$list[$key]['detail'] = $this->encode->removeLink($list[$key]['detail']);
				
			}
			$this->tpl->assign('list', $list);
			
			$this->tpl->display('member_design/skin/default/comment.html');
			
		}
		
		function _common_assign($settings=array()){
			$this->_assign_info();		// 基本信息
			$this->_assign_navi();		// 导航
			$this->_assign_case(1);
			$this->_assign_art_category();
			if( !isset( $settings['hide_lyb'] ) ){
				$this->_assign_lyb(5);
			}
			$this->_assign_visit(12);
		}
		
		
		function _assign_navi(){
			// 导航定义
			$blog_navi = array(
				array('url'=>'index.html', 'name'=>'首页'),
				array('url'=>'art_list.html', 'name'=>'日志'),
				array('url'=>'case_list.html', 'name'=>'案例'),
				array('url'=>'intro.html', 'name'=>'档案'),
				array('url'=>'comment_page.html', 'name'=>'留言'),
				array('url'=>'contact.html', 'name'=>'预约')
			);
			$this->tpl->assign('navi', $blog_navi);
		}
		
		// 基本信息推送
		function _assign_info(){
			//头像
			if( empty($this->info_data['face_image']) ){
				$this->info_data['face_image'] = '/resources/member_design/blog_skin/default/images/default_face_image.png';
			} else {
				$this->info_data['face_image'] = $this->thumb->crop($this->info_data['face_image'], 165, 180);
			}
			/* 基本资料 */
			$this->tpl->assign('style_enum', $this->infomation->get_style_enum());
			$this->tpl->assign('field_enum', $this->infomation->get_field_enum());
			$this->tpl->assign('infomation_data', $this->info_data);
		}
		
		// 侧边栏案例
		// 案例数量
		function _assign_case($num = 1){
			/* 案例 */
			$case_list = $this->sjs_case->get_case_list("select top ". $num ." id, username, case_name, category_01, category_02, category_03, user_shen, user_city, caseprice, artstyle, area, fm from sjs_case where username = '". 
				$this->info_data['username'] ."' order by addtime desc");
			if( $case_list != false ){
				foreach($case_list as $key=>$val){
					// 缩略图
					$case_list[$key]['thumb'] = $this->thumb->crop($val['fm'], 128, 80);
				}
			}
			$this->tpl->assign('case_style', $this->sjs_case->style_enum());
			$this->tpl->assign('case_category', $this->sjs_case->case_category_enum());
			$this->tpl->assign('case_list', $case_list);
		}
		
		// 推送博客文章分类
		function _assign_art_category(){
			$list = $this->sjs_article->get_cat_list($this->info_data['username']);
			$this->tpl->assign('art_cat_list', $list);
		}
		
		// 推送留言板
		function _assign_lyb($num = 10){
			$sql = "select top ". $num ." id, detail, send_user, addtime from sjs_lyb where username = '". $this->info_data['username'] ."' order by addtime desc";
			$list = $this->lyb->get_ly_list($sql, true);
			foreach($list as $key=>$val){
				if( !empty( $val['user_inf']['face_image'] ) ){
					$list[$key]['user_inf']['face_image'] = $this->thumb->crop($list[$key]['user_inf']['face_image'], 45, 45);
				} else {
					$list[$key]['user_inf']['face_image'] = '/resources/member_design/blog_skin/default/images/lyb_face.png';
				}
				$list[$key]['detail'] = $this->encode->removeHtmlAndSpace($list[$key]['detail']);
			}
			$this->tpl->assign('lyb_list', $list);
		}
		
		// 最近访客
		function _assign_visit($num = 12){
			$this->load->model('sjs/recvisit', 'recvisit');
			$list = $this->recvisit->get_visit_users($this->info_data['username'], $num, true);
			foreach($list as $key=>$val){
				if( !empty( $val['user_inf']['face_image'] ) ){
					$list[$key]['user_inf']['face_image'] = $this->thumb->crop($list[$key]['user_inf']['face_image'], 45, 45);
				} else {
					$list[$key]['user_inf']['face_image'] = '/resources/member_design/blog_skin/default/images/lyb_face.png';
				}
			}
			$this->tpl->assign('visit_list', $list);
			///var_dump($list);
		}
		
	}

?>