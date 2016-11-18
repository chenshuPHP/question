<?php if( !defined('BASEPATH') ) exit('禁止直接浏览此文件 ~/controllers/admin/admin_worker.php');

class admin_worker extends admin_base {
	
	public function __construct(){
		parent::__construct();
		$this->load->library('encode');
		$this->load->model('worker/types_model', 'worker_types_model');
	}
	
	/*
	public function set_all_birthday(){
		$this->load->model('worker/worker_model');
		$list = $this->worker_model->get_list('select id, card_code, birthday from worker');
		$list = $list['list'];
		$this->load->library('mdb');
		foreach($list as $key=>$value){
			if( empty($value['birthday']) && !empty( $value['card_code'] ) ){
				$tmp = substr($value['card_code'], 6, 8);
				$birthday = date(substr($tmp, 0, 4) . '-' . substr($tmp, 4, 2) . '-' . substr($tmp, 6, 2));
				$this->mdb->update("update worker set birthday = '". $birthday ."' where id=" . $value['id']);
			}
		}
		echo('ok');
	}
	*/
	
	// 装修工人管理首页
	public function manage(){
		
		$this->load->model('worker/worker_model');
		//$this->load->model('worker/types_model', 'worker_type_model');
		$page = $this->encode->get_request_encode('page');
		$s = $this->encode->get_request_encode('s');	// 排序
		if( $page < 1 ) $page = 1;
		
		$cfg = array(
			'size'=>20,
			'page'=>$page
		);
		
		$order_sql = '';
		if( $s == 'recommend' ){
			$order_sql = ' order by recommend desc';
		} else {
			$order_sql = ' order by addtime desc';
		}
		
		$sql = "select top ". $cfg['size'] ." id, name, card_code, sex, worker_type, city, town, addtime, recommend, birthday from worker where id not in (select top ". ($cfg['page']-1)*$cfg['size'] ." id from worker". $order_sql .")" . $order_sql;
		$sql_count = "select count(*) as icount from worker";
		$result = $this->worker_model->get_list($sql, $sql_count);
		$list = $result['list'];
		$list = $this->worker_types_model->fill($list);
		$count = $result['count'];
		$this->tpl->assign('list', $list);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->assign('r', urlencode($_SERVER['REQUEST_URI']));
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->url_template_first = 'manage?s=' . $s;
		$this->pagination->url_template = 'manage?page=<{page}>&s=' . $s;
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $count);
		$pagination = $this->pagination->toString(true);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->display('admin/worker/manage.html');
	}
	
	// 设置工人岗位类型界面
	public function types(){
		$list = $this->worker_types_model->get_all();
		$this->tpl->assign('list', $list);
		$this->tpl->assign('r', urlencode($_SERVER['REQUEST_URI']));
		$this->tpl->display('admin/worker/types.html');
	}
	
	// 添加新岗位
	public function add_type(){
		$type_name = $this->encode->getFormEncode('type_name');
		if( $type_name == '' ){
			$this->tpl->display('admin/worker/add_type.html');
		} else {
			$this->worker_types_model->add($type_name);
			$this->tpl->assign('finish', '1');
			$this->tpl->display('admin/worker/add_type.html');
		}
	}
	
	// 删除
	public function delete_type(){
		$id = $this->encode->get_request_encode('id');
		$r = $this->encode->get_request_encode('r');
		$result = $this->worker_types_model->delete($id);
		if( $result === true ){
			echo('<script type="text/javascript">alert("删除成功");location.href="'. $r .'";</script>');
		} else {
			echo('<script type="text/javascript">alert("'. $result .'");location.href="'. $r .'";</script>');
		}
	}
	
	// 修改岗位
	public function edit_type(){
		$object = array();
		$object['type_name'] = $this->encode->getFormEncode('type_name');
		if( $object['type_name'] == '' ){
			$id = $this->encode->get_request_encode('id');
			$type = $this->worker_types_model->get_type($id);
			$this->tpl->assign('object', $type);
			$this->tpl->display('admin/worker/edit_type.html');
		} else {
			$object['id'] = $this->encode->getFormEncode('id');
			$this->worker_types_model->edit($object);
			$this->tpl->assign('finish', '1');
			$this->tpl->display('admin/worker/edit_type.html');
		}
	}
	
	// 进入会员中心
	public function go_worker_center(){
		$id = $this->encode->get_request_encode('id');
		$this->load->model('worker/worker_login_model');
		$this->worker_login_model->set_login($id);
		echo('<script type="text/javascript">location.href="/worker/manage/index";</script>');
	}
	
	// 设置推荐指数界面
	public function set_recommend(){
		
		$this->load->model('worker/worker_model');
		
		if( $this->encode->getFormEncode('recommend_val') == '' ) {
			$id = $this->encode->get_request_encode('id');
			$val = $this->worker_model->get_recommend_val($id);
			$this->tpl->assign('id', $id);
			$this->tpl->assign('val', $val);
			$this->tpl->display('admin/worker/set_recommend.html');
		} else {
			$id = $this->encode->getFormEncode('id');
			$val = $this->encode->getFormEncode('recommend_val');
			$this->worker_model->set_recommend_val($id, $val);
			echo('<script type="text/javascript">parent.location.reload();</script>');
		}
	}
	
	// 删除装修工人
	public function delete_worker(){
		$id = $this->encode->get_request_encode('id');
		$r = $this->encode->get_request_encode('r');
		$this->load->model('worker/worker_model');
		$this->worker_model->delete($id);
		echo('<script type="text/javascript">location.href="'. $r .'";</script>');
	}
	
}

?>