<?php
// 装修报价管理
// kko4455@163.com
// 2014-01-07
class admin_budget extends admin_base {
	
	public function __construct(){
		parent::__construct();
		$this->load->library('encode');
	}
	
	// 报价管理主页
	// 报价列表，报价删除，报价编辑链接
	public function manage(){
		$this->load->model('budget/budget_model');
		$this->load->model('budget/budget_config_model');
		
		$page = $this->encode->get_request_encode('page');
		if( empty($page) ) $page = 1;
		$sort = $this->encode->get_request_encode('sort');
		if( empty($sort) ) $sort = 'time';
		$cfg = array('size'=>20, 'page'=>$page);
		
		switch($sort){
			case 'time':
				$sort_sql = "order by addtime desc";
				break;
			case 'recommend':
				$sort_sql = "order by recommend desc";
				break;
			case 'viewed':
				$sort_sql = "order by view_count desc";
				break;
			default:
				$sql_sql = "order by addtime desc";
				break;
		}
		
		$sql = "select top ". $cfg['size'] ." id, name, recommend, view_count from budget where id not in (select top ". ($cfg['page']-1)*$cfg['size'] ." id from budget ". $sort_sql .") " . $sort_sql;
		$count_sql = "select count(*) as icount from budget";
		$result = $this->budget_model->get_list($sql, $count_sql);
		
		
		//echo('<!--');
		$result['list'] = $this->budget_config_model->cfg_assign_budgets($result['list']);
		//var_dump($result['list']);
		//echo('-->');
		
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->url_template_first = 'manage?page=1&sort=' . $sort;
		$this->pagination->url_template = 'manage?page=<{page}>&sort=' . $sort;
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $result['count']);
		$pagination = $this->pagination->toString(true);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('r', urlencode($_SERVER['REQUEST_URI']));
		
		$this->tpl->assign('module', 'budget.manage');
		
