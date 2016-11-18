<?php

	class Publish extends MY_Controller {
		function __construct(){
			parent::__construct();
			$this->load->library('encode');
		}
		
		
		public function _remap($class, $params = array()){
			
			// 兼容旧文件中的代码
			if( method_exists($this, strtolower($class)) ){
				if( count($params) > 0 ){
					$this->$class($params[0]);
				} else {
					$this->$class();
				}
			} else {
				
				// 使用外部处理文件中的代码
				$prefix = 'publish_';
				$directory = rtrim(dirname(__FILE__), '\\') . '\\';
				$base_class = $prefix . 'base';
				$class_name = $prefix . strtolower($class);
				
				if( ! file_exists( $directory . $base_class . '.php' ) ){
					show_error('base class file is not found', 404, 'include error.');
				} else {
					include($directory . $base_class . '.php');
				}
				
				if( ! file_exists($directory . $class_name . '.php') ){
					show_error('class file is not found', 404, 'include error.');
				} else {
					include($directory . $class_name . '.php');
				}
				
				$object = new $class_name();
				$method_name = 'home';
				if( count($params) > 0 ){
					$method_name = strtolower( array_shift($params) );
					if( count($params) > 0 ){
						$object->$method_name($params);
					} else {
						$object->$method_name();
					}
				} else {
					$object->$method_name();
				}
				exit();
			}
		}
		
		
		// 输出验证码
		public function validate(){
			session_start();
			$this->load->library('kaocode');
			$this->kaocode->doimg();
			$_SESSION['publish_validate_code'] = $this->kaocode->getCode();
		}
		
		public function check_validate_ajax(){
			session_start();
			$this->load->library('encode');
			$post_validate_code = $this->encode->getFormEncode('validate_code');
			$validate_code = $_SESSION['publish_validate_code'];
			
			//exit($validate_code . ';' . $post_validate_code);
			
			if( strtolower($validate_code) != strtolower($post_validate_code) ){
				echo('0');
			} else {
				echo('1');
			}
		}
		
		private function _check_validate(){
			session_start();
			
			$post_validate_code = $this->encode->getFormEncode('validate_code');
			
			if( isset( $_SESSION['publish_validate_code'] ) ){
				$validate_code = $_SESSION['publish_validate_code'];
			} else {
				$validate_code = '-----';
			}
			
			
			if( strtolower($post_validate_code) != strtolower( $validate_code ) ){
				exit('验证码错误。请返回重新提交');
			}
		}
		
		// 工装招标
		function zb(){
			$this->tpl->display('publish/zhaobiao.html');
		}
		// 工装招标表单处理
		function zb_submit(){
			
			$this->_check_validate();
			
			$project = array();
			$project['name'] = $this->encode->getFormEncode('project');
			$project['budget'] = $this->encode->getFormEncode('budget');
			$project['shen'] = $this->encode->getFormEncode('User_Shen');
			$project['city'] = $this->encode->getFormEncode('User_City');
			$project['town'] = $this->encode->getFormEncode('User_Town');
			$project['category_b'] = $this->encode->getFormEncode('category_b');
			$project['category_s'] = $this->encode->getFormEncode('category_s');
			$project['bao'] = $this->encode->getFormEncode('bao');
			$project['detail'] = $this->encode->getFormEncode('detail');
			$project['rejion'] = $this->encode->getFormEncode('rejion');
			$project['tel'] = $this->encode->getFormEncode('tel');
			
			if( $project['name'] == '' || $project['budget'] == '' || $project['rejion'] == '' || $project['tel'] == '' ){
				echo('您填写的数据不完整');
				return false;
			}
			
			$this->load->model('publish/PubModel', 'pub');
			$insertId = $this->pub->zhaobiao_add( $project );
			
			echo('<script type="text/javascript">alert("您的信息已经提交成功，我们将在两个工作日内与您联系");location.href="/post/zb_list.html";</script>');
			
		}
		// 招标列表
		function zb_list($page=1){
			$settings = array('page'=>$page, 'size'=>20);
			$this->load->model('publish/PubModel', 'pub');
			$res = $this->pub->getZhaobiao_list( $settings );
			$this->tpl->assign('list', $res['list']);
			$this->load->library('pagination');
			$this->pagination->baseUrl = '/post/zb_list.html';
			$this->pagination->currentPage = $page;
			$pageCount = $this->pagination->getPageCount($settings['size'], $res['count']);
			$this->pagination->pageCount = $pageCount;
			$this->pagination->delimiter = '_';
			$pagination = $this->pagination->toString(true);
			$this->tpl->assign('pagination', $pagination);
			$this->tpl->display('publish/zb_list.html');
		}
		
		function zb_view($id){
			$this->load->model('publish/PubModel', 'pub');
			$object = $this->pub->getZhaobiaoView($id);
			if( !$object ){ show_404(); }
			$this->tpl->assign('object', $object);
			$this->tpl->display('publish/zb_view.html');
		}
		
		
		// 监理咨询界面
		function jianli(){
			$this->tpl->display('publish/jianli.html');
		}
		// 监理表单处理
		function jianli_submit(){
			
			$this->_check_validate();
			
			$project = array();
			$project['budget'] = $this->encode->getFormEncode('budget');
			$project['shen'] = $this->encode->getFormEncode('User_Shen');
			$project['city'] = $this->encode->getFormEncode('User_City');
			$project['town'] = $this->encode->getFormEncode('User_Town');
			$project['category_b'] = $this->encode->getFormEncode('category_b');
			$project['category_s'] = $this->encode->getFormEncode('category_s');
			$project['bao'] = $this->encode->getFormEncode('bao');
			$project['detail'] = $this->encode->getFormEncode('detail');
			$project['rejion'] = $this->encode->getFormEncode('rejion');
			$project['tel'] = $this->encode->getFormEncode('tel');
			$project['segment'] = isset($_POST['segment']) ? implode(',', $_POST['segment']) : '';
			if( $project['budget'] == '' || $project['rejion'] == '' || $project['tel'] == '' ){
				echo('您填写的数据不完整');
				return false;
			}
			$this->load->model('publish/PubModel', 'pub');
			$insertId = $this->pub->jianli_add( $project );
			echo('<script type="text/javascript">alert("您的信息已经提交成功，我们将在两个工作日内与您联系");location.href="/post/jianli.html";</script>');
		}
		function jianli_list($page=1){
			$settings = array('page'=>$page, 'size'=>20);
			$this->load->model('publish/PubModel', 'pub');
			$res = $this->pub->getJianli_list( $settings );
			$this->tpl->assign('list', $res['list']);
			
			$this->load->library('pagination');
			$this->pagination->baseUrl = '/post/jianli_list.html';
			$this->pagination->currentPage = $page;
			$pageCount = $this->pagination->getPageCount($settings['size'], $res['count']);
			$this->pagination->pageCount = $pageCount;
			$this->pagination->delimiter = '_';
			$pagination = $this->pagination->toString(true);
			$this->tpl->assign('pagination', $pagination);
			$this->tpl->display('publish/jianli_list.html');
		}
		function jianli_view($id){
			$this->load->model('publish/PubModel', 'pub');
			$object = $this->pub->getJianliView($id);
			if( !$object ){ show_404(); }
			$this->tpl->assign('object', $object);
			$this->tpl->display('publish/jianli_view.html');
		}
		
		// 大清仓界面
		function qingcang(){
			
			$this->tpl->display('publish/qingcang.html');
		}
		
		// 清仓表单处理
		function qingcang_submit(){
			
			$this->_check_validate();
			
			$project = array();
			$project['shen'] = $this->encode->getFormEncode('User_Shen');
			$project['city'] = $this->encode->getFormEncode('User_City');
			$project['town'] = $this->encode->getFormEncode('User_Town');
			$project['title'] = $this->encode->getFormEncode('title');
			$project['detail'] = $this->encode->getFormEncode('detail');
			$project['rejion'] = $this->encode->getFormEncode('rejion');
			$project['tel'] = $this->encode->getFormEncode('tel');
			
			if( $project['title'] == '' || $project['detail'] == '' || $project['rejion'] == '' || $project['tel'] == '' ){
				echo('您填写的数据不完整');
				return false;
			}
			
			$this->load->model('publish/PubModel', 'pub');
			$insertId = $this->pub->qingcang_add( $project );
			echo('<script type="text/javascript">alert("您的信息已经提交成功，我们将在两个工作日内与您联系");location.href="/post/qingcang.html";</script>');
		}
		
		function qingcang_list($page = 1){
			$settings = array('page'=>$page, 'size'=>20);
			$this->load->model('publish/PubModel', 'pub');
			$res = $this->pub->getQingcang_list( $settings );
			$this->tpl->assign('list', $res['list']);
			$this->load->library('pagination');
			$this->pagination->baseUrl = '/post/qingcang_list.html';
			$this->pagination->currentPage = $page;
			$pageCount = $this->pagination->getPageCount($settings['size'], $res['count']);
			$this->pagination->pageCount = $pageCount;
			$this->pagination->delimiter = '_';
			$pagination = $this->pagination->toString(true);
			$this->tpl->assign('pagination', $pagination);
			$this->tpl->display('publish/qingcang_list.html');
		}
		
		function qingcang_view($id){
			$this->load->model('publish/PubModel', 'pub');
			$object = $this->pub->getQingcangView($id);
			if( !$object ){ show_404(); }
			$this->tpl->assign('object', $object);
			$this->tpl->display('publish/qingcang_view.html');
		}
		
		
		// 维修服务界面
		function weixiu(){
			$this->tpl->display('publish/weixiu.html');
		}
		
		function weixiu_submit(){
			
			$this->_check_validate();
			
			$project = array();
			
			$project['shen'] = $this->encode->getFormEncode('User_Shen');
			$project['city'] = $this->encode->getFormEncode('User_City');
			$project['town'] = $this->encode->getFormEncode('User_Town');
			$project['worker'] = $this->encode->getFormEncode('worker');
			$project['title'] = $this->encode->getFormEncode('project');
			$project['detail'] = $this->encode->getFormEncode('detail');
			$project['rejion'] = $this->encode->getFormEncode('rejion');
			$project['tel'] = $this->encode->getFormEncode('tel');
			$project['address'] = $this->encode->getFormEncode('address');
			
			$this->load->model('publish/PubModel', 'pub');
			$insertId = $this->pub->weixiu_add( $project );
			echo('<script type="text/javascript">alert("您的信息已经提交成功，我们将在两个工作日内与您联系");location.href="/post/weixiu.html";</script>');
			
		}
		
		function weixiu_list($page=1){
			
			$settings = array('page'=>$page, 'size'=>20);
			$this->load->model('publish/PubModel', 'pub');
			$res = $this->pub->getWeixiu_list( $settings );
			$this->tpl->assign('list', $res['list']);
			$this->load->library('pagination');
			$this->pagination->baseUrl = '/post/weixiu_list.html';
			$this->pagination->currentPage = $page;
			$pageCount = $this->pagination->getPageCount($settings['size'], $res['count']);
			$this->pagination->pageCount = $pageCount;
			$this->pagination->delimiter = '_';
			$pagination = $this->pagination->toString(true);
			$this->tpl->assign('pagination', $pagination);
			$this->tpl->display('publish/weixiu_list.html');
			
		}
		
		function weixiu_view($id){
			$this->load->model('publish/PubModel', 'pub');
			$object = $this->pub->getWeixiuView($id);
			if( !$object ){ show_404(); }
			$this->tpl->assign('object', $object);
			$this->tpl->display('publish/weixiu_view.html');
		}
		
		// 贷款表单界面
		function daikuan(){
			$this->tpl->display('publish/daikuan.html');
		}
		function daikuan_submit(){
			
			$this->_check_validate();
			
			$project = array();
			$project['shen'] = $this->encode->getFormEncode('User_Shen');
			$project['city'] = $this->encode->getFormEncode('User_City');
			$project['town'] = $this->encode->getFormEncode('User_Town');
			$project['title'] = $this->encode->getFormEncode('title');
			$project['total'] = $this->encode->getFormEncode('total');
			$project['limit'] = $this->encode->getFormEncode('limit');
			$project['detail'] = $this->encode->getFormEncode('detail');
			$project['rejion'] = $this->encode->getFormEncode('rejion');
			$project['tel'] = $this->encode->getFormEncode('tel');
			
			if( $project['title'] == '' || $project['total'] == '' || $project['rejion'] == '' || $project['tel'] == '' ){
				echo('您填写的数据不完整');
				return false;
			}
			
			$this->load->model('publish/PubModel','pub');
			$insertId = $this->pub->daikuan_add( $project );
			echo('<script type="text/javascript">alert("您的信息已经提交成功，我们将在两个工作日内与您联系");location.href="/post/daikuan.html";</script>');
			
		}
		// 贷款列表页
		function daikuan_list($page = 1){
			$settings = array('page'=>$page, 'size'=>20);
			$this->load->model('publish/PubModel', 'pub');
			$res = $this->pub->getDaikuan_list( $settings );
			$this->tpl->assign('list', $res['list']);
			$this->load->library('pagination');
			$this->pagination->baseUrl = '/post/daikuan_list.html';
			$this->pagination->currentPage = $page;
			$pageCount = $this->pagination->getPageCount($settings['size'], $res['count']);
			$this->pagination->pageCount = $pageCount;
			$this->pagination->delimiter = '_';
			$pagination = $this->pagination->toString(true);
			$this->tpl->assign('pagination', $pagination);
			$this->tpl->display('publish/daikuan_list.html');
		}
		function daikuan_view($id){
			$this->load->model('publish/PubModel', 'pub');
			$object = $this->pub->getDaikuanView($id);
			if( !$object ){ show_404(); }
			$this->tpl->assign('object', $object);
			$this->tpl->display('publish/daikuan_view.html');
		}
		
		// 2013-12-12 我要装修表单 戴一改动的版本
		public function zh(){
			
			$config = array(
				'size'			=> 7,
				'debug'			=> FALSE
			);
			
			$this->tpl->caching = true;
			$config['size'] 	= 25;
			$config['debug']	= TRUE;
			$tpl = 'publish/sendzh2015.html';
			
			$this->tpl->cache_lifetime = 60 * 30;			// 30 分钟
			$cache_dir = $this->tpl->cache_dir . 'post/';	// 设置这个模版的缓存目录
			$this->tpl->cache_dir = $cache_dir;
			
			
			if(! $this->tpl->isCached($tpl) ){
				
				// 加载部分订单
				$this->load->model('publish/deco_orders_model');
				
				$sql = "select top ". $config['size'] ." id, town, housearea, budget, fullname, shome, sroom, addtime, mobile ".
				"from sendzh where hide = 0 order by addtime desc";
				
				$orders = $this->deco_orders_model->get_deco_order_list( $sql );
				$orders = $orders['list'];
				
				// 处理格式
				foreach($orders as $key=>$value)
				{
					$value['tel'] = substr($value['mobile'], 0, 3) . '****' . substr($value['mobile'], -4);
					$value['date'] = date('m月d日', strtotime($value['addtime']));
					$orders[$key] = $value;
				}
				
				if( $config['debug'] == 1 )
				{
					var_dump2( $orders );
				}
				
				$this->tpl->assign('orders', $orders);
				
				// 加载图库内容
				$this->load->model('photo/photo', 'album_model');
				$this->load->model('photo/PhotoCategory', 'album_cat_model');
				$this->load->library('thumb');
				$hot_cats = $this->album_cat_model->getHotCategory(10);	// 获取热门分类
				$this->tpl->assign('hot_cats', $hot_cats);
				$latest_albums = $this->album_model->latestAlbum(6);
				
				// 不规则图片的尺寸
				$album_image_sizes = array(
					array(164, 161),
					array(164, 161),
					array(348, 173),
					array(264, 354),
					array(264, 354),
					array(264, 354)
				);
				// 对应图片的class
				$album_image_class = array('photo_one', 'photo_two', 'photo_three', 'photo_four', 'photo_five', 'photo_six');
				
				$i = 0;
				foreach($latest_albums as $key=>$value){
					// 生成对应尺寸的图片缩略图
					$latest_albums[$key]['thumb'] = $this->thumb->crop($value['fm'], $album_image_sizes[$i][0], $album_image_sizes[$i][1]);
					$i++;
				}
				$this->tpl->assign('albums', $latest_albums);
				$this->tpl->assign('album_image_sizes', $album_image_sizes);
				$this->tpl->assign('album_image_class', $album_image_class);

				// 装修公司城市数据
				$this->load->model('city_model');
				$city_childs = $this->city_model->get_childs(9968);	// 获取子城市
				//var_dump($city_childs);
				$this->tpl->assign('city_childs', $city_childs);
				
				// 预算 budget
				$this->load->model('budget/budget_model');
				$budgets = $this->budget_model->get_list("select top 4 id, name, image from budget order by addtime desc");
				$budgets = $budgets['list'];
				foreach($budgets as $key=>$val){
					if( !empty($val['image']) ){
						$budgets[$key]['thumb'] = $this->thumb->resize($val['image'], 208, 255);
					} else {
						$budgets[$key]['thumb'] = '/resources/publish/deco2images/baojia01.jpg';
					}
				}
				$this->tpl->assign('budgets', $budgets);

				$this->tpl->display($tpl);

			} else {
				$this->tpl->display($tpl);
				echo('<!-- cached -->');
			}
			
		}
		
		
		// 装修设计提交
		function zh_submit(){
			
			$this->_check_validate();
			
			$project = array();
			
			$project['shen'] = $this->encode->getFormEncode('User_Shen');
			$project['city'] = $this->encode->getFormEncode('User_City');
			$project['town'] = $this->encode->getFormEncode('User_Town');
			$project['address'] = $this->encode->getFormEncode('address');
			$project['category_b'] = $this->encode->getFormEncode('category_b');
			$project['category_s'] = $this->encode->getFormEncode('category_s');
			$project['area'] = $this->encode->getFormEncode('area');
			$project['budget'] = $this->encode->getFormEncode('budget');
			$project['bao'] = $this->encode->getFormEncode('bao');
			$project['zh_certif'] = $this->encode->getFormEncode('zh_certif');
			$project['sj_certif'] = $this->encode->getFormEncode('sj_certif');
			$project['detail'] = $this->encode->getFormEncode('detail');
			$project['name'] = $this->encode->getFormEncode('name');
			//$project['sex'] = $this->encode->getFormEncode('sex');
			$project['tel'] = $this->encode->getFormEncode('tel');
			
			
			// 2016-09-19 支持 ajax 提交
			$ajax = $this->gf('ajax');
			$errors = array();
			
			if( strlen($project['shen']) < 4 || strlen($project['city']) < 4 || strlen($project['town']) < 4 ){
				$errors[] = '请填写正确的省/市/县(区)';
			}
			
			if( $project['name'] == '' || $project['tel'] == '' ){
				$errors[] = '请填写称呼和电话';
			}

			$project['aid'] = $this->encode->getFormEncode('aid');		// 客服代表 的 用户名
			
			// 检测客服代表是否存在
			if( $project['aid'] != '' ){
				$this->load->model('manager/manager_model');
				$admin = $this->manager_model->get_manager($project['aid'], 'id');
				if( ! $admin ) $project['aid'] = '';
			}
			
			$id = 0;
			
			if( count( $errors ) == 0 )
			{
				$this->load->model('publish/PubModel', 'pub');
				try{
					
					$id = $this->pub->zhuanghuang_add( $project );
					// 短信发送
					/* 2016 - 04 - 25 已取消短信发送功能
					if( preg_match('/^1\d{10}$/', $project['tel']) == true && $project['aid'] == '' ){
						$this->load->model('sms_model');
						$this->load->library('send_message');
						$message = urlencode('【上海室内装饰行业协会】恭喜提交成功，协会家装顾问30分钟内会致电您，装修疑问免费解答 400-728-5580。');
						$config = array(
							'tid'				=> $id,
							'message'			=> $message,
							'category'			=> 'sendzh_welcome',
							'addtime'			=> date('Y-m-d H:i:s'),
							'mobile'			=> $project['tel'],
							'validate_code'		=> 0
						);
						// ==== 短信发送 ====
						$this->send_message->send($config['mobile'], $config['message']);
						// =================
						$this->sms_model->add($config);	// 记录发送的短信内容
					}
					*/
				}catch(Exception $e){
					$errors[] = $e->getMessage();
				}
			}
			
			// 支持 ajax 方式返回数据
			if( count( $errors ) == 0 )
			{
				if( $ajax == 1 )
				{
					echo( json_encode( array(
						'type'		=> 'success',
						'id'		=> $id
					) ) );
				}
				else
				{
					echo('<script type="text/javascript">alert("您的信息已经提交成功，我们将在两个工作日内与您联系");location.href="/post/zh_list.html";</script>');
				}
			}
			else
			{
				if( $ajax == 1 )
				{
					echo( json_encode( array(
						'type'		=> 'error',
						'message'	=> $errors
					) ) );
				}
				else
				{
					$this->alert( implode("\n", $errors) );
				}
			}
			
		}
		
		// 首页弹出预约内嵌界面
		public function deco_simple(){
			
			$this->load->model('publish/PubModel', 'pub');
			
			// 获取预约总和
			$count = $this->pub->get_deco_count();
			$this->tpl->assign('count', $count);
			$this->tpl->display('publish/deco_simple.html');
		}
		
		
		// 装修公司列表页面和其他页面的简单预约表单提交
		function zh_submit_simple(){
			$project = array();
			$project['shen'] = $this->encode->getFormEncode('User_Shen');
			$project['city'] = $this->encode->getFormEncode('User_City');
			$project['town'] = $this->encode->getFormEncode('User_Town');
			$project['area'] = $this->encode->getFormEncode('area');
			$project['budget'] = $this->encode->getFormEncode('budget');
			$project['detail'] = $this->encode->getFormEncode('detail');
			$project['name'] = $this->encode->getFormEncode('name');
			$project['tel'] = $this->encode->getFormEncode('tel');
			
			
			$project['address'] = '';
			$project['category_b'] = $this->encode->getFormEncode('category_b');
			$project['category_s'] = $this->encode->getFormEncode('category_s');
			$project['bao'] =  $this->encode->getFormEncode('bao');
			$project['zh_certif'] = '';
			$project['sj_certif'] = '';
			
			
			if( $project['name'] == '' || $project['tel'] == '' ){
				//var_dump($project);
				echo('您填写的数据不完整');
				return false;
			}
			
			$this->load->model('publish/PubModel', 'pub');
			$insertId = $this->pub->zhuanghuang_add( $project );
			echo('1');
		}
		
		
		// 装修信息列表
		function zh_list($page=1){
			
			$settings = array('page'=>$page, 'size'=>20);
			
			$this->load->model('publish/PubModel','pub');
			$res = $this->pub->getZhuanghuang_list( $settings );
			
			$this->tpl->assign('list', $res['list']);
			
			$this->load->library('pagination');
			$this->pagination->baseUrl = '/post/zh_list.html';
			$this->pagination->currentPage = $page;
			$pageCount = $this->pagination->getPageCount($settings['size'], $res['count']);
			$this->pagination->pageCount = $pageCount;
			$this->pagination->delimiter = '_';
			$pagination = $this->pagination->toString(true);
			
			$this->tpl->assign('pagination', $pagination);
			$this->tpl->assign('settings', $settings);
			$this->tpl->display('publish/zh_list.html');
			
		}
		
		// 装修信息详细
		function zh_view($id){
			$this->load->model('publish/PubModel','pub');
			$object = $this->pub->getZhuanghuang_view($id);
			if( $object == false ){
				show_404();
			}
			$object = $object[0];
			
			$this->tpl->assign('object', $object);
			$this->tpl->display('publish/zh_view.html');
		}
		
		
		// 快速预约通道
		function express_pipe(){
			
			$this->load->library('encode');
			$this->load->model('publish/PubModel', 'publish_model');
			$object = array();
			$object['tel'] = $this->encode->getFormEncode('tel');
			$object['true_name'] = $this->encode->getFormEncode('true_name');
			$object['address'] = $this->encode->getFormEncode('address');
			$object['email'] = $this->encode->getFormEncode('email');
			// rel exp: {"url":"http://www.shzh.net/zhuanti/xxx", "name":"xxx专题"}
			$object['rel'] = $this->encode->getFormEncode('rel');
			
			$object['shen'] = $this->encode->getFormEncode('sheng');
			$object['city'] = $this->encode->getFormEncode('city');
			$object['town'] = $this->encode->getFormEncode('town');
			
			
			/*
				ps exp: 
				[
					{"key:"风格选项", "value":"地中海风格"},
					{"key:"附加信息", "value":"xxxxx"}
				]
			*/ 
			$object['ps'] = $this->encode->getFormEncode('ps');
			
			if( $object['tel'] == '' || $object['true_name'] == '' ){
				exit('数据不完整，请检查号码是否正确');
			}
			
			$result = '';

			try{
				$this->publish_model->express_pipe_add($object);
				$result = '0';
			}catch(Exception $e){
				$result ='信息提交失败,请重试!';
			}
			
			echo($result);
			
		}
		
		// 接收来自于协会发过来的数据
		function sync_sida_sendzh(){
			// 处于安全考虑 这里少了密钥验证， 2013-11-27
			$this->load->library('encode');
			$project = array();
			$project['shen'] = $this->encode->getFormEncode('shen');
			$project['city'] = $this->encode->getFormEncode('city');
			$project['town'] = $this->encode->getFormEncode('town');
			$project['address'] = $this->encode->getFormEncode('address');
			$project['category_b'] = $this->encode->getFormEncode('home_type');
			$project['category_s'] = $this->encode->getFormEncode('home_type_child');
			$project['area'] = $this->encode->getFormEncode('area');
			$project['budget'] = $this->encode->getFormEncode('budget');
			$project['bao'] = $this->encode->getFormEncode('bao');
			$project['zh_certif'] = $this->encode->getFormEncode('zh_certif');
			$project['sj_certif'] = $this->encode->getFormEncode('sj_certif');
			$project['detail'] = $this->encode->getFormEncode('detail');
			$project['name'] = $this->encode->getFormEncode('true_name');
			$project['tel'] = $this->encode->getFormEncode('tel');
			$project['addtime'] = $this->encode->getFormEncode('addtime');
			
			$project['source'] = 'snzsxh';
			
			//phpinfo();
			//var_dump($project);
			$this->load->model('publish/PubModel', 'pub');
			$insertId = $this->pub->zhuanghuang_add( $project );
			echo('1');
			
		}
	}

?>