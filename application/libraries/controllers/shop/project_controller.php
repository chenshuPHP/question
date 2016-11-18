<?php

// 装修公司项目集中展示
class project_controller extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	private function get_tpl($tpl){
		return 'company/project/' . ltrim($tpl, '/');
	}
	
	public function _remap($uri, $args = array()){
		
		$uri = explode('-', strtolower($uri));
		
		$method = array_shift($uri);
		
		if( method_exists($this, $method) ){
			if( count($uri) > 0 ){
				$this->$method($uri);
			} else {
				$this->$method();
			}
		} else {
			show_404();
		}
		
	}
	
	public function attrs(){
		
		$this->load->model('company/project_category_model');
		
		$tree = $this->project_category_model->tree();
		
		$this->tpl->assign('tree', $tree);
		$this->tpl->display( $this->get_tpl('attrs.js.html') );
	}
	
	public function home($args = array()){
		
		// seo
		$page_title = '';
		
		$id = $this->gr('id');
		
		if( $id != '' ){
			if( ! preg_match('/^\d+(,\d+)?$/', $id) ){
				exit('参数格式错误');
			}
		}
		
		// 获取分页数据
		$page = $this->encode->get_page();
		
		if( $page == '' ) $page = 1;
		
		if( count($args) > 0 ) $page = $args[count($args)-1];
		
		
		$config = array(
			'size'=>32,
			'page'=>$page
		);
		
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60 * 60; 	// 单位 分钟, 缓存 60 分钟
		$this->tpl->cache_dir .= 'shop\\project\\';
		
		$tpl = $this->get_tpl('home.html');
		
		if( $id == '' ){
			$tpl_cache_id = $page;
		} else {
			$tpl_cache_id = $id . ',' . $page;
		}
		
		if( ! $this->tpl->isCached($tpl, $tpl_cache_id) ){
		
			$this->load->model('company/usercase', 'project_model');
			$this->load->model('company/project_category_model');
			
			$this->load->model('company/company', 'deco_model');
			
			$this->load->library('thumb');
			
			$where_sql = "fm_image <> '' and recycle = 0 and username in ( select username from company where delcode = 0 and hangye = '装潢公司' )";
			
			
			if( $id != '' ){
				
				$ids = explode(',', $id);
				
				$cats = $this->project_category_model->gets("select id, name, type from [user_case_attr] where id in (". implode(',', $ids) .")");
				$cats = $cats['list'];
				
				$where_sql .= " and 1=1";
				
				$type_cat = false;
				$style_cat = false;
				
				foreach($cats as $cat){
					if( $cat['type'] == 1 ){	// 房型
						$where_sql .= " and build_type_2 = '". $cat['id'] ."'";
						$type_cat = $cat;
						$page_title .= $cat['name'];
					}
					if( $cat['type'] == 0 ){	// 风格
						$where_sql .= " and style_name = '". $cat['id'] ."'";
						$style_cat = $cat;
						$page_title .= $cat['name'];
					}
				}
				
				$this->tpl->assign('type_cat', $type_cat);
				$this->tpl->assign('style_cat', $style_cat);
				
			}
			
			$sql = "select * from ( select id, username, casename as name, city, town, fm_image as fm, build_type_1 as t1, build_type_2 as t2, style_name as style, sdate, edate, num = row_number() over( order by addtime desc ) from [user_case] where ". $where_sql ." ) as tmp where num between ". ( ($config['page'] - 1) * $config['size'] + 1 ) ." and " . ( $config['page'] * $config['size'] );
			
			$sql_count = "select count(*) as icount from [user_case] where " . $where_sql;
			
			$result = $this->project_model->get_case_list($sql, $sql_count);
			$list = $result['list'];
			$list = $this->project_model->image_count_assign_case($list);
			
			$list = $this->deco_model->fill_collection($list, array('fields'=>array('username', 'company')), false);
			
			//echo('<!--');
			//var_dump($list);
			//echo('-->');
			
			foreach($list as $key=>$value){
				$list[$key]['thumb'] = $this->thumb->resize($value['fm'], 280, 0);
			}
			
			$urls = $this->config->item('url');
			$this->tpl->assign('url', $urls['project']);
			
			$this->load->library('pagination');
			$this->pagination->currentPage = $config['page'];
			$this->pagination->pageSize = $config['size'];
			$this->pagination->recordCount = $result['count'];
			if( $id != '' ){
				$this->pagination->url_template = $urls['project'] . 'home?page=<{page}>&id=' . $id;
				$this->pagination->url_template_first = $urls['project'] . 'home?id=' . $id;
			} else {
				$this->pagination->url_template = $urls['project'] . 'home?page=<{page}>';
				$this->pagination->url_template_first = $urls['project'] . 'home';
			}
			
			$this->tpl->assign('pagination', $this->pagination->toString(true));
			
			
			$tree = $this->project_category_model->tree(array(
				'other_opt'=>false	// 是否包含名叫 其他 的分类
			));
			
			$tree = $this->_build_tree_url($id, $tree);
			
			$this->tpl->assign('list', $list);
			$this->tpl->assign('tree', $tree);
			
			if( $page_title != '' )
				$page_title .= '-';
				
			$page_title .= '本站会员装修项目';
			
			$this->tpl->assign('page_title', $page_title);
			$this->tpl->assign('id', $id);
			
			
			$this->tpl->display($tpl, $tpl_cache_id);
		
		
		} else {
			
			$this->tpl->display($tpl, $tpl_cache_id);
			echo('<!-- cached -->');
			
		}

		
		
	}
	
	
	// 构造搜索条件的URL
	private function _build_tree_url($id, $tree){
		
		if( empty($id) ) return $tree;
		
		$id = explode(',', $id);
		
		$this->load->model('company/project_category_model');
		
		$cats = $this->project_category_model->gets("select id, name, pid, type from [user_case_attr] where id in (". implode(',', $id) .")");
		
		$cats = $cats['list'];
		
		$types = $tree[0];
		$styles = $tree[1];
		
		$url = $this->config->item('url');
		$url = $url['project'];
		
		// 房型 
		foreach($types['childs'] as $key=>$value){
			foreach($value['childs'] as $k=>$v){
				foreach($cats as $cat){
					if( $cat['type'] != $v['type'] ){
						$v['link'] = $url . 'home?id=' . $v['id'] . ',' . $cat['id'];	// 附加当前风格ID, 替换原link值
						break;
					}
				}
				$value['childs'][$k] = $v;
			}
			$types['childs'][$key] = $value;
		}
		
		// 风格 
		foreach($styles['childs'] as $key=>$value){
			foreach($cats as $cat){
				if( $value['type'] != $cat['type'] ){
					$value['link'] = $url . 'home?id=' . $value['id'] . ',' . $cat['id'];	// 附加当前房型ID
					break;
				}
			}
			$styles['childs'][$key] = $value;
		}
		
		return array($types, $styles);
		
	}
	
	private function _get_cache_dir(){
		
	}
	
	
}






















?>