		$this->tpl->display('admin/budget/manage.html');
	}
	
	// 添加报价
	public function add(){
		
		$tpl = 'admin/budget/add.html';
		
		$this->load->model('budget/home_type_model');
		$home_types = $this->home_type_model->get_types();
		$quarter_types = $this->home_type_model->get_quarter_types();
		$this->tpl->assign('home_types', $home_types);
		$this->tpl->assign('quarter_types', $quarter_types);
		
		$this->tpl->assign('module', 'budget.add');
		
		// 获取配置参数
		$this->load->model('budget/budget_config_model');
		
		$config = $this->budget_config_model->roots();
		$config = $this->budget_config_model->child_assign($config);
		
		$this->tpl->assign('config', $config);
		
		$this->tpl->display( $tpl );
	}
	
	// 表单添加处理
	public function add_submit(){
		//$detail = $
		//phpinfo();
		$this->load->model('budget/budget_model');
		$object = array();
		$object['image'] = $this->encode->getFormEncode('image');
		$object['name'] = $this->encode->getFormEncode('name');
		
		//$object['home_type'] = $this->encode->getFormEncode('home_type');
		//$object['bao'] = $this->encode->getFormEncode('bao');
		//$object['cate'] = $this->encode->getFormEncode('cate');
		// 新增季度 2014-10-30
		// $object['quarter'] = $this->encode->getFormEncode('quarter');
		
		// 2015-06-19
		// 以上四种属性 已经更改到单独表来记录
		$object['config'] = $this->encode->getFormEncode('config');
		
		$object['ps'] = $this->encode->getFormEncode('ps');
		$object['area'] = $this->encode->getFormEncode('area');
		$object['detail'] = $this->encode->getFormEncode('detail');
		$object['recommend'] = $this->encode->getFormEncode('recommend');
		$object['attach'] = $this->encode->getFormEncode('attach_path');
		$object['addtime'] = date('Y-m-d H:i:s');
		
		
		$id = $this->budget_model->add($object);
		echo('<script type="text/javascript">alert("添加成功");location.href="manage";</script>');
		
	}
		
	// 报价修改界面
	public function edit(){
		
		$id = $this->encode->get_request_encode('id');
		$r = $this->encode->get_request_encode('r');
		$this->load->model('budget/budget_model');
		$object = $this->budget_model->get_budget($id);
		//$object['detail'] = $this->encode->htmldecode($object['detail']);
		$this->tpl->assign('r', $r);
		$this->tpl->assign('object', $object);
		
		$this->load->model('budget/home_type_model');
		$home_types = $this->home_type_model->get_types();
		$quarter_types = $this->home_type_model->get_quarter_types();
		$this->tpl->assign('home_types', $home_types);
		$this->tpl->assign('quarter_types', $quarter_types);
		
		// 获取配置参数
		$this->load->model('budget/budget_config_model');
		
		$config = $this->budget_config_model->roots();
		$config = $this->budget_config_model->child_assign($config);
		
		$this->tpl->assign('config', $config);
		
		// 获取预算属性
		$budget_config = $this->budget_config_model->get_budget_config($id);
		
		
		
		$this->tpl->assign('module', 'budget.edit');
		$this->tpl->assign('budget_config', $budget_config);
		$this->tpl->display('admin/budget/edit.html');
	}
	
	// 修改提交处理
	public function edit_submit(){
		$this->load->model('budget/budget_model');
		$r = $this->encode->get_request_encode('r');
		$object = array();
		$object['id'] = $this->encode->getFormEncode('id');
		$object['image'] = $this->encode->getFormEncode('image');
		$object['name'] = $this->encode->getFormEncode('name');
		
		//$object['bao'] = $this->encode->getFormEncode('bao');
		//$object['cate'] = $this->encode->getFormEncode('cate');
		//$object['home_type'] = $this->encode->getFormEncode('home_type');
		// 新增季度 2014-10-30
		//$object['quarter'] = $this->encode->getFormEncode('quarter');
		
		// 2015-06-19
		// 以上四种属性 已经更改到单独表来记录
		$object['config'] = $this->encode->getFormEncode('config');
		
		$object['ps'] = $this->encode->getFormEncode('ps');
		$object['area'] = $this->encode->getFormEncode('area');
		$object['detail'] = $this->encode->getFormEncode('detail');
		$object['recommend'] = $this->encode->getFormEncode('recommend');
		$object['attach'] = $this->encode->getFormEncode('attach_path');
		
		
		//$object['addtime'] = date('Y-m-d H:i:s');
		$this->budget_model->edit($object);
		//var_dump($object);
		echo('<script type="text/javascript">alert("修改成功");location.href="'. $r .'";</script>');
	}
	
	// 报价删除
	public function delete(){
		$r = $this->encode->get_request_encode('r');
		$id = $this->encode->get_request_encode('id');
		$this->load->model('budget/budget_model');
		$this->budget_model->delete($id);
		echo('<script type="text/javascript">alert("已删除");location.href="'. $r .'";</script>');
	}
	
	// 报价附件删除
	public function attach_delete(){
		$cfg = array();
		$cfg['path'] = $this->encode->getFormEncode('p');
		$cfg['id'] = $this->encode->getFormEncode('id');
		$this->load->model('budget/budget_model');
		$this->budget_model->attach_delete($cfg);
		echo('1');
	}
	
	// 下载记录页面
	public function download_record(){
		$this->load->model('budget/budget_model');
		$page = $this->encode->get_request_encode('page');
		$sort = $this->encode->get_request_encode('s');
		
		if( empty($page) ) $page = 1;
		$cfg = array('size'=>20, 'page'=>$page);
		
		$order = "order by addtime desc";
		
		if( $sort == 'atime' ){
			$order = "order by atime desc";
		}
		
		
		$sql = "select top ". $cfg['size'] ." id, name, mobile, budget_id, addtime, mobile_addr, atime from budget_download where id not in ( select top ". ($cfg['page']-1)*$cfg['size'] ." id from budget_download ". $order ." ) and res_type = 'budget' ". $order ."";
		
		
		$count_sql = "select count(*) as icount from budget_download where res_type = 'budget'";
		
		$res = $this->budget_model->get_download_records($sql, $count_sql);
		
		$list = $res['list'];
		
		$list = $this->budget_model->check_download_visit($list);
		
		$count = $res['count'];
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->url_template_first = 'download_record?page=1&s=' . $sort;
		$this->pagination->url_template = 'download_record?page=<{page}>&s=' . $sort;
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $count);
		$pagination = $this->pagination->toString(true);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('list', $list);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->assign('r', urlencode($_SERVER['REQUEST_URI']));
		
		$this->tpl->assign('module', 'budget.download');
		
		$this->tpl->display('admin/budget/download_record.html');
	}
	
	// 获取手机归属地，并更新数据库
	public function get_mobile_addr(){
		
		$mobile = $this->encode->getFormEncode('mobile');
		//$appkey = '10003';
		//$secrty = '3b25e5c23cc5d57f2e174c7b817d127e';
		$result = array('type'=>'finish');
		if( preg_match('/^1\d{10}$/', $mobile) ){
			
			// 2015-06-17 获取手机归属地已经转移到 download/res_download_model->get_mobile_addr();
			//$url = 'http://api.k780.com:88/?app=phone.get&phone='. $mobile .'&appkey=10003&sign=b59bc3ef6191eb9f747dd4e83c99f2a4&format=json';
			//$object = json_decode( file_get_contents($url) );
			//var_dump($object->success);
			
			$this->load->model('download/res_download_model');
			$object = $this->res_download_model->get_mobile_addr($mobile);
			
			if( $object->success != '1' ){
				$result['type'] = 'error';
				$result['message']= $object->msg;
			} else {
				$this->load->model('budget/budget_model');
				$this->budget_model->update_mobile_addr($object->result);
				$result['type'] = 'success';
				$result['message']= '已经成功更新该号码归属地';
			}
			
		} else {
			$result['type'] = 'error';
			$result['message']= '需要获取归属地的手机号码格式不正确';
		}
		echo( json_encode($result) );
	}
	
	// 手机号码备注信息
	public function tel_relations(){
		$tel = $this->encode->get_request_encode('t');
		$back_url = $this->encode->get_request_encode('r');
		
		$mod = $this->gr('mod');
		
		$this->tpl->assign('tel', $tel);
		$this->tpl->assign('back_url', $back_url);
		
		// 读取备注信息
		$this->load->model('budget/budget_model');
		$relations = $this->budget_model->get_tel_relations($tel);
		$this->tpl->assign('relations', $relations);
		
		$this->tpl->assign('module', 'budget.download');
		
		$this->tpl->assign('mod', $mod);
		
		$this->tpl->display('admin/budget/tel_relations.html');
	}
	
	// 手机号码备注提交
	public function tel_relations_submit(){
		$info = array(
			'tel'=>$this->encode->getFormEncode('tel'),
			'detail'=>$this->encode->getFormEncode('content'),
			'admin'=>$_COOKIE['MANAGE_USER'],
			'atime'=>$this->encode->getFormEncode('atime'),
			'addtime'=>date('Y-m-d H:i:s')
		);
		
		// 回传参数
		$r = $this->encode->getFormEncode('r');
		$t = $this->encode->getFormEncode('t');
		
		$this->load->model('budget/budget_model');
		$this->budget_model->add_tel_relations($info);
		echo('<script>alert("提交成功");location.href="tel_relations?t='. $t .'&r='. urlencode($r) .'";</script>');
	}
	
	
	
}











?>