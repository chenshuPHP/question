<?php

	class designer_channel extends MY_Controller {
		
		function __construct(){
			parent::__construct();
		}
		
		// 名师设计首页
		function index(){
			$this->tpl->caching = true;
			$this->tpl->cache_lifetime = 1800;	// 30分钟
			$cache_dir = $this->tpl->cache_dir . 'sjs/channel/';	// 设置这个模版的缓存目录
			$this->tpl->cache_dir = $cache_dir;
			$tpl = 'member_design/channel/index.html';
			if( !$this->tpl->isCached($tpl) ){
				$this->load->library('thumb');
				$this->load->model('sjs/sjs_case', 'sjs_case');
				$sql = "select top 9 sjs_case.id, sjs_case.username, case_name, category_01, category_02, category_03, fm, area, sjs_info.true_name from sjs_case,sjs_info where fm <> '' and sjs_info.username = sjs_case.username order by sjs_case.addtime desc";
				$case_list = $this->sjs_case->get_case_list( $sql );
				foreach($case_list as $key=>$val){
					$case_list[$key]['thumb'] = $this->thumb->crop($val['fm'], 174, 204);
				}
				$this->tpl->assign('case_list', $case_list);
				
				$this->load->model('sjs/info', 'sjs_info');
				$user_list = $this->sjs_info->get_list("select top 7 id, username, face_image, true_name from sjs_info where face_image <> '' order by update_time desc");
				
				foreach($user_list as $key=>$val){
					$user_list[$key]['thumb'] = $this->thumb->crop($val['face_image'], 135, 135);
				}
				$this->tpl->assign('user_list', $user_list);
				
				$this->load->model('sjs/sjs_article', 'sjs_article');
				
				$sql = "select top 12 sjs_article.id, sjs_article.username, title, cat_id, sjs_article_cat.cat_name from sjs_article, sjs_article_cat where sjs_article_cat.id = sjs_article.cat_id order by addtime desc";
				
				$art_list = $this->sjs_article->get_article_list( $sql );
				
				$this->tpl->assign('art_list', $art_list);
				
				// 获取案例数量最多的设计师
				$this->load->library('mdb');
				$result = $this->mdb->query('select top 1 count(*) as icount, username from sjs_case where username in (select username from sjs_info) group by username order by icount desc');
				$result = $result[0];
				$max_case_username = $result['username'];
				$max_case_user = $this->sjs_info->getInfomation($max_case_username);
				$this->tpl->assign('max_case_user', $max_case_user);
				$this->tpl->display( $tpl );
			} else {
				$this->tpl->display($tpl);
				echo('<!-- cached -->');
			}
		}
		
		
		// 名师设计案例列表
		function case_list($params){
			
			if( empty( $params ) ){ show_404(); }
			
			$params = explode('-', trim($params, '-'));	// 得到参数值
			
			$page = $params[6];
			
			$category_01 = $params[0];
			$category_02 = $params[1];
			$category_03 = $params[2];
			$style = $params[3];
			
			array_pop($params);
			
			
			$this->load->library('thumb');
			
			$this->load->model('sjs/sjs_case', 'sjs_case');
			
			$settings = array(
				'size'=>20,
				'page'=>$page
			);
			
			
			// 构造SQL
			$w_sql = ' 1=1';
			
			if( $category_01 != 0  ){
				$w_sql .= " and sjs_case.category_01 = '" . $category_01 . "'";
			}
			
			if( $category_02 != 0 ){
				$w_sql .= " and sjs_case.category_02 = '" . $category_02 . "'";
			}
			
			if( $category_03 != 0 ){
				$w_sql .= " and sjs_case.category_03= '" . $category_03 . "'";
			}
			
			if( $style != 0 ){
				$w_sql .= " and sjs_case.artstyle= '" . $style . "'";
			}
			
			$sql = "select top ". $settings['size'] ." sjs_case.id, sjs_case.fm, sjs_case.username, sjs_case.case_name, sjs_case.category_01, sjs_case.category_02, sjs_case.category_03, sjs_case.addtime, sjs_info.true_name from".
			" sjs_case, sjs_info where". $w_sql ." and sjs_info.username = sjs_case.username and sjs_case.id not in (select top ". $settings['size']*($settings['page']-1) ." id from sjs_case where". $w_sql ." and sjs_case.fm <> '' order by addtime desc ) and sjs_case.fm <> '' order by addtime desc";
			$list = $this->sjs_case->get_case_list( $sql );
			if($list != false){
				foreach($list as $key=>$val){
					$list[$key]['thumb'] = $this->thumb->crop($val['fm'], 230, 170);
				}
			}
			
			$count = $this->sjs_case->get_case_count('select count(*) as icount from sjs_case where' . $w_sql);
			
			$this->tpl->assign('case_list', $list);
			
			
			
			$this->load->library('pagination');
			$this->pagination->currentPage = $settings['page'];
			$this->pagination->pageCount = $this->pagination->getPageCount($settings['size'], $count);
			$this->pagination->url_template_first = '/sjs/anli-' . implode('-', $params) . '-1.html';
			$this->pagination->url_template = '/sjs/anli-' . implode('-', $params) . '-<{page}>.html';
			$pagination = $this->pagination->toString(true);
			$this->tpl->assign('pagination', $pagination);
			$this->tpl->assign('params', $params);
			$this->tpl->assign('category_data', $this->sjs_case->case_category_enum());
			$this->tpl->assign('style_enum', $this->sjs_case->style_enum());
			
			
			// SEO
			$page_title = array();
			$page_keywords = array();
			
			$category_data = $this->sjs_case->case_category_enum();
			$style_enum = $this->sjs_case->style_enum();
			
			if( $category_01 != 0 ){
				foreach( $category_data as $key=>$val ){
					if( $val['id'] == $category_01 ){
						$category_01_name = $val['name'];
						$category_02_data = $val['childs'];
						break;
					}
				}
				
				array_push($page_title, $category_01_name);
				array_push($page_keywords, $category_01_name);
				
			}
			
			if( $category_02 != 0 ){
				foreach( $category_02_data as $key=>$val ){
					if( $val['id'] == $category_02 ){
						$category_02_name = $val['name'];
						$category_03_data = $val['childs'];
						break;
					}
				}
				array_push($page_title, $category_02_name);
				array_push($page_keywords, $category_02_name);
			}
			if( $category_03 != 0 ){
				foreach( $category_03_data as $key=>$val ){
					if( $val['id'] == $category_03 ){
						$category_03_name = $val['name'];
						break;
					}
				}
				array_push($page_title, $category_03_name);
				array_push($page_keywords, $category_03_name);
			}
			
			
			if( $style != 0 ){
				foreach( $style_enum as $key=>$val ){
					if( $val['id'] == $style ){
						array_push($page_title, $val['name'] . '风格');
						array_push($page_keywords, $val['name'] . '风格');
					}
				}
			}
			
			
			
			$current_keys = $page_title;
			$page_title = array_merge($page_title, array('设计装修案例图片欣赏','名师设计','上海装潢网'));
			$page_keywords = array_merge($page_keywords, array('装修图片','装修案例','名师设计','上海装潢网'));
			$page_description = implode(',', $current_keys) . "_设计装修案例图片欣赏_名师设计_上海装潢网";
			
			$this->tpl->assign('current_keys', implode('-', $current_keys));
			$this->tpl->assign('title', implode('-', $page_title) );
			$this->tpl->assign('keywords', implode(',', $page_keywords) );
			$this->tpl->assign('description', $page_description );
			// SEO end
			
			$this->tpl->display('member_design/channel/case_list.html');
			
			
		}
		
		function sjs_list($params){
			
			if( empty($params) ){ show_404(); }
			
			$params = explode('-', trim($params, '-'));
			$page = $params[count($params)-1];
			array_pop($params);
			
			$this->tpl->assign('params', $params);
			$this->load->model('sjs/info', 'infomation');
			$type_enum = $this->infomation->get_type_enum();
			$field_enum = $this->infomation->get_field_enum();
			array_shift($field_enum);
			$style_enum = $this->infomation->get_style_enum();
			array_shift($style_enum);
			$this->tpl->assign('type_enum', $type_enum);
			$this->tpl->assign('field_enum', $field_enum);
			$this->tpl->assign('style_enum', $style_enum);
			
			$settings = array(
				'page'=>$page,
				'size'=>12
			);
			
			$search = array();
			$search['type'] = $params[0];
			$search['field'] = $params[1];
			$search['style'] = $params[2];
			
			$w_sql = ' 1=1';
			
			if( $search['type'] != 0 ){
				//$w_sql .= " and type = '" . $search['type'] . "'";
				$w_sql .= " and type = '" . $search['type'] . "'";	// 做单一条件查询，不允许组合条件查询，因为设计师数量少
			}
			if( $search['field'] != 0 ){
				//$w_sql .= ' and field = ' . $search['field'];
				$w_sql .= " and field = '" . $search['field'] . "'";
			}
			if( $search['style'] != 0 ){
				//$w_sql .= ' and style = ' . $search['style'];
				$w_sql .= " and style = '" . $search['style'] . "'";
			}
			
			$sql = "select top " . $settings['size'] . " id, username, true_name, shen, city, town, zhiwei, style, field, face_image from sjs_info where". $w_sql ." and id not in (select top ". ($settings['page']-1)*$settings['size'] ." id from sjs_info where". $w_sql ." order by update_time desc) order by update_time desc";
			
			//echo( $sql );
			$list = $this->infomation->get_list( $sql );
			$count = $this->infomation->get_count('select count(*) as icount from sjs_info where' . $w_sql);
			$this->load->library('thumb');
			$this->load->model('sjs/sjs_case', 'sjs_case');
			
			foreach($list as $key=>$val){
				if( !empty( $val['face_image'] ) ){
					$list[$key]['thumb'] = $this->thumb->crop($val['face_image'], 125, 125);
				} else {
					$list[$key]['thumb'] = '/resources/member_design/channel_images/no_face.png';
				}
				
				$tmp_case_list = $this->sjs_case->get_case_list("select top 3 id, username, case_name, fm from sjs_case where username = '". $val['username'] ."' order by addtime desc");
				if($tmp_case_list != false){
					foreach($tmp_case_list as $key2=>$val2){
						$tmp_case_list[$key2]['thumb'] = $this->thumb->crop($val2['fm'], 135, 105);
					}
				}
				$list[$key]['cases'] = $tmp_case_list;
			}
			$this->tpl->assign('list', $list);
			
			$this->load->library('pagination');
			$this->pagination->currentPage = $settings['page'];
			$this->pagination->pageCount = $this->pagination->getPageCount($settings['size'], $count);
			$this->pagination->url_template_first = '/sjs/list-' . implode('-', $params) . '-1.html';
			$this->pagination->url_template = '/sjs/list-' . implode('-', $params) . '-<{page}>.html';
			$pagination = $this->pagination->toString(true);
			$this->tpl->assign('pagination', $pagination);
			
			$this->tpl->assign('icount', $count);
			
			// SEO =================================================
			$title = '';
			$keywords = '';
			$description = '';
			if( $params[0] != 0 ){
				foreach($type_enum as $key=>$val){
					if( $val[0] == $params[0] ){
						$title = $val[1];
					}
				}
			}
			if( $params[1] != 0 ){
				foreach($field_enum as $key=>$val){
					if( $val[0] == $params[1] ){
						$title =  $val[1] . '设计师';
					}
				}
			}
			if( $params[2] != 0 ){
				foreach($style_enum as $key=>$val){
					if( $val[0] == $params[2] ){
						$title =  $val[1] . '设计师';
					}
				}
			}
			$keywords = $title. ',设计师频道,上海装潢网';
			$title .= $title != '' ? '-':'';
			$title .= '设计师频道-上海装潢网';
			
			$this->tpl->assign('title', $title);
			$this->tpl->assign('keywords', $keywords);
			$this->tpl->display('member_design/channel/sjs_list.html');
		}
		
	}

