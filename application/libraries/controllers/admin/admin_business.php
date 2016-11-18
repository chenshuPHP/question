<?php if( !defined('BASEPATH') ) exit('禁止浏览 admin_business.php');

class admin_business extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 快速预约通道信息浏览
	public function express_pipe(){
		
		$this->load->library('encode');
		$page = $this->encode->get_request_encode('page');
		if( empty($page) ) $page = 1;
		$cfg = array('page'=>$page, 'size'=>20);
		$this->load->model('publish/pubModel', 'business_model');
		$sql = "select top ". $cfg['size'] ." id, tel, true_name, addtime, rel, invalid from sendzh_express_pipe where id not in (select top ". ($page-1)*$cfg['size'] ." id from sendzh_express_pipe order by addtime desc) order by addtime desc";
		$sql_count = "select count(*) as icount from sendzh_express_pipe";
		$res = $this->business_model->get_express_pipe_list($sql, $sql_count);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $res['count']);
		$this->pagination->url_template = 'express_pipe?page=<{page}>';
		$this->pagination->url_template_first = 'express_pipe';
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign("list", $res['list']);
		$this->tpl->assign("pagination", $pagination);
		
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->assign('r', urlencode($_SERVER['REQUEST_URI']));
		$this->tpl->display('admin/business/express_pipe.html');
	}
	
	// 快速预约通道信息编辑
	public function express_edit(){
		$this->load->library('encode');
		$id = $this->encode->get_request_encode('id');
		$r = $this->encode->get_request_encode('r');
		$this->load->model('publish/pubModel', 'business_model');
		$object = $this->business_model->get_express_pipe_object($id);
		
		// 改版后的城市为数字，要转换为字符串
		if( is_numeric( $object['shen'] ) ){
			$this->load->model('city_model');
			$object['shen'] = $this->city_model->get($object['shen'], array('fields'=>'id, cname'));
			$object['shen'] = $object['shen']['cname'];
			$object['city'] = $this->city_model->get($object['city'], array('fields'=>'id, cname'));
			$object['city'] = $object['city']['cname'];
			$object['town'] = $this->city_model->get($object['town'], array('fields'=>'id, cname'));
			$object['town'] = $object['town']['cname'];
		}
		
		
		$this->tpl->assign('r', $r);
		$this->tpl->assign('object', $object);
		//var_dump($object['ps']);
		$this->tpl->display('admin/business/express_edit.html');
	}
	
	// 快速预约通道信息回访后编辑表单提交
	public function express_edit_submit(){
		$this->load->library('encode');
		$object = array();
		$r = $this->encode->getFormEncode('r');
		$object['id'] = $this->encode->getFormEncode('id');
		$object['true_name'] = $this->encode->getFormEncode('name');
		$object['tel'] = $this->encode->getFormEncode('tel');
		$object['shen'] = $this->encode->getFormEncode('sheng');
		$object['city'] = $this->encode->getFormEncode('city');
		$object['town'] = $this->encode->getFormEncode('town');
		$object['address'] = $this->encode->getFormEncode('address');
		$object['email'] = $this->encode->getFormEncode('email');
		$object['area'] = $this->encode->getFormEncode('area');
		$object['budget'] = $this->encode->getFormEncode('budget');
		$object['category'] = $this->encode->getFormEncode('category');
		$object['invalid'] = $this->encode->getFormEncode('invalid');
		$object['invalid_reason'] = $this->encode->getFormEncode('invalid_reason');
		if( $object['true_name'] != '' && $object['tel'] != '' ){
			$this->load->model('publish/pubModel', 'business_model');
			$this->business_model->express_edit($object);
			echo('<script>alert("修改成功");location.href="'. $r .'";</script>');
		} else {
			echo('称呼和电话不能为空');
		}
	}
	
	// 装修预约管理
	public function deco_orders(){
		$this->load->library('encode');
		$this->load->model('publish/deco_orders_model');
		
		$cfg = array(
			'page'=>$this->encode->get_request_encode('page'),
			'size'=>30
		);
		if( ! preg_match('/^\d+$/', $cfg['page']) ) $cfg['page'] = 1;
		
		$opts = array(
			'invalid'=>$this->encode->get_request_encode('invalid'),
			'visit'=>$this->encode->get_request_encode('visit'),
			'sort'=>$this->encode->get_request_encode('sort'),
			'key'=>$this->encode->get_request_encode('key')
		);
		
		
		
		// 搜索关键词
		if( !empty($opts['key']) ){
			$opts['key'] = iconv("gbk", "utf-8", $opts['key']);
		}
		
		//var_dump($opts);
		
		if( empty($opts['invalid']) ) $opts['invalid'] = 1;
		if( ! preg_match('/^\d+$/', $opts['visit']) ) $opts['visit'] = '';
		
		$where = "1=1";
		if( $opts['invalid'] == 1 ){
			$where .= " and invalid = 0";
		} elseif ($opts['invalid'] == 2) {
			$where .= " and invalid = 1";
		}
		
		
		$where2 = $where;
		
		if( $opts['visit'] != '' ){
			if( $opts['visit'] != 0 ){
				$where .= " and visit_date <> '' and datediff(d, '". date('Y-m-d') ."', visit_date) >= 0 and datediff(d, '". date('Y-m-d') ."', visit_date) = ". ( $opts['visit'] - 1 );
			} else {
				$where .= " and visit_date <> '' and datediff(d, '". date('Y-m-d') ."', visit_date) < 0";
			}
		}
		
		if( ! empty($opts['key']) ){
			$where .= " and ( fullname like '%". $opts['key'] ."%' or address like '%". $opts['key'] ."%' or mobile like '%". $opts['key'] ."%' )";
		}
		
		// 分区域显示
		// 2015-07-03 非总管理 管理员只可查看本地的客户信息
		if( ! empty( $this->admin_city_id ) ){
			$where .= " and scity = '". $this->admin_city_id ."'";
		}
		 
		
		$order = "order by addtime desc";
		if( $opts['sort'] == 'visit' ){
			$order = "order by visit_date desc";
		}
		
		$sql = "select top ". $cfg['size'] ." id, housetype, sprovince, scity, town, slocal, address, shome, sroom, housearea, budget, inv, fullname, housestructs, invalid, hide, source, addtime, visit_date, bz_content from sendzh where id not in (select top ". ($cfg['page']-1) * $cfg['size'] ." id from sendzh where ". $where ." ".$order .") and ". $where ." " . $order;
		
		$sql_count = "select count(*) as icount from sendzh where " . $where;
		
		$result = $this->deco_orders_model->get_deco_order_list($sql, $sql_count);
		$result['list'] = $this->deco_orders_model->assign_business($result['list']);
		
		// 附加回访状态
		$this->load->model('biz/biz_visit_model');
		$result['list'] = $this->biz_visit_model->visit_state_assign($result['list']);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $result['count']);
		
		
		$this->pagination->url_template = 'deco_orders?key='. $opts['key'] .'&sort='. $opts['sort'] .'&page=<{page}>&visit='. $opts['visit'] .'&invalid=' . $opts['invalid'];
		$this->pagination->url_template_first = 'deco_orders?key='. $opts['key'] .'&sort='. $opts['sort'] .'&page=1&visit='. $opts['visit'] .'&invalid=' . $opts['invalid'];
		
		
		//$this->pagination->url_template = 'deco_orders?key='. $opts['key'] .'&page=<{page}>&sort='. $opts['sort'] .'&invalid=' . $opts['invalid'];
		//$this->pagination->url_template_first = 'deco_orders?key='. $opts['key'] . '&sort='. $opts['sort'] .'&invalid=' . $opts['invalid'];
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->assign('opts', $opts);
		$this->tpl->assign('pagination', $pagination);
		
		if( $opts['invalid'] == 2 ){
			$this->tpl->assign('module', 'invalid');
		} else {
			$this->tpl->assign('module', 'effec');
		}
		
		// 统计数据
		// 今日回访
		$visit_count = array();
		$visit_count['a'] = $this->deco_orders_model->get_count("select count(*) as icount from sendzh where invalid = 0 and visit_date <> '' and datediff(d, '". date('Y-m-d') ."', visit_date) = 0 and " . $where2);
		$visit_count['b'] = $this->deco_orders_model->get_count("select count(*) as icount from sendzh where invalid = 0 and visit_date <> '' and datediff(d, '". date('Y-m-d') ."', visit_date) = 1 and " . $where2);
		$visit_count['c'] = $this->deco_orders_model->get_count("select count(*) as icount from sendzh where invalid = 0 and visit_date <> '' and datediff(d, '". date('Y-m-d') ."', visit_date) = 2 and " . $where2);
		$visit_count['z'] = $this->deco_orders_model->get_count("select count(*) as icount from sendzh where invalid = 0 and visit_date <> '' and datediff(d, '". date('Y-m-d') ."', visit_date) < 0 and " . $where2);
		
		$this->tpl->assign('visit_count', $visit_count);
		$this->tpl->assign('r', urlencode($_SERVER['REQUEST_URI']));
		$this->tpl->display('admin/business/deco_orders.html');
	}
	
	// 信息修改界面 新版
	public function deco_order_edit(){
		$this->load->library('encode');
		$id = $this->encode->get_request_encode('id');
		$r = $this->encode->get_request_encode('r');
		
		$this->load->model('manager/manager_model');
		
		$this->load->model('publish/deco_orders_model');
		$order = $this->deco_orders_model->get_deco_order($id);
		
		
		if( ! empty( $this->admin_city_id ) && $order['scity'] != $this->admin_city_id ){
			exit('无权限');
		}
		
		// 派发记录
		$this->load->model('biz/biz_model');
		$biz = $this->biz_model->get_business_biz($id, 'id, title');

		if( $biz != false ){
			$biz = $this->biz_model->company_assign_biz($biz);
		}

		// 附加客服代表信息
		if( ! empty($order['aid']) ){
			$this->load->model('manager/manager_model');
			$order = $this->manager_model->assign($order, 'username, fullname', 'aid');
		}

		
		// 跟踪记录
		$this->load->model('biz/biz_state_model');
		$biz['distribut'] = $this->biz_state_model->tracks_assign( $biz['distribut'] );
		
		// 回访记录
		$this->load->model('biz/biz_visit_model');
		$visit = $this->biz_visit_model->get_biz_visit($order['id']);
		
		// 写入订单浏览记录
		$this->load->model('publish/sendzh_action_model');
		$this->sendzh_action_model->add(array(
			'username'		=> $this->admin_username,
			'time'			=> date('Y-m-d H:i:s'),
			'rid'			=> $order['id'],
			'act'			=> 'view',
			'ip'			=> $this->encode->get_ip()
		));
		
		$this->sendzh_action_model->assign( $order );
		$order['actions'] = $this->manager_model->assign( $order['actions'],  'fullname, username', 'username' );
		
		// var_dump2( $order );
		
		$this->tpl->assign('order', $order);
		$this->tpl->assign('rurl', $r);
		$this->tpl->assign('module', 'edit');
		
		$this->tpl->assign('visit', $visit);
		$this->tpl->assign('biz', $biz);
		
		$this->tpl->display('admin/business/deco_order_edit.html');
	}
	
	
	// 预约信息修改提交
	public function deco_order_edit_submit(){
		
		$this->load->library('encode');
		
		$r = $this->encode->getFormEncode('r');
		
		$info = array();
		$info['id'] = $this->encode->getFormEncode('id');
		$info['housetype'] = $this->encode->getFormEncode('housetype');
		$info['sheng'] = $this->encode->getFormEncode('sheng');
		$info['city'] = $this->encode->getFormEncode('city');
		$info['town'] = $this->encode->getFormEncode('town');
		$info['local'] = $this->encode->getFormEncode('local');
		$info['xiaoqu'] = $this->encode->getFormEncode('xiaoqu');
		$info['address'] = $this->encode->getFormEncode('address');
		$info['home'] = $this->encode->getFormEncode('home');
		$info['room'] = $this->encode->getFormEncode('room');
		$info['area'] = $this->encode->getFormEncode('area');
		$info['budget'] = $this->encode->getFormEncode('budget');
		$info['inv'] = $this->encode->getFormEncode('inv');
		$info['shigongzizhi'] = $this->encode->getFormEncode('shigongzizhi');
		$info['shejizizhi'] = $this->encode->getFormEncode('shejizizhi');
		$info['fullname'] = $this->encode->getFormEncode('fullname');
		$info['sex'] = $this->encode->getFormEncode('sex');
		$info['mobile'] = $this->encode->getFormEncode('mobile');
		$info['email'] = $this->encode->getFormEncode('email');
		$info['detail'] = $this->encode->getFormEncode('detail');
		$info['tuijian'] = $this->encode->getFormEncode('tuijian');
		$info['invalid'] = $this->encode->getFormEncode('invalid');
		//$info['visit_date'] = $this->encode->getFormEncode('visit_date');
		$info['bz_detail'] = $this->encode->getFormEncode('bz_detail');
		
		// 隐藏
		$info['hide'] = $this->gf('hide');
		if( empty($info['hide']) ) $info['hide'] = 0;

		$this->load->model('publish/deco_orders_model');

		try{
			$this->deco_orders_model->deco_order_edit($info);
			
			// 写入订单编辑记录
			$this->load->model('publish/sendzh_action_model');
			$this->sendzh_action_model->add(array(
				'username'		=> $this->admin_username,
				'time'			=> date('Y-m-d H:i:s'),
				'rid'			=> $info['id'],
				'act'			=> 'edit',
				'ip'			=> $this->encode->get_ip()
			));
			
			
			echo('<script>alert("修改成功");location.href="'. $r .'";</script>');
		}catch(Exception $e){
			exit('未知错误,请联系管理员' . $e->getMessage());
		}
	}
	
	// 客户提交的订单 复制到 可以派发订单中 界面
	public function to_biz(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		$this->load->model('publish/deco_orders_model');
		$order = $this->deco_orders_model->get_deco_order($id);
		$order = $this->deco_orders_model->assign_business($order);
		if( $order['biz'] != false ) exit('此单已经提交过了。请不要重复提交！');
		
		$this->tpl->assign('order', $order);
		$this->tpl->assign('r', $r);
		$this->tpl->assign('module', 'to_biz');
		$this->tpl->display('admin/business/deco_to_biz.html');
	}
	
	// 订单提交到可派发订单中
	public function deco_to_biz_submit(){
		
		$this->load->model('publish/deco_orders_model');
		
		$id = $this->gf('id');
		$r = $this->gf('r');
		$contact_time = $this->gf('contact_time');
		
		$order = $this->deco_orders_model->get_deco_order($id);
		$order = $this->deco_orders_model->assign_business($order);
		if( $order['biz'] != false ) exit('此单已经提交过了。请不要重复提交！');
		
		$this->tpl->assign('order', $order);
		$content = $this->tpl->fetch('admin/business/deco_to_biz_template.html');
		$content = $this->encode->htmlencode($content);
		
		$biz = array(
			'title'=>$order['sprovince'] . $order['town'] . $order['slocal'] . $order['fullname'] . $contact_time,
			'content'=>$content,
			'addtime'=>date('Y-m-d H:i:s'),
			'adduser'=>$this->admin_username,
			'bizid'=>$order['id']
		);
		
		$this->load->model('biz/biz_model');
		try{
			$id = $this->biz_model->add($biz);
			echo('<script>alert("提交成功");location.href="'. $r .'";</script>');
		}catch(Exception $e){
			echo('<script>alert("提交失败:'. $e->getMessage() .'");history.back();</script>');
		}
		
	}
	
	
}
?>