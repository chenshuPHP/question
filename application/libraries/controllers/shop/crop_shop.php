<?php if( !defined('BASEPATH') ) exit('prohibition of direct view; shop/crop_shop.controller.php');

// 装修公司店铺
// 2015-02-27
class crop_shop extends MY_Controller {
	
	var $username = '';
	var $info = NULL;
	
	public function __construct(){
		parent::__construct();
	}


	public function _remap($method, $params){
		
		$original_args = array($method, $params);
		
		$this->username = $method;
		
		if( count($params) == 0 ){
			$method = 'index';
		} else {
			$uri = strtolower(array_shift($params));
			// 案例 不使用单词 case 作为方法名，因为 case 为保留字
			$method = $uri;
			if(strtolower($uri) == 'case'){
				$method = 'cases';
			}
			// 案例详细
			if(preg_match('/^c\d+(\-\d+)*$/', $uri)){
				$params = str_replace('c', '', $uri);
				$params = explode('-', $params);
				$method = 'case_view';
			}
			// 案例图片展示
			if( preg_match('/^civ(\-\d+){2}$/', $uri) ){
				$params = str_replace('civ', '', $uri);
				$params = explode('-', $params);
				array_shift($params);
				$method = 'case_image_view';
			}
			// 资讯详细
			if( preg_match('/^n\d+(\-\d+)*$/', $uri) ){
				$params = str_replace('n', '', $uri);
				$params = explode('-', $params);
				$method = 'news_show';
			}
			//证书详细
			if( preg_match('/^zz\d+$/', $uri) ){
				$params = str_replace('zz','',$uri);
				$params = explode('-', $params);
				$method = 'cert_show';
			}
			// 服务团队
			if( preg_match('/^team(\d+(\-\d)?)?$/', $uri) ){
				$params = str_replace('team','',$uri);
				$params = explode('-', $params);
				if( count($params) == 1 && empty($params[0]) ){
					$params = array(0, 0);
				} else if(count($params) == 1 && !empty($params[0])){
					$params = array($params[0], 0);
				}
				$method = 'team';
			}
			
			// 店铺的评论提交
			if( preg_match('/^sp_comment_submit$/', $uri) ){
				$method = 'sp_comment_submit';
			}
			
		}
		
		
		// 2015-05-12
		if( method_exists($this, $method) ){
			$this->$method($params);
		} else {
			
			//echo( dirname(__FILE__) );
			
			$dir = rtrim(dirname(__FILE__), '\\') . '\\';
			
			if( ! file_exists($dir . 'sp\\sp_base.php') || ! file_exists($dir . 'sp\\sp_controller.php') ){
				show_error('404 file not found', 404);
			}
			
			include('sp/sp_controller.php');
			$class = new sp_controller();
			$class->controller($original_args);
			
		}
		
	}

	private function _common(){
		
		$this->load->model('company/Company', 'company_model');
		
		
		$this->load->library('thumb');
		$object = $this->company_model->getCompany($this->username);
		
		if( $object['delcode'] == 1 ) show_error('店铺已经被删除', 404);
		
		
		if( !$object ){ show_404(); }
		if( empty($object['logo']) ){
			$object['logo_url'] = '';
		} else {
			$this->thumb->setPathType(1);
			$object['logo_url'] = $this->thumb->resize($object['logo'], 245, 160);
			$this->thumb->setPathType(0);
		}
		if( !empty($object['company_pic']) ){
			$object['company_pic_url'] = $this->thumb->crop($object['company_pic'], 320, 215);
		} else {
			$object['company_pic_url'] = 'http://www.shzh.net/resources/shop/laconic/images/t/3.jpg';
		}
		
		// 2015-06-24
		// 屏蔽电话号码
		// 模版中已经设置 普通会员不显示手机号码了，所以这里不需要替换手机号码
		$object['tel_original'] = $object['tel'];
		
		if( $object['flag'] != 2 ){
			
			$check_update_limit = true;
			if( empty( $object['update_time'] ) ){
				$check_update_limit = false;
			} else {
				$update_time_limit = floor( ( strtotime( date('Y-m-d H:i:s') ) - strtotime($object['update_time']) ) / 86400 );
				if( $update_time_limit > 30 ) $check_update_limit = false;
			}
			
			if( $check_update_limit == false ){
				$tel = $this->config->item('tel');
				$object['tel'] = $tel['400'];	// 将固定电话设置为本站的 400
				$object['mobile'] = '';
			}
			
		}
		
		$this->info = $object;
		
		
	}
	
