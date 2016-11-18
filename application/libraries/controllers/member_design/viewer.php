<?php
	
	class Viewer extends MY_Controller {
		
		var $infomation_data = NULL;
		var $template_manager = NULL;
		
		
		function __construct(){
			parent::__construct();
		}
		
		private function _init($username){
			$this->load->model('sjs/info', 'infomation');
			$info = $this->infomation->getInfomation($username);
			if( $info == false ){
				show_404();
				exit();
			}
			$this->infomation_data = $info;
			if( $info['simple'] == true ){
				show_404();
			}
		}
		
		private function _load_template_manager(){
			
			if($this->template_manager != NULL){
				return;
			}
			
			if( !empty( $this->infomation_data['blog_skin_id'] ) ){
				$skin_id = $this->infomation_data['blog_skin_id'];
			} else {
				$skin_id = 'default';
			}
			$file = FCPATH . 'application\\controllers\\member_design\\skin_class\\' . $skin_id . '_skin_class.php';
			$class_name = $skin_id . '_skin_class';
			if( file_exists( $file ) ){
				include($file);
				$this->template_manager = new $class_name();
				$this->template_manager->init( $this->infomation_data );
			} else {
				echo('加载失败，请刷新重试.');
			}
		}
		
		//  留言板的提交
		function lyb_submit($username){
			
			$this->load->library('encode');
			$this->load->model('LoginModel', 'login');
			$this->load->model('sjs/lyb', 'lyb');
			
			$object = array();
			$object['username'] = $username;
			$object['content'] = $this->encode->getFormEncode('content');
			$login_info = $this->login->check_login();
			if( $login_info == false ){
				$object['send_user'] = '';
			} else {
				$object['send_user'] = $login_info['username'];
			}
			$object['addtime'] = date('Y-m-d H:i:s');
			$object['ip'] = $_SERVER['REMOTE_ADDR'] == '' ? $_SERVER["REMOTE_HOST"] : $_SERVER['REMOTE_ADDR'];
			try{
				$ly_id = $this->lyb->add_ly($object);
				echo($ly_id);
			}catch(Exception $e){
				echo($e->getMessage());
			}
			
			
		}
		
		function index($username){
			// 初始化用户基本信息
			$this->_init($username);
			// 加载对应的模版控制器
			$this->_load_template_manager();
			// 工作转交给对应的模版控制器类
			$this->template_manager->index();
		}
		
		function art_list($username, $page=1, $cat_id=0){
			// 初始化用户基本信息
			$this->_init($username);
			// 加载对应的模版控制器
			$this->_load_template_manager();
			// 工作转交给对应的模版控制器类
			$this->template_manager->art_list($page, $cat_id);
		}
		
		function art_view($username, $id){
			$this->_init($username);
			$this->_load_template_manager();
			$this->template_manager->art_view($username, $id);
		}
		
		function case_list($username, $page = 1){
			$this->_init($username);
			$this->_load_template_manager();
			$this->template_manager->case_list($page);
		}
		
		function case_view($username, $id){
			$this->_init($username);
			$this->_load_template_manager();
			$this->template_manager->case_view($username, $id);
		}
		
		function intro($username){
			
			$this->_init($username);
			$this->_load_template_manager();
			
			$this->template_manager->intro($username);
			
		}
		
		// 装修预约
		function contact($username){
			
			$this->_init($username);
			$this->_load_template_manager();
			$this->template_manager->contact($username);
			
		}
		
		function contact_submit($username){
			$this->_init($username);
			$this->_load_template_manager();
			$this->template_manager->contact_submit($username);
		}
		
		function comment_page($username){
			
			$this->_init($username);
			$this->_load_template_manager();
			$this->template_manager->comment($username);
			
			
		}
		
		// 评论的提交
		function comment($username){
			
			$this->load->library('encode');
			$this->load->model('LoginModel', 'login');
			$this->load->model('sjs/sjs_comment', 'sjs_comment');
			
			$object = array();
			$object['id'] = $this->encode->getFormEncode('id');
			$object['blog_user'] = $this->encode->getFormEncode('blog_user');
			$object['type'] = $this->encode->getFormEncode('type');
			$object['content'] = $this->encode->getFormEncode('content');
			$object['addtime'] = date('Y-m-d H:i:s');
			$object['ip'] = $_SERVER['REMOTE_ADDR'] == '' ? $_SERVER["REMOTE_HOST"] : $_SERVER['REMOTE_ADDR'];
			if( $object['content'] == '' || $object['type'] == '' || $object['id'] == '' ){
				echo("{status:0, error:'参数错误'}");
				return;
			}
			$login_info = $this->login->check_login();
			if( $login_info == false ){
				$object['send_user'] = '';
			} else {
				$object['send_user'] = $login_info['username'];
			}
			
			try{
				$insertId = $this->sjs_comment->add($object);
				// 返回评论信息
				/*
				$comments = $this->sjs_comment->get_comment_list("select * from sjs_comment where id = '". $insertId ."' order by addtime desc", true);
				foreach($comments as $key=>$val){
					if( !empty( $val['user_inf']['face_image'] ) ){
						$url_cfg = $this->config->item('upload_image_options');
						$comments[$key]['user_inf']['face_image'] = $url_cfg[0]['url'] . str_replace('\\', '/', $comments[$key]['user_inf']['face_image']);
					} else {
						$comments[$key]['user_inf']['face_image'] = '/resources/member_design/blog_skin/default/images/lyb_face.png';
					}
				}*/
				echo("{status:1}");
				//echo("{status:1, face:'". $comments[0]['user_inf']['face_image'] ."', send_user:'"
				//. $comments[0]['user_inf']['username'] ."', true_name:'". $comments[0]['user_inf']['true_name'] 
				//."', content:'". $comments[0]['detail'] ."', date:'". $comments[0]['addtime'] ."'}");
			}catch(Exception $e){
				echo("{status:0, error:'". $e->getMessage() ."'}");
			}
			
		}
		
		// 最近访客
		function recvisit($username){
			$this->load->library('encode');
			$object = array();
			$object['visit_user'] = $this->encode->getFormEncode('visit_user');
			$object['blog_user'] = $this->encode->getFormEncode('blog_user');
			$object['visit_time'] = date('Y-m-d H:i:s');
			$this->load->model('sjs/recvisit', 'recvisit');
			try{
				$this->recvisit->update($object);
				echo("{error:0}");
			}catch(Exception $e){
				echo("{error:1, err_info:'". $e->getMessage() ."'}");
			}
		}
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
