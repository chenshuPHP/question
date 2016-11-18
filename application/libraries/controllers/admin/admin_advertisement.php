<?php if( !defined('BASEPATH') ) exit('prohibition of direct view /admin/admin_advertisement.controller.php');
class admin_advertisement extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function adv_manage($params){
		$this->load->model('advertisement/advertisement', 'adv_model');
		$this->load->library('encode');
		$page = $this->encode->get_request_encode('page');
		if($page == '') $page = 1;
		$sets = array('size'=>20, 'page'=>$page, 'fields'=>'id, name, price, price, addtime, display');
		$result = $this->adv_model->get_list($sets);
		$list = $result['list'];
		$count = $result['count'];
		
		$this->load->library('pagination');
		$this->pagination->url_template_first = 'adv_manage';
		$this->pagination->url_template = 'adv_manage?page=<{page}>';
		$this->pagination->pageCount = $this->pagination->getPageCount($sets['size'], $count);
		$this->pagination->currentPage = $sets['page'];
		$pagination_content = $this->pagination->toString(true);
		
		$tpl = 'admin/advertisement/adv_manage.html';
		$this->tpl->assign('list', $list);
		$this->tpl->assign('pagination', $pagination_content);
		
		// 获取当前页面的完整URL
		$this->load->helper('url');
		$this->tpl->assign('current_url', get_full_url());
		
		$this->tpl->display($tpl);
	}
	
	// 添加表单
	public function adv_add(){
		$tpl = 'admin/advertisement/adv_add.html';
		$this->tpl->display($tpl);
	}
	
	// 广告位提交处理程序
	public function adv_add_submit(){
		$this->load->library('encode');
		$object = array();
		$object['adv_name'] = $this->encode->getFormEncode('adv_name');
		$object['price'] = $this->encode->getFormEncode('price');
		$object['adv_start_date'] = $this->encode->getFormEncode('adv_start_date');
		$object['adv_end_date'] = $this->encode->getFormEncode('adv_end_date');
		$object['content'] = $this->encode->getFormEncode('content');
		$object['display'] = $this->encode->getFormEncode('display');
		$object['addtime'] = date('Y-m-d H:i:s');
		$object['adduser'] = $this->admin_username;
		$this->load->model('advertisement/advertisement', 'adv_model');
		$adv_id = $this->adv_model->add( $object );
		echo('<script type="text/javascript">alert("添加成功");location.href="adv_manage";</script>');
	}
	
	// 编辑广告表单
	public function adv_edit(){
		
		$this->load->library('encode');
		$this->load->model('advertisement/advertisement', 'adv_model');
		$params = array();
		$params['id'] = $this->encode->get_request_encode('id');
		$params['r'] = $this->encode->get_request_encode('r');
		
		$sets = array('id'=>$params['id'], 'fields'=>'*');
		$object = $this->adv_model->get_object($sets);
		$tpl = 'admin/advertisement/adv_edit.html';
		$this->tpl->assign('adv', $object);
		$this->tpl->assign('params', $params);
		$this->tpl->display($tpl);
	}
	
	// 广告提交修改处理程序
	public function adv_edit_submit(){
		$this->load->library('encode');
		$r = $this->encode->getFormEncode('r');
		$object['id'] = $this->encode->getFormEncode('id');
		$object['adv_name'] = $this->encode->getFormEncode('adv_name');
		$object['price'] = $this->encode->getFormEncode('price');
		$object['adv_start_date'] = $this->encode->getFormEncode('adv_start_date');
		$object['adv_end_date'] = $this->encode->getFormEncode('adv_end_date');
		$object['content'] = $this->encode->getFormEncode('content');
		$object['display'] = $this->encode->getFormEncode('display');
		//$object['addtime'] = date('Y-m-d H:i:s');
		//$object['adduser'] = $this->admin_username;
		$this->load->model('advertisement/advertisement', 'adv_model');
		$this->adv_model->edit( $object );
		echo('<script type="text/javascript">alert("修改完成");location.href="'. $r .'";</script>');
	}
	
	// 广告删除程序
	public function adv_delete(){
		$this->load->library('encode');
		$r = $this->encode->get_request_encode('r');
		$id = $this->encode->get_request_encode('id');
		$this->load->model('advertisement/advertisement', 'adv_model');
		$this->adv_model->delete($id);	// 删除广告
		echo('<script type="text/javascript">alert("删除成功");location.href="'. $r .'";</script>');
	}
	
	
}
?>