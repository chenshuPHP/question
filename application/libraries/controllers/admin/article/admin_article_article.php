<?php

// 资讯文章管理
class admin_article_article extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	/* =====================
		  文章管理列表浏览
	========================*/
	public function manage(){
		
		$this->load->model('article/article');
		$this->load->model('manager/manager_model');
		
		$cfg = array(
			'page'=>$this->gr('page'),
			'size'=>20
		);
		
		$args = array(
			'key'=>$this->gr('key'),
			's'=>$this->gr('s')
		);
		
		$where = '1=1';
		$order = '';
		$params = '';
		
		if( ! empty($args['key']) ){
			$args['key'] = iconv("gbk", "utf-8", $args['key']);
			$params .= 'key=' . $args['key'];
			$where .= " and contains((title, keyword), '\"". $args['key'] ."\"')";	// 全文索引搜索
		}
		
		if( ! empty($args['s']) ){
			$params .= "s=" . $args['s'];
		}
		
		switch($args['s']){
			case 'showcount':
				$order = "order by showcount desc";
				break;
			default:
				$order = "order by addtime desc";
				break;
		}
		
		// 城市设置
		// 2015-07-06
		if( ! empty($this->admin_city_id) ){
			$where .= " and city_id = '". $this->admin_city_id ."'";
		}
		
		if( ! preg_match('/^[1-9]\d*$/', $cfg['page']) ) $cfg['page'] = 1;
		
		$sql = "select * from (select id, title, clsid, admin, addtime, keyword, showcount, row_number() over(". $order .") as num from art_art where ". $where .") as temp where num between ". (($cfg['page']-1)*$cfg['size']+1) ." and " . $cfg['page'] * $cfg['size'];
		
		//echo($sql);
		
		$sql_count = "select count(*) as icount from art_art where " . $where;
		
		$result = $this->article->get_list($sql, $sql_count);
		$admins = array();
		foreach($result['list'] as $key=>$val){
			array_push($admins, $val['admin']);
		}
		$admins = $this->manager_model->get_manager($admins);
		
		// 装载用户信息
		foreach($result['list'] as $key=>$val){
			foreach($admins as $value){
				if( strtolower($val['admin']) == strtolower($value['username']) ){
					$result['list'][$key]['admin_info'] = $value;
					break;
				}
			}
		}
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->url_template_first = $this->manage_url . 'article/article/manage' . (empty($params) ? '' : '?' . $params);
		$this->pagination->url_template = $this->manage_url . 'article/article/manage?page=<{page}>' . (empty($params) ? '' : '&' . $params);
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $result['count']);
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->assign('args', $args);
		$this->tpl->assign('module', 'manage');
		$this->tpl->display('admin/article/article_manage.html');
	}
	
	
	/* =====================
		   添加文章界面
	======================== */
	/*public function add(){
		$this->tpl->assign('module', 'add');
		
		// 分类数据
		$cats = $this->_get_all_cate();
		$this->tpl->assign('cats', $cats);
		
		$this->tpl->display('admin/article/article_add.html');
	}*/
	/*
		time  					2016/11/10
		author 					段
		description				显示 添加 页面
	*/
	public function add(){
		$this->tpl->assign('module', 'add');
		
		// 分类数据
		$cats = $this->_get_all_cate();
		
		$this->load->model('city_model');
		
		$city = $this->city_model->get_fenzhan_city();
		
		$this->tpl->assign('city', $city);
		$this->tpl->assign('cats', $cats);

		$this->tpl->display('admin/article/article_add.html');
	}
	
	
	/* =====================
		文章添加提交处理
	======================== */
	public function add_submit(){
		
		$cate = array(
			'cate01'		=> $this->gf('cate01'),
			'cate02'		=> $this->gf('cate02'),
			'cate03'		=> $this->gf('cate03')
		);
		
		$article = array(
			'title'				=> $this->gf('title'),
			'stitle'			=> $this->gf('stitle'),
			'keyword'			=> $this->gf('keyword'),
			'editor_content'	=> $this->gf('editor_content'),
			'base_showcount'	=> $this->gr('base_view_count'),
			'recmd'				=> $this->gf('recmd')
		);
		
		if( empty( $article['recmd'] ) )
		{
			$article['recmd'] = 0;
		}
		
		
		if( empty($cate['cate03']) ){
			$article['cate'] = $cate['cate02'];
			$article['cates'] = ',' . $cate['cate01'] . ',' . $cate['cate02'] . ',';
		} else {
			$article['cate'] = $cate['cate03'];
			$article['cates'] = ',' . $cate['cate01'] . ',' . $cate['cate02'] . ',' . $cate['cate03'] . ',';
		}
		
		$this->load->model('article/article_active_model');
		
		$article['description'] = $this->article_active_model->extract_description($article['editor_content']);
		$article['image'] = $this->article_active_model->extract_image($article['editor_content']);
		
		$article['addtime'] = date('Y-m-d H:i:s');
		
		$article['admin'] = $this->admin_username;
		//$article['city_id'] = $this->admin_city_id;
		$article['city_id'] = $this->gf('city_id');
		$article['showcount'] = 1;
		
		if( empty($article['stitle']) )
			$article['stitle'] = $article['title'];
			
		if( empty( $article['base_showcount'] ) )
			$article['base_showcount'] = 0;
		
		try{
			$id = $this->article_active_model->article_add($article);
			$this->tpl->assign('module', 'add');
			$this->load->model('article/article');
			$art = $this->article->getArt($id, 'id, clsid');
			$this->tpl->assign('art', $art);
			$this->tpl->display('admin/article/article_add_success.html');
		}catch(Exception $e){
			echo($e->getMessage());
		}
	}

	/* =====================
		   修改文章界面
	======================== */
	public function edit(){
		$r = $this->gr('r');
		$id = $this->gr('id');
		$this->load->model('article/article');
		$article = $this->article->getArt($id, 'id, title, keyword, scontent, admin, cid, clsid, short_title, base_showcount, showcount, city_id, recmd');
		
		if( ! empty( $this->admin_city_id ) && $article['city_id'] != $this->admin_city_id ){
			exit('无法修改, 城市不匹配');
		}
		
		$article['keyword'] = implode(' ', $article['keyword']);
		$article['cid'] = trim($article['cid'], ',');
		
		if( empty($article['recmd']) ) $article['recmd'] = 0;
		
		$this->load->model('city_model');
		
		$city = $this->city_model->get_fenzhan_city();
		
		$this->tpl->assign('city', $city);
		
		
		// 分类数据
		$cats = $this->_get_all_cate();
		$this->tpl->assign('cats', $cats);
		$this->tpl->assign('article', $article);
		$this->tpl->assign('module', 'edit');
		$this->tpl->assign('r', $r);
		$this->tpl->display('admin/article/article_edit.html');
	}
	
	/* =====================
		  修改文章提交处理
	======================== */
	public function edit_submit(){
		$cate = array(
			'cate01'		=> $this->gf('cate01'),
			'cate02'		=> $this->gf('cate02'),
			'cate03'		=> $this->gf('cate03')
		);
		
		$r = $this->gf('r');
		
		$article = array(
			'id'				=> $this->gf('id'),
			'title'				=> $this->gf('title'),
			'stitle'			=> $this->gf('stitle'),
			'keyword'			=> $this->gf('keyword'),
			'editor_content'	=> $this->gf('editor_content'),
			'base_showcount'	=> $this->gr('base_view_count'),
			'recmd'				=> $this->gf('recmd')
		);
		
		if( empty( $article['recmd'] ) ) $article['recmd'] = 0;

		if( empty($cate['cate03']) ){
			$article['cate'] = $cate['cate02'];
			$article['cates'] = ',' . $cate['cate01'] . ',' . $cate['cate02'] . ',';
		} else {
			$article['cate'] = $cate['cate03'];
			$article['cates'] = ',' . $cate['cate01'] . ',' . $cate['cate02'] . ',' . $cate['cate03'] . ',';
		}
		
		$this->load->model('article/article_active_model');
		
		$article['description'] = $this->article_active_model->extract_description($article['editor_content']);
		
		//var_dump($article['description']);
		//exit();
		
		$article['image'] = $this->article_active_model->extract_image($article['editor_content']);
		
		// $article['addtime'] = date('Y-m-d H:i:s');
		// $article['admin'] = $this->admin_username;
		// $article['showcount'] = 1;
		
		// 修改文章不会修改所属城市
		//$article['city_id'] = $this->admin_city_id;
		
		$article['city_id'] = $this->gf('city_id');
		
		if( empty($article['stitle']) )
			$article['stitle'] = $article['title'];
			
		if( empty( $article['base_showcount'] ) )
			$article['base_showcount'] = 0;
		
		try{
			
			if( $this->article_active_model->article_edit($article) ){
				
				// 清理缓存
				$this->load->model('article/article_tpl_model');
				$this->load->library('tpl');
				if( $this->article_tpl_model->article_detail_cache === TRUE )
				{
					$tpl = $this->article_tpl_model->get_detail_tpl();
					$cache_id = $article['id'];
					$this->tpl->caching = $this->article_tpl_model->article_detail_cache;
					$this->tpl->cache_dir = $this->article_tpl_model->get_cache_dictionary($cache_id);
					$this->tpl->clearCache($this->article_tpl_model->get_detail_tpl(), $cache_id);
				}
				
				echo('<script>alert("修改成功");location.href="'. $r .'";</script>');
			}
			
		}catch(Exception $e){
			echo($e->getMessage());
		}
	}
	
	/* =====================
		     删除文章
	======================== */
	public function delete(){
		
		exit('暂时不支持删除');
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		try{
			$this->load->model('article/article_active_model');
			$this->article_active_model->article_delete($id);
			echo('<script>location.href="'. $r .'";</script>');
		}catch(Exception $e){
			echo($e->getMessage());
		}
	}
	
	private function _get_all_cate(){
		$this->load->model('article/category');
		$cates = $this->category->get_status_all();
		return $cates;
	}
}

?>