	private function _common_assign(){
		$this->tpl->assign('object', $this->info);
		// seo infomation
		$object = $this->info;
		$this->load->library('encode');
		$description = $this->encode->get_text_description($object['content'], 100, false);
		$this->tpl->assign('title', $object['company'] . '-上海装潢网');
		$this->tpl->assign('keywords', $object['company'] . ',装修');
		$this->tpl->assign('description', $description);
	}
	
	public function index( $params )
	{
		$this->_remap( $this->username, array('home-home') );
	}
	
	
	// 公司简介页面
	public function intro($params){
		
		/*
		$this->_common();
		$this->_common_assign();
		$this->tpl->assign('module', 'intro');
		$this->tpl->display('crop_page/laconic/intro.html');
		*/
		
		$this->_remap($this->username, array('intro-home'));
		
	}
	
	// 企业案例页面
	public function cases($params){
		/*
		$this->_common();
		$this->_common_assign();
		$this->load->model('company/usercase', 'usercase_model');
		$list = $this->usercase_model->getUserCases($this->username, array(
			'where'=>"(edate < '". date('Y-m-d') ."' or edate is null)"
		));
		foreach($list as $key=>$val){
			$val['thumb'] = $this->thumb->crop($val['fm'], 156, 118);
			$list[$key] = $val;
		}
		// var_dump2( $list );
		$this->tpl->assign('list', $list);
		$this->tpl->assign('module', 'case');
		$this->tpl->display('crop_page/laconic/case.html');
		*/
		// 2016-08-23 启用新的处理程序
		$this->_remap($this->username, array('project-cases'));
	}
	
	// 案例详细显示页面
	public function case_view($params){
		
		// 2015-05-15 启用新的处理程序
		$this->_remap($this->username, array('project-detail-' . $params[0]));
		
		/*
		
		exit();
		
		$this->_common();
		$this->_common_assign();
		$this->load->model('company/usercase', 'case_model');
		$case_id = $params[0];
		if( count($params) == 1 ){
			$image_id = 0;
		} else {
			$image_id = $params[1];
		}
		
		$case = $this->case_model->getCase($case_id);
		$images = $this->case_model->getImages($case_id, $this->username);		// w 210 h 130
		foreach($images as $key=>$value){
			$value['thumb'] = $this->thumb->crop($value['imgpath'], 230, 130);
			$images[$key] = $value;
		}
		$this->tpl->assign('case', $case);
		$this->tpl->assign('images', $images);
		
		$this->tpl->assign('module', 'case');
		$this->tpl->display('crop_page/laconic/case_view.html');
		*/
	}
	// 案例图片展示
	public function case_image_view($params){
		
		//$case_id = $params[0];
		$image_id = $params[1];
		// 2015-05-15 启用新的处理程序
		$this->_remap($this->username, array('project-image-' . $image_id));
		/*
		if( empty($case_id) || empty($image_id) ){
			show_404();
		}
		$this->_common();
		$this->_common_assign();
		$this->load->model('company/usercase', 'case_model');
		$case = $this->case_model->getCase($case_id);
		$images = $this->case_model->getImages($case_id, $this->username);		// w 105 h 55
		foreach($images as $key=>$value){
			$value['thumb'] = $this->thumb->crop($value['imgpath'], 105, 55);
			$images[$key] = $value;
		}
		$this->tpl->assign('case', $case);
		$this->tpl->assign('images', $images);
		$this->tpl->assign('image_id', $image_id);
		
		$this->tpl->assign('module', 'case');
		$this->tpl->display('crop_page/laconic/case_image_view.html');
		*/
	}
	
	// 公司动态 
	public function news($params){
		
		
		/*
		$this->_common();
		$this->_common_assign();
		$this->load->model('company/usernews', 'news_model');
		$list = $this->news_model->get_list(array(
				'username'=>$this->username,
				'fields'=>array('id','title','addtime','username')
		));
		$this->tpl->assign('list', $list);
		
		$this->tpl->assign('module', 'news');
		$this->tpl->display('crop_page/laconic/news.html');
		*/
		$this->_remap($this->username, array('article-home'));
		
		
		
	}
	
