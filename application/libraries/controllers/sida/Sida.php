<?php

class Sida extends MY_Controller {
	
	var $template_dir = 'xiehui/2016/';
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('body_class', 'w1200');
		//if( $this->gr('v') == '2016' ){
		//	$this->template_dir = 'xiehui/2016/';
		//}
	}
	
	// 扩展
	public function _remap($class, $args = array()){
		
		if( method_exists($this, strtolower($class)) ){
			if( count($args) > 0 ){
				$this->$class($args[0]);
			} else {
				$this->$class();
			}
		} else {
			$class_name = 'sida_' . $class;
			$director = dirname(__FILE__) . '\\';
			
			if( file_exists( $director . $class_name . '.php' ) ){
				
				include( $director . 'sida_base.php' );
				include( $director . $class_name . '.php' );
				
			} else {
				exit( $director . $class_name . '.php file not found' );
			}
			
			$object = new $class_name();
			$method_name = 'home';
			if( count($args) > 0 ){
				$method_name = array_shift($args);
			}
			if( count($args) > 0 ){
				$object->$method_name($args);
			} else {
				$object->$method_name();
			}
		}
	}
	
	// 协会介绍
	public function index(){
		
		$data = array(
			'id'=>'index',
			'title'=>'协会介绍',
			'keyword'=>'',
			'description'=>''
		);
		
		$this->tpl->assign('data', $data);
		$this->tpl->display( $this->template_dir . 'index.html');
		
	}
	
	// 资质申办
	public function zizhishenban(){
		
		$data = array(
			'id'=>'zizhishenban',
			'title'=>'资质申办',
			'keyword'=>'',
			'description'=>''
		);
		
		$this->tpl->assign('data', $data);
		$this->tpl->display( $this->template_dir . 'zizhishenban.html');
	}
	
	// 资质查询
	public function zizhichaxun(){
		$data = array(
			'id'=>'zizhichaxun',
			'title'=>'资质查询',
			'keyword'=>'',
			'description'=>''
		);
		
		/*
		$this->load->model('company/Company', 'company');
		$cmps = $this->company->getKouBeiList(
			array(
				'top'=>20,
				'fields'=>'company, username, logo'
			)
		);
		$this->tpl->assign('cmps', $cmps);
		*/
		//echo('<!--');
		//var_dump($cmps);
		//echo('-->');
		
		
		$this->tpl->assign('data', $data);
		$this->tpl->display($this->template_dir . 'zizhichaxun.html');
	}
	
	function zzcx_simple(){
		
		// phpinfo();
		
		//if( !isset($_REQUEST['key']) ) return;
		//$key = $_REQUEST['key'];
		//if( mb_check_encoding($key, 'GB2312') ){
		//	$key = mb_convert_encoding($key, 'UTF-8', 'GB2312');
		//}
		//$result = $this->_zzcx_search( $key );
	//	echo( $key );
	}
	
	function zzcx(){
		// 获取搜索关键词
		if( !isset($_REQUEST['key']) ) return;
		$key = $_REQUEST['key'];
		$result = $this->_zzcx_search( $key );
		echo( $result );
	}
	
	function _zzcx_search($key){
		
		$this->load->model('sida/SidaModel', 'xiehui');
		$object = $this->xiehui->searchZiZhi( $key );
		if( $object == false ){ 
			echo('{name:""}');
			return false;
		}
		
		$result = '';
		
		$result .= '{name:"'. $object['name'] .'"';
		
		if( $object['construct'] != false ){
			$result .= ',construct:true,construct_lv:"'. $object['construct']['lv'] .'",construct_code:"'. $object['construct']['code'] .'"';
		} else {
			$result .= ',construct:false';
		}
		
		if( $object['design'] != false ){
			$result .= ',design:true,design_lv:"'. $object['design']['lv'] .'",design_code:"'. $object['design']['code'] .'"';
		} else {
			$result .= ',design:false';
		}
		
		if( $object['supervise'] != false ){
			$result .= ',supervise:true,supervise_lv:"'. $object['supervise']['lv'] .'",supervise_code:"'. $object['supervise']['code'] .'"';
		} else {
			$result .= ',supervise:false';
		}
		
		if( $object['member'] != false ){
			$result .= ',member:"'. $object['member'] .'"';
		} else {
			$result .= ',member:false';
		}
		
		$result .= '}';
		
		return $result;
		
	}
	
	// 投诉表单页面
	function tousu(){
		$data = array(
			'id'=>'tousu',
			'title'=>'我要投诉',
			'keyword'=>'',
			'description'=>''
		);
		$this->tpl->assign('data', $data);
		
		$this->echo_left_ts_topic();	// 排行榜
		
		$this->tpl->display( $this->template_dir . 'tousu.html' );
	}
	
	/*
	public function tousu_b(){
		$data = array(
			'id'=>'tousu',
			'title'=>'我要投诉',
			'keyword'=>'',
			'description'=>''
		);
		$this->tpl->assign('data', $data);
		$this->tpl->display('xiehui/tousu_b.html');
	}
	*/
	
	
	public function tousu_submit(){
		
		// $ajax 请求
		if( $this->gf('token') == config_item('shzh_sync_token') )
		{
			header("Access-Control-Allow-Origin:*");
			$error = '';
			
			$complaint = $this->gf('complaint');
			$complaint = $this->encode->htmldecode( $complaint );
			$complaint = json_decode( $complaint, TRUE );
			
			$this->load->model('sida/tousuModel', 'tousu_model');

			try
			{
				$id = $this->tousu_model->add( $complaint );
			}
			catch(Exception $e)
			{
				$error = $e->getMessage();
			}
			
			if( $error == '' )
			{
				json_echo( array(
					'type'		=> 'success',
					'id'		=> $id,
					'complaint'	=> $this->gf('complaint')
				) );
			}
			else
			{
				json_echo( array(
					'type'		=> 'error',
					'error'		=> $error
				) );
			}
		}
		else		// 原始页面请求
		{
			$this->load->model('sida/TousuModel', 'tousu');
			// 添加到数据库， 返回新的ID
			$newId = $this->tousu->add();
			$this->load->helper('url');
			redirect('/sida/tousu_submit_finish');
		}
		
	}
	
	function tousu_submit_finish(){
		$data = array(
			'id'=>'tousu_submit_finish',
			'title'=>'我要投诉',
			'keyword'=>'',
			'description'=>''
		);
		$this->tpl->assign('data', $data);
		//$this->tpl->display( $this->template_dir . 'tousu_submit_finish.html');
		$this->tpl->display( 'xiehui/2015/tousu_submit_finish.html');
	}
	
	private function echo_left_ts_topic(){
		
		$this->load->model('sida/TousuModel', 'tousu');
		
		// 获取投诉排行榜 
		$topic = $this->tousu->get_topic(array(
			'size'=>10
		));
		$this->tpl->assign('topic', $topic);
		
		// 获取装修公司口碑值排行榜
		$this->load->model('company/company');
		$koubei_cmps = $this->company->get_list("select top 10 username, company, shortname, koubei from company where register = 2 and delcode = 0 and hangye = '装潢公司' and flag = 2 order by koubei desc", '', TRUE);
		$this->tpl->assign('koubei_cmps', $koubei_cmps['list']);
		
	}
	
	// 投诉受理（投诉列表）
	public function tousushouli($page=1, $tpl = ''){
		
		
		$this->load->model('sida/TousuModel', 'tousu');
		$this->load->model('sida/tousu_config_model');
		$this->load->model('sida/tousu_image_model');
		
		$data = array(
			'id'			=> 'tousushouli',
			'title'			=> '装修投诉受理',
			'keyword'		=> '',
			'description'	=> ''
		);
		
		$args = array(
			'finish'		=> $this->gr('finish'),
			'target'		=> $this->gr('target'),
			'status'		=> $this->gr('status')
		);
		
		if( $args['finish'] == 1 ) $args['status'] = 20;
		
		$status = $this->tousu_config_model->get_status_opts();
		
		$where = array();
		$params = array();
		$title = array();
		
		if( $args['target'] != '' ){
			$args['target'] = iconv('gbk', 'utf-8', $args['target']);
			$where[] = "danwei like '%". $args['target'] ."%'";
			$params[] = "target=" . $args['target'];
			$title[] = '投诉' . $args['target'];
		}
		
		if( $args['status'] != '' ){
			$where[] = "status = '". $args['status'] ."'";
			$params[] = "status=" . $args['status'];
			foreach($status as $item)
			{
				if( $item['id'] == $args['status'] )
				{
					$title[] = $item['name'];
					break;
				}
			}
		}
		
		$title[] = '装修投诉受理';
		
		$settings = array(
			'size'=>20,
			'page'=>$page,
			'fields'=>array('id', 'username', 'title', 'classname', 'danwei', 'puttime', 'status'),
			'where'=>$where
		);
		
		$res = $this->tousu->getList($settings);
		
		$count = $res['count'];
		$list = $res['list'];
		unset( $res );
		$list = $this->tousu_config_model->attr_assign($list);
		
		$this->load->library('Pagination', 'pagination');
		$this->pagination->currentPage = $page;
		$this->pagination->recordCount = $count;
		$this->pagination->pageSize = $settings['size'];
		
		$url = $this->config->item('curr_base_url');
		
		if( count($params) == 0 ){
			
			$this->pagination->url_template = $url . '/sida/tousushouli-<{page}>.html';
			$this->pagination->url_template_first = $url . '/sida/tousushouli.html';
			
		} else {
			
			$this->pagination->url_template = $url . '/sida/tousushouli-<{page}>.html?' . implode('&', $params);
			$this->pagination->url_template_first = $url . '/sida/tousushouli.html?' . implode('&', $params);
			
		}
		
		$pagination = $this->pagination->toString(true);

		
		// 获取 投诉的图片 ，返回数组 array 
		$img = array(
				'fields'	=>	'id,tsid,path',
				'num'		=>	3,
				'key_name'	=>	'images'
			);
		$this->tousu_image_model->getImages($list,$img);
		
		
		var_dump2($list);
		
		$this->tpl->assign('list', $list);
		

		$this->tpl->assign('pagination', $pagination);
		
		$data['title'] = implode('-', $title);
		
		$this->tpl->assign('data', $data);
		
		$this->echo_left_ts_topic();	// 排行榜
		
		$this->tpl->assign('args', $args);
		$this->tpl->assign('target', $args['target']);
		
		$this->tousu->assign_status_count( $status );
		
		$this->tpl->assign('status', $status);
		$this->tpl->assign('tousu_all_count', $this->tousu->get_all_count());
		
		$this->tpl->display( $this->template_dir . 'tousushouli.html' );
	}
	
	public  function tousu_view($id){
		
		$this->load->model('sida/TousuModel', 'tousu');
		$this->load->model('sida/tousu_config_model');
		
		$object = $this->tousu->getSingle( $id, array(
			'fields'		=> 'id, username, title, content, classname, puttime, tel, mobile, address, fangtype, mianji, xingshi, zaojia, danwei, rejion, he, xie, backContent, source, status, recycle, puttime, revok, revoke_info, revoke_time, showcount_base, showcount'
		) );
		
		if( $object['recycle'] == 1 ){
			show_error('page not found', 404);
			exit();
		}
		
		if( !$object ){ show_404(); }
		
		$object = $this->tousu_config_model->attr_assign($object);
		
		$data = array(
			'id'=>'tousu_view',
			'title'=>$object['title'] . '_' . $object['danwei'],
			'keyword'=>'',
			'description'=>''
		);
		
		if( $object['revok'] == 1 )
		{
			$data['title'] = $object['title'];
		}
		
		$r = $this->gr('r');
		
		$this->tpl->assign('rurl', $r);
		$this->tpl->assign('data', $data);
		$this->tpl->assign('object', $object);
		
		
		// var_dump2( $object );
		
		// 投诉反馈记录
		$this->load->model('sida/tousu_recordset_model');
		$recordset = $this->tousu_recordset_model->get_recordset($object['id'], array(
			'fields'	=> 'id, detail, addtime, admin',
			'order'		=> 'order by addtime asc',
			'format'	=> true
		));
		if( count( $recordset ) == 0 ) $recordset = false;
		$this->tpl->assign('recordset', $recordset);
		
		$this->echo_left_ts_topic();	// 排行榜
		
		// 第一页评论
		$forum = file_get_contents( $this->get_complete_url('/sida/tsforum/gets?tsid=' . $object['id']) );
		//$forum = file_get_contents( $this->get_complete_url('/sida/tsforum/gets?tsid=14022') );
		
		$this->tpl->assign('forum', $forum);
		
		$this->tpl->display($this->template_dir . 'tousu_view.html');
		
		
	}
	
	// 协会新闻
	function latest_news($top=5){
		$this->load->model('sida/SidaModel', 'xiehui');
		$list = $this->xiehui->get_latest_news(array('size'=>$top));
		$data = array();
		foreach($list as $key=>$val){
			array_push($data, '{title:"'. $val['title'] .'", url:"'. $val['url'] .'"}');
		} 
		$result = '['. implode(',', $data) .']';
		
		$this->load->library('encode');
		$callback = $this->encode->get_request_encode('callback');
		if( empty($callback) ){
			echo( $result );
		} else {
			echo($callback . "('". $result ."')");
		}
	}
	
	
	// 首页协会查询内嵌页面
	public function index_frame_search(){
		$this->tpl->display('xiehui/index_frame_search.html');
	}
	
	
}
?>