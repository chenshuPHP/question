<?php

// 预算下载列表筛选
class budget_budgets extends budget_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		
		$this->load->model('multi/multi_info_model');
		
		// == 左侧
		$url = $this->multi_info_model->get_url_info();
		$decos = $this->multi_info_model->get_koubei_decos();
		
		$diarys = $this->multi_info_model->get_diarys();
		$this->tpl->assign('decos', $decos);
		$this->tpl->assign('diarys', $diarys['list']);
		$this->tpl->assign('multi_url', $url['multi_url']);
		$this->tpl->assign('res_multi_url', $url['res_multi_url']);
		// == end
		
		$cfg = array(
			'size'=>20,
			'page'=>$this->encode->get_page()
		);
		
		// 报价条件
		$cid = $this->gr('cid');
		$this->load->model('budget/budget_config_model');
		$this->load->model('budget/budget_model');
		$categories = $this->budget_config_model->roots();
		$categories = $this->budget_config_model->child_assign($categories);
		
		$categories = $this->_build_categories_url($categories, $cid);					// 所有分类
		$selected_categories = $this->_get_selected_categories($categories, $cid);		// 已经选择的 分类
		
		$params = '';
		$where = "1=1";
		
		if( ! empty($cid) ){
			
			//echo('<!--');
			//var_dump($cid);
			//echo('-->');
			
			if( ! preg_match('/^\d+(\,\d+)*$/', $cid) ){
				show_404('参数格式错误');
				exit();
			}
			
			$params .= 'cid=' . $cid;
			$cids = explode(',', $cid);
		} else {
			$cids = array();
		}
		
		if( count($cids) != 0 ){
			$where .= " and id in ( select bid from ( select count(*) as icount, bid from [budget_attr] where cid in (". implode(',', $cids) .") group by bid ) as t1 where icount = '". count($cids) ."' )";
		}
		
		$sql = "select * from ( select id, name, area, row_number() over(order by addtime desc) as num from budget where ". $where ." ) as temp where num between ". (($cfg['page']-1) * $cfg['size'] + 1) ." and " . ( $cfg['page'] * $cfg['size'] );
		
		$sql_count = "select count(*) as icount from budget where " . $where;
		
		$budgets = $this->budget_model->get_list($sql, $sql_count);
		$count = $budgets['count'];
		$budgets = $budgets['list'];
		$budgets = $this->budget_config_model->cfg_assign_budgets($budgets);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->pageSize = $cfg['size'];
		$this->pagination->recordCount = $count;
		
		$urls = $this->config->item('url');
		$url = $urls['www'] . '/budget/budgets';
		if( $params != '' ){
			$this->pagination->url_template = $url . '?'. $params .'&page=<{page}>';
			$this->pagination->url_template_first = $url . '?' . $params;
		} else {
			$this->pagination->url_template = $url . '?page=<{page}>';
			$this->pagination->url_template_first = $url;
		}
		$pagination = $this->pagination->toString(true);
		$this->tpl->assign('pagination', $pagination);
		
		
		
		$this->tpl->assign('categories', $categories);
		$this->tpl->assign('selected_categories', $selected_categories);
		
		$this->tpl->assign('budgets', $budgets);
		$this->tpl->assign('cfg', $cfg);
		
		// seo
		$title = '';
		$keywords = array();
		foreach($selected_categories as $item){
			$title .= $item['name'];
			$keywords[] = $item['name'];
		}
		
		$keywords[] = '上海市装饰行业装修指导价';
		
		$this->tpl->assign('title', $title .= '上海市装饰行业装修指导价');
		$this->tpl->assign('keywords', implode(',', $keywords));
		$this->tpl->display('budget/budget_list.html');
	}
	
	
	// 获取已经选中的分类
	private function _get_selected_categories($all, $cid){
		
		if( empty($cid) ){
			$cid = array();
		} else {
			$cid = explode(',', $cid);
		}
		
		$result = array();
		
		foreach($all as $key=>$value){
			foreach($value['childs'] as $k=>$v){
				if( in_array($v['id'], $cid) ){
					$result[] = $v;
				}
			}
		}
		
		return $result;
		
	}
	
	// 构建分类URL
	private function _build_categories_url($categories, $cid){
		
		$urls = $this->config->item('url');
		$url = rtrim($urls['www'], '/') . '/budget/budgets';
		
		if( empty($cid) ){
			$cid = array();
		} else {
			$cid = explode(',', $cid);
		}
		
		foreach($categories as $key=>$value){
			
			// 去掉一个当前分类下的子ID
			$temp = array();
			$ids = array();
			foreach($value['childs'] as $item){
				$ids[] = $item['id'];
			}
			
			foreach($cid as $id){
				if( ! in_array($id, $ids, false) ){
					$temp[] = $id;
				}
			}
			
			foreach($value['childs'] as $k=>$v){
				$t = $temp;
				$t[] = $v['id'];
				sort($t);
				$v['link'] = $url . '?cid=' . implode(',', $t);
				$value['childs'][$k] = $v;
			}
			
			$categories[$key] = $value;
			
		}
		
		return $categories;
				
	}
	
}
?>