	// 公司动态详细显示
	public function news_show($params){
		
		$article_id = $params[0];
		$this->_remap($this->username, array('article-detail-' . $article_id));
		
		/*
		$id = $params[0];
		$this->_common();
		$this->_common_assign();
		$this->load->model('company/usernews', 'news_model');
		$new_object = $this->news_model->get_new($id);
		$this->load->library('encode');
		$new_object['detail'] = $this->encode->htmldecode($new_object['detail']);
		$this->tpl->assign('new_object', $new_object);
		
		$this->tpl->assign('module', 'news');
		$this->tpl->display('crop_page/laconic/news_show.html');
		*/
		
	}
	
	
	// 人才招聘
	public function hr($params){
		
	 /*
		$this->_common();
		$this->_common_assign();
		$this->load->model('company/hr', 'hr_model');
		$list = $this->hr_model->getHrs($this->username);
		$this->tpl->assign('list', $list);
		$this->tpl->assign('module', 'hr');
		$this->tpl->display('crop_page/laconic/hr.html');
		
		*/
		$this->_remap($this->username, array('hr-home'));
		
	
	}
	
	// 资质证书
	public function cert($params){
		
		/*
		$this->_common();
		$this->_common_assign();
		$this->load->model('company/zizhi', 'zizhi_model');
		$list = $this->zizhi_model->get_list(array(
			'username'=>$this->username
		));
		foreach($list as $key=>$val){
			$val['thumb'] = $this->thumb->crop($val['imgpath'], 156, 118);
			$list[$key] = $val;
		}
		$this->tpl->assign('list', $list);
		
		//var_dump( $list );
		$this->tpl->assign('module', 'cert');
		$this->tpl->display('crop_page/laconic/cert.html');
		*/
		
		
		$this->_remap($this->username, array('cert-home'));
	}
	// 资质证书详细
	public function cert_show($params){
		$id = $params[0];
		$this->_common();
		$this->_common_assign();
		$this->load->model('company/zizhi', 'zizhi_model');
		$certificate = $this->zizhi_model->get_zizhi($id);
		//var_dump($certificate);
		$this->tpl->assign('certificate', $certificate);
		
		$this->tpl->assign('module', 'cert');
		$this->tpl->display('crop_page/laconic/cert_show.html');
	}
	
	// 联系我们
	public function contact($params){
		
		/*
		$this->_common();
		$this->_common_assign();
		
		$logo = $this->info['logo'];
		$thumb = '';
		if( !empty($logo) ){
			$this->load->library('thumb');
			$this->thumb->setPathType(1);
			$thumb = $this->thumb->resize($logo, 60, 60);
		}
		$this->tpl->assign('guest_reply_thumb', $thumb);
		$this->load->model('company/comchild', 'comchild_model');
		$list = $this->comchild_model->getCom_child($this->username);
		$this->tpl->assign('contact_list', $list);
		
		$this->tpl->assign('module', 'contact');
		$this->tpl->display('crop_page/laconic/contact.html');
		*/
		
		$this->_remap($this->username, array('contact-home'));
	}
	
	
	
	// 服务团队
	/*
	public function team($params){
		$this->_common();
		$this->_common_assign();
		$this->load->model('company/designer', 'team_model');
		$list = $this->team_model->getDesigners($this->username, array('noface'=>true));
		
		foreach($list as $key=>$val){
			if( !empty($val['imgpath']) ){
				$val['thumb'] = $this->thumb->crop($val['imgpath'], 125, 145);
			} else {
				$val['thumb'] = '';
			}
			$list[$key] = $val;
		}
		$this->tpl->assign('list', $list);
		$this->tpl->display('crop_page/laconic/team.html');
	}
	*/
	
	// 装修公司预约
	public function reserve($params){
		$this->_common();
		$this->_common_assign();
		
		if( count($params) == 0 ){
			// 预约的基本形式，店铺中的预约
			$this->tpl->display('crop_page/laconic/reserve.html');
		} else {
			// 预约的其他形式
			// 装修公司频道列表页预约
			$type = strtolower($params[0]);
			if( $type != 'design' && $type != 'baojia' ){
				show_404();
			} else {
				$this->tpl->assign('type', $type);
				
				$this->load->model('publish/reserve', 'reserve_model');
				$reserve_count = $this->reserve_model->get_reserve_count();
				$this->tpl->assign('reserve_count', $reserve_count);
				$this->tpl->display('crop_page/laconic/ifr_send_design.html');
			}
		}
		
	}
	
