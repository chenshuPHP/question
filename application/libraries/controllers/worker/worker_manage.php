<?php if( !defined('BASEPATH') ) exit('禁止直接浏览');

class worker_manage extends MY_Controller {
	
	private $worker_id = 0;
	
	public function __construct(){
		parent::__construct();
		
		// 权限
		
		if( empty( $_COOKIE['worker_id'] ) || empty( $_COOKIE['worker_login'] ) ){
			show_404();
		} else {
			$this->worker_id = $_COOKIE['worker_id'];
			//$worker_login = $_COOKIE['worker_login'];
		}
		
		$this->tpl->assign('hide_kf', '1');	// 提高速度，隐藏客服
		
	}
	
	// 职工登录管理界面
	public function index(){
		
		$this->load->model('worker/worker_model');
		$this->load->model('worker/types_model');
		
		$types = $this->types_model->get_all();
		
		$object = $this->worker_model->get_single($this->worker_id);
		
		$this->tpl->assign('types', $types);
		$this->tpl->assign('object', $object);
		$this->tpl->display('worker/worker_manage_index.html');
	}	
	
	// 表单提交
	public function edit_info(){
		$this->load->library('encode');
		$object = array();
		$object['id'] = $this->worker_id;
		$object['form_id'] = $this->encode->getFormEncode('id');
		
		if( $object['id'] != $object['form_id'] ){
			exit('页面已经过期，请重新登录，原因：您当前登录的用户并非此用户');
		}
		
		$object['name'] = $this->encode->getFormEncode('name');
		$object['card_code'] = $this->encode->getFormEncode('card_code');
		$object['sex'] = $this->encode->getFormEncode('sex');
		$object['worker_type'] = $this->encode->getFormEncode('worker_type');
		$object['cmp_name'] = $this->encode->getFormEncode('cmp_name');
		$object['address'] = $this->encode->getFormEncode('address');
		$object['mobile'] = $this->encode->getFormEncode('mobile');
		$object['tel'] = $this->encode->getFormEncode('tel');
		$object['subsidy'] = $this->encode->getFormEncode('subsidy');
		$object['hold_post'] = $this->encode->getFormEncode('hold_post');
		$object['face'] = $this->encode->getFormEncode('current_face');
		$object['detail'] = $this->encode->getFormEncode('detail');
		$object['exper'] = $this->encode->getFormEncode('exper');
		$this->load->model('worker/worker_model');
		if( $this->worker_model->edit($object) ){
			echo('<script type="text/javascript">alert("修改成功");location.href="index";</script>');
		}
	}
	
	
	
}

?>