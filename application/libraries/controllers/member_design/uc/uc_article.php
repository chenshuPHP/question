<?php

class uc_article extends uc_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'article');
	}
	
	public function manage(){
			
		$page = $this->gr('page');
		if( ! preg_match('/^[1-9]\d*$/', $page) ) $page = 1;
		
		$this->load->model('sjs/sjs_article', 'sjs_article');
		
		// 读取分类信息
		$cat_list = $this->sjs_article->get_cat_list($this->info['username'], true);
		$this->tpl->assign('cat_list', $cat_list);
		
		$settings = array( 'size'=>20, 'page'=>$page );
		$list = $this->sjs_article->get_article_list("select top ". $settings['size'] ." id, username, title, cat_id, addtime, showcount, repcount from sjs_article where username = '". $this->info['username'] ."' and id not in (select top ". $settings['size'] * ($settings['page'] - 1) ." id from sjs_article where username = '". $this->info['username'] ."' order by id desc) order by id desc");
		$this->tpl->assign('list', $list);
		
		$count = $this->sjs_article->get_article_count("select count(*) as icount from sjs_article where username = '". $this->info['username'] ."'");
		$this->load->library('pagination');
		$this->pagination->baseUrl = $this->config->item('curr_base_url') . '/member_design/article_manage.html';
		$this->pagination->currentPage = $page;
		$this->pagination->delimiter = '_';
		$pageCount = $this->pagination->getPageCount($settings['size'], $count);
		$this->pagination->pageCount = $pageCount;
		$pagination = $this->pagination->toString(true);
		$this->tpl->assign('count', $count);
		$this->tpl->assign('pagination', $pagination);
		
		$this->tpl->display('member_design/ucenter/uc_article_manage.html');
			
	}
	
	// 文章的添加与编辑界面
	public function active(){
		
		$this->load->model('sjs/sjs_article', 'sjs_article');
		
		$id = $this->gr('id');
		
		if( $id != 0 && is_numeric($id) ){
			$object = $this->sjs_article->get_art($id);
			$this->load->library('encode');
			$object['detail'] = $this->encode->htmldecode($object['detail']);
			$this->tpl->assign('object', $object);
		}
		$cat_list = $this->sjs_article->get_cat_list($this->info['username']);
		$this->tpl->assign('cat_list', $cat_list);
		
		$this->tpl->display('member_design/ucenter/uc_article_active.html');
	}
	
	// 添加/修改提交处理
	public function active_handler(){
		$this->load->model('sjs/sjs_article', 'sjs_article');
		$this->load->library('encode');
		$object = array();
		$object['id'] = $this->gf('id');
		$object['art_name'] = $this->gf('art_name');
		$object['cat_id'] = $this->gf('cat_id');
		$object['detail'] = $this->gf('editor_area');
		$object['username'] = $this->info['username'];
		$object['addtime'] = date('Y-m-d H:i:s');
		try{
			if( empty($object['id']) ){
				$this->sjs_article->add_article($object);
				echo('<script type="text/javascript">alert("添加成功");location.href="'. $this->ucenter_url .'/article/manage";</script>');
			} else {
				$this->sjs_article->edit_article($object);
				echo('<script type="text/javascript">alert("修改成功");location.href="'. $this->ucenter_url .'/article/active?id='. $object['id'] .'";</script>');
			}
		}catch(Exception $e){
			echo($e->getMessage());
		}
	}
	
	// 文章删除
	public function delete(){
		$this->load->model('sjs/sjs_article', 'sjs_article');
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		$this->sjs_article->delete_article($id, $this->info['username']);
		
		echo('<script type="text/javascript">location.href="'. $r .'";</script>');
		
	}
	
	// 自定义分类的添加与修改
	public function custom_cate_active(){
		$this->load->library('encode');
		$object['cat_id'] = $this->gf('id');
		$object['cat_name'] = $this->gf('name');
		$object['username'] = $this->info['username'];
		$this->load->model('sjs/sjs_article', 'sjs_article');
		
		if( empty($object['cat_id']) ){
			$id = $this->sjs_article->cat_add($object);
		} else {
			$id = $this->sjs_article->cat_edit($object);
		}
		
		echo(json_encode( array('id'=>$id,  'name'=>$object['cat_name']) ));
	}
	
	// 自定义分类的删除
	public function custom_cate_delete(){
		$object['cat_id'] = $this->gf('id');
		$object['cat_name'] = $this->gf('name');
		$object['username'] = $this->info['username'];
		$this->load->model('sjs/sjs_article', 'sjs_article');
		try{
			$this->sjs_article->cat_delete($object);
			echo('1');
		} catch(Exception $e) {
			echo($e->getMessage());
		}
	}
	
	
	// 文章富文本图片上传
	public function rich_text_image_upload(){
		
		$this->load->model('sjs/sjs_article', 'sjs_article');
		
		$info = $this->sjs_article->article_image_upload();
		
		if( $info !== false ){
			echo("{'url':'". $info['url'] ."', 'title':'". $info['title'] ."', 'state':'SUCCESS'}");
		} else {
			echo('0');
		}
		
	}
	
	
}

?>