	// 装修公司内部预约提交
	public function reserve_submit($params){
		$this->load->library('encode');
		$object = array();
		$object['true_name'] = $this->encode->getFormEncode('true_name');
		$object['tel'] = $this->encode->getFormEncode('tel');
		$object['area'] = $this->encode->getFormEncode('area');
		$object['budget'] = $this->encode->getFormEncode('budget');
		$object['shen'] = $this->encode->getFormEncode('provinceID');
		$object['city'] = $this->encode->getFormEncode('cityID');
		$object['address'] = $this->encode->getFormEncode('address');
		$object['content'] = $this->encode->getFormEncode('content');
		$object['username'] = $this->encode->getFormEncode('username');
		$object['ip'] = $_SERVER['REMOTE_ADDR'];
		$object['addtime'] = date('Y-m-d H:i:s');
		$object['validate'] = strtolower( $this->gf('validate') );

		if( ! $this->reserve_validate_check($object['validate']) ){
			$this->alert('验证码错误');
			exit();
		}

		// 2015-03-11 空白装修信息提交成功的限制
		if( empty( $object['true_name'] ) || empty( $object['tel'] ) ){
			$this->alert('信息不完整，称呼和电话必须填写');
			exit();
		}

		$this->load->model('publish/reserve', 'reserve_model');
		
		try{
			$this->reserve_model->add($object);
			echo('<script type="text/javascript">alert("预约成功，等待客服联系。");parent.close_reserve();</script>');
		}catch(Exception $e){
			echo($e->getMessage());
		}
		
	}
	
	// 装修公司列表等其他地方的预约装修, 多样化的预约装修表单处理程序
	public function reserve_multi_submit(){
		$this->load->library('encode');
		$object = array();
		$object['true_name'] = $this->encode->getFormEncode('true_name');
		$object['tel'] = $this->encode->getFormEncode('tel');
		$object['deco_type'] = $this->encode->getFormEncode('deco_type');
		$object['deco_home_type'] = $this->encode->getFormEncode('deco_home_type');
		$object['deco_bao'] = $this->encode->getFormEncode('deco_bao');
		$object['province_id'] = $this->encode->getFormEncode('provinceID');
		$object['city_id'] = $this->encode->getFormEncode('cityID');
		$object['ip'] = $_SERVER['REMOTE_ADDR'];
		$object['addtime'] = date('Y-m-d H:i:s');
		$object['username'] = $this->encode->getFormEncode('username');

		$this->load->model('publish/reserve', 'reserve_model');

		// 2015-03-11 空白装修信息提交成功的限制
		if( empty( $object['true_name'] ) || empty( $object['tel'] ) ){
			$this->alert('信息不完整，称呼和电话必须填写');
			exit();
		}

		try{
			$this->reserve_model->add_multi($object);
			echo('<script type="text/javascript">location.href="/shop/'. $object['username'] .'/reserve_multi_submit_success";</script>');
		}catch(Exception $e){
			echo($e->getMessage());
		}
	}
	
	// 装修公司预约成功反馈页面
	public function reserve_multi_submit_success(){
		$this->tpl->display('crop_page/laconic/ifr_send_design_success.html');
	}
		
	// ================================================
	// 添加预约图片验证获取 2015-02-27
	public function get_reserve_validate_image(){
		session_start();
		$this->load->library('kaocode');
		$this->kaocode->doimg();
		$_SESSION['reserve_validate_code'] = $this->kaocode->getCode();
	}
	// 验证码验证
	public function reserve_validate_check($code = ''){
		
		if( empty( $code ) ){
			$validate = $this->gf('validate');
			$client = true;
		} else {
			$validate = $code;
			$client = false;
		}


		$result = false;

		session_start();
		$server_validate = strtolower( $_SESSION['reserve_validate_code'] );
		$client_validate = strtolower( $validate );

		if( $server_validate == $client_validate ){
			$result = true;
		}

		if( $client == true ){
			echo( $result == true ? 1 : 0 );
		}

		return $result;
		
	}
	// ===============================================
	
	
	
	// 留言提交
	public function comment_submit($params){
		$this->load->model('company/guest', 'guest');
		$res = $this->guest->add($this->username);
		if( $res === true ){
			$this->tpl->assign('post', 'ok');
		} else {
			$this->tpl->assign('post', 'no');
			$this->tpl->assign('errs', $res);
			
			var_dump2($res);
			
			$this->tpl->assign('object', $_POST);
		}
		$this->contact($params);
	}

	// 验证码
	public function validate_code($params){
		session_start();
		$this->load->library('kaocode');
		$this->kaocode->doimg();
		$_SESSION['shop_guest_validate'] = $this->kaocode->getCode();
	}
	
	private function _check_validate_code($code = '')
	{
		if( ! isset( $_SESSION ) ) session_start();
		if( ! isset( $_SESSION['shop_guest_validate'] ) ) return false;
		if( empty( $_SESSION['shop_guest_validate'] ) ) return false;
		return strtolower( $code ) == strtolower( $_SESSION['shop_guest_validate'] );
	}
	
