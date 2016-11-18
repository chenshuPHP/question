<?php

class Jianliriji extends MY_Controller {
	
	function __construct(){
		
		parent::__construct();
		$this->load->library('encode');
		
		$this->tpl->assign('body_class', 'w1200');
		
	}
	
	/*
	public function sync_image_count(){
		
		$this->load->model('diary/diary_model');
		$this->load->model('diary/diary_extend_model');
		
		$sql = "select top 10 id, detail from diary where image_count = 0";
		$res = $this->diary_model->get_list($sql);
		
		if( $res['count'] == 0 ) exit('done');
		
		$res = $res['list'];
		
		$res = $this->diary_extend_model->image_count_assign($res);
		
		$this->diary_model->update_image_count($res);
		
	}
	*/
	

	public function index(){
		$this->load->library('encode');
		$this->load->library('pagination');
		
		$this->load->model('diary/diary_model');
		$this->load->model('diary/diary_extend_model');
		
		$page = $this->encode->get_request_encode('page');
		if( empty($page) ) $page = 1;
		$cfg = array('size'=>10, 'page'=>$page);
		
		
		//$tpl = 'jianliriji/index.html';
		
		//if( $this->gr('version') == 2015 ){
			$tpl = 'jianliriji/2015/home.html';
		//}
		
		
		$sql = "select top ". $cfg['size'] ." id, title, town, address, area, budget, deco_name, addtime, detail, image_count from diary where id not in (select top ". ($cfg['page']-1)*$cfg['size'] ." id from diary order by addtime desc) order by addtime desc";
		$count_sql = "select count(*) as icount from diary";
		$res = $this->diary_model->get_list($sql, $count_sql);

		$res['list'] = $this->diary_extend_model->image_count_assign($res['list']);
		$res['list'] = $this->diary_extend_model->image_path_assign($res['list'], array('size'=>5));
		foreach($res['list'] as $key=>$val){
			$val['desc'] = $this->encode->get_text_description($val['detail'], 150, false);
			$res['list'][$key] = $val;
			unset($res['list'][$key]['detail']);
		}
		
		$this->tpl->assign('list', $res['list']);
		
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $res['count']);
		$this->pagination->url_template = '/jianliriji/index.html?page=<{page}>';
		$this->pagination->url_template_first = '/jianliriji/index.html';
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->display( $tpl );
		
	}
	
	public function view(){
		
		$this->load->library('encode');
		$id = $this->encode->get_request_encode('id');
		
		$this->load->model('diary/diary_model');
		
		$object = $this->diary_model->get_object($id);
		
		$object['detail'] = $this->encode->htmldecode($object['detail']);
		
		$this->tpl->assign('object', $object);
		$this->tpl->assign('prev', $this->diary_model->get_prev($id));
		$this->tpl->assign('next', $this->diary_model->get_next($id));
		
		//echo('<!--');
		//var_dump($object);
		//echo('-->');
		
		//if( $this->gr('v2') == '1' ){
			$this->tpl->display('jianliriji/2015/view.html');
		///} else {
		//	$this->tpl->display('jianliriji/view.html');
		//}
		
		
		
	}
	
	public function get_shares_data(){
		
		$this->load->library('encode');
		$this->load->model('diary/diary_model');
		$single_mode = true;
		if( $this->encode->getFormEncode('size') > 1 ) $single_mode = false;

		if( ! $single_mode ){

			$cfg = array();
			$cfg['page'] = $this->encode->getFormEncode('page');
			$cfg['size'] = $this->encode->getFormEncode('size');
			$sql = "select top ". $cfg['size'] ." id, title, town, address, area, deco_name, addtime from diary where id not in (select top ". ($cfg['page']-1)*$cfg['size'] ." id from diary order by addtime desc) order by addtime desc";
			$count_sql = "select count(*) as icount from diary";
			$res = $this->diary_model->get_list($sql, $count_sql);
			echo(json_encode($res));

		} else {

			$id = $this->encode->getFormEncode('id');
			$result = array();
			$result['diary'] = $this->diary_model->get_object($id);
			$result['prev'] = $this->diary_model->get_prev($id);
			$result['next'] = $this->diary_model->get_next($id);
			
			echo(json_encode($result));
			
		}
	}
	
	
	
}

?>