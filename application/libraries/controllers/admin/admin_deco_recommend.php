<?php if( !defined('BASEPATH') ) exit('~/controllers/admin/admin_deco_recommend.php');

// 装修会员 推荐功能整合类
	
class admin_deco_recommend extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 最新入驻会员推荐 界面
	public function join_recommend(){
		
		$this->load->library('encode');
		$page = $this->encode->get_request_encode('page');
		if( empty($page) ) $page = 1;
		$this->load->model('company/deco_recommend_model');
		
		// 获取后台管理的最新加入公司数据
		$sets = array('size'=>100, 'page'=>$page);
		$result = $this->deco_recommend_model->get_manages_join_recommend($sets);
		
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('sets', $sets);
		$this->tpl->assign('r', urlencode($_SERVER['REQUEST_URI']));
		$this->tpl->display('admin/deco_recommend/join_recommend.html');
		
	}
	
	// 最新入驻会员推荐 表单提交处理
	public function join_recommend_submit(){
		
		$object = array();
		$this->load->library('encode');
		
		$object['username'] = $this->encode->getFormEncode('username');
		$object['adduser'] = $this->admin_username;
		$object['addtime'] = date('Y-m-d H:i:s');
		
		$r = $this->encode->getFormEncode('r');
		
		if( empty($object['username']) || empty($object['adduser']) ){
			echo('<script type="text/javascript">alert("数据不完整");location.href="' . $r . '";</script>');
		} else {
			$this->load->model('company/deco_recommend_model');
			$result = $this->deco_recommend_model->join_recommend_add($object);
			if( $result === true ){
				echo('<script type="text/javascript">alert("加入成功");location.href="' . $r . '";</script>');
			} else {
				echo('<script type="text/javascript">alert("'. $result .'");location.href="' . $r . '";</script>');
			}
		}
		
	}
	
	// 移除推荐会员
	public function join_recommend_remove(){
		$this->load->library('encode');
		$username = $this->encode->get_request_encode('username');
		$r = $this->encode->get_request_encode('r');
		$this->load->model('company/deco_recommend_model');
		$this->deco_recommend_model->join_recommend_remove($username);
		echo('<script type="text/javascript">location.href="' . $r . '";</script>');
	}
	
}

?>