	public function check_validate_code()
	{
		$code = $this->gf('code');
		if( $this->_check_validate_code($code) === FALSE )
		{
			echo(0);
		}
		else
		{
			echo(1);
		}
	}
	
	//  ajax 留言板
	public function get_comments($params){
		$this->load->model('company/guest', 'company_guest_model');
		$list = $this->company_guest_model->getTenGuests($this->username);
		echo( json_encode($list) );
	}
	
	// 临时的 服务团队测试
	public function team($params){
		
		// 2015-05-21 使用新的处理程序
		$this->_remap($this->username, array('team-home-' . $params[0]));
		exit();
		
		
		/*
		$this->_common();
		$this->_common_assign();
		$job_id = $params[0];
		$page = $params[1];
		$this->load->model('company/userteam', 'userteam_model');
		// 读取岗位信息
		$job_list = $this->userteam_model->get_all_jobs($this->username);
		if( $job_id == 0 ){
			$job_id = $this->userteam_model->get_first_job_id($this->username);
		}
		$this->tpl->assign('job_id', $job_id);
		$this->tpl->assign('job_list', $job_list);
		// 读取员工信息
		$members = $this->userteam_model->get_members(array('username'=>$this->username, 'job_id'=>$job_id));
		
		$default_face_images = array(
			array('label'=>'设计师', 'src'=>$this->config->item('resources_url') . '/shop/laconic/images/shejishi.jpg'),
			array('label'=>'项目经理', 'src'=>$this->config->item('resources_url') . '/shop/laconic/images/xiangmujingli.jpg'),
			array('label'=>'水电工', 'src'=>$this->config->item('resources_url') . '/shop/laconic/images/diangong.jpg'),
			array('label'=>'木工', 'src'=>$this->config->item('resources_url') . '/shop/laconic/images/mugong.jpg'),
			array('label'=>'泥水工', 'src'=>$this->config->item('resources_url') . '/shop/laconic/images/nishuigong.jpg'),
			array('label'=>'油漆工', 'src'=>$this->config->item('resources_url') . '/shop/laconic/images/youqigong.jpg'),
			array('label'=>'outher', 'src'=>$this->config->item('resources_url') . '/shop/laconic/images/shejishi.jpg')
		);
		
		// 缩略图管理 w:140 h:174
		$this->load->library('thumb');
		foreach($members as $key=>$value){
			if( !empty($value['face_image']) ){
				$value['thumb'] = $this->thumb->crop($value['face_image'], 140, 174);
			} else {
				$custom_job_check = true;
				foreach($default_face_images as $k=>$v){
					if( $v['label'] == $value['job_name'] ){
						$value['thumb'] = $v['src'];
						$custom_job_check = false;
					}
				}
				if( $custom_job_check ){
					$value['thumb'] = $default_face_images[count($default_face_images)-1]['src'];
				}
			}
			$members[$key] = $value;
		}
		
		$this->tpl->assign('members', $members);
		
		$this->tpl->assign('module', 'team');
		$this->tpl->display('crop_page/laconic/team_latest.html');
		*/
	}
	
	
	// 店铺评论提交
	private function sp_comment_submit($params = array()){
		
		
		// 2016-09-30 改版为新的处理程序
		$this->_remap($this->username, array(
			'forum-handler'
		));
		exit();
		
		
		$this->load->library('encode');
		$object = array();
		$object['comment'] = $this->encode->getFormEncode('comment');
		$object['sp_username'] = $this->username;
		$object['comment_username'] = $this->encode->getFormEncode('comment_username');
		$object['send_time'] = date('Y-m-d H:i:s');
		$object['send_ip'] = $_SERVER['REMOTE_ADDR'];
		
		$this->load->model('company/guest', 'guest_model');
		
		// 添加店铺留言
		$result = $this->guest_model->add_sp_comment($object);
		if( $result === true ){
			echo('1');
		} else {
			echo('0');
		}
	}
	
	// 加载评论 ajax
	private function get_sp_comments($params = array()){
		
		var_dump($params);
		
		/*
		$this->load->library('encode');
		$config = array('page'=>$this->encode->getFormEncode('page'), 'size'=>10);
		$this->load->model('company/guest', 'guest_model');
		$sql = "select top ". $config['size'] ." comment, ip, addtime from user_shop_comments where sp_username = '". $this->username 
		."' and id not in (select top ". ($config['page']-1)*$config['size'] ." id from user_shop_comments where sp_username = '". $this->username ."' order by id desc) order by id desc";
		$list = $this->guest_model->get_comments($sql);
		*/
	}
	
}
?>