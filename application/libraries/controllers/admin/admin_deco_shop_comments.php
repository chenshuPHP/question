<?php if( !defined('BASEPATH') ) exit('禁止直接浏览');

class admin_deco_shop_comments extends admin_base {
	
	private $message_category;
	
	public function __construct(){
		parent::__construct();
		$this->load->library('encode');
		$this->message_category = 'deco_guest';
	}
	
	public function manage(){
		$page = $this->encode->get_request_encode('page');
		if( empty($page) ) $page = 1;
		$this->load->model('company/guest', 'deco_comments_model');
		$this->load->model('company/company', 'deco_model');
		$cfg = array('size'=>10, 'page'=>$page);
		
		$where_sql = "pid = 0 and module_type = 'shop'";
		
		$sql = "select top ". $cfg['size'] ." id, content, addtime, shopID as username, truename as name, state from shop_guest where " . $where_sql
		. " and id not in (select top ". ($cfg['page']-1) * $cfg['size'] ." id from shop_guest where ". $where_sql ." order by addtime desc) order by addtime desc";
		
		$count_sql = "select count(*) as icount from shop_guest where " . $where_sql;
		
		$result = $this->deco_comments_model->get_list($sql, $count_sql);
		$list = $result['list'];
		$count = $result['count'];
		$list = $this->deco_model->fill_collection($list);
		
		echo('<!--');
		var_dump($list);
		echo('-->');
		
		$this->tpl->assign('list', $list);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->assign('r', urlencode($_SERVER['REQUEST_URI']));
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $page;
		$this->pagination->url_template_first = 'manage';
		$this->pagination->url_template = 'manage?page=<{page}>';
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $count);
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->display('admin/deco_comments/manage.html');
	}
	
	
	// 留言详细页面
	public function detail(){
		
		$id = $this->encode->get_request_encode('id');
		$r = $this->encode->get_request_encode('r');
		
		$this->load->model('company/guest', 'deco_comments_model');
		$this->load->model('company/company', 'deco_model');
		
		$object = $this->deco_comments_model->get_guest($id);
		$object = array_change_key_case($object, CASE_LOWER);
		
		$deco = $this->deco_model->getCompany($object['shopid']);
		
		$this->tpl->assign('deco', $deco);
		$this->tpl->assign('object', $object);
		$this->tpl->assign('r', $r);
		
		// 短信手机号码：
		$tel = $object['mobile'];
		if( !empty($tel) ) {
			$tmp = substr($tel, -4);
			$tel = str_replace($tmp, '****', $tel);
		}
		
		$this->tpl->assign('tel', $tel);
		
		
		// 检测是否已经发过短信
		$this->load->model('sms_model');
		// 获取本记录相关短信发送的条数
		$exists_send_count = $this->sms_model->get_count($id, $this->message_category);
		
		$this->tpl->assign('exists_send_count', $exists_send_count);
		
		$this->tpl->display('admin/deco_comments/detail.html');
	}
	
	// 短信发送到装修公司
	public function send_message_to_deco(){
		
		$this->load->model('company/company', 'deco_model');
		
		$message = $this->encode->getFormEncode('message');
		$rid = $this->encode->getFormEncode('rid');
		$username = $this->encode->getFormEncode('username');
		
		$deco = $this->deco_model->getCompany($username);
		$category = $this->message_category;
		$mobile = $deco['mobile'];
		
		$this->load->library('send_message');
		$send_return = $this->send_message->send($mobile, $message);
		
		// 短信发送返回结果状态码
		if( $send_return == 0 ){
			// 发送成功
			$object = array('tid'=>$rid, 'category'=>$category, 'mobile'=>$mobile, 'message'=>$message, 'addtime'=>date('Y-m-d H:i:s'));
			$this->load->model('sms_model');
			$this->sms_model->add($object);
			echo('1');
		} else {
			echo('发送失败，短信服务器返回错误码：' . $send_return . '，请联系技术人员');
		}
		
	}
	
	// 批量删除留言信息
	public function delete_batch(){
		
		$data = $this->encode->getFormEncode('cid');
		$r = $this->encode->getFormEncode('r');
		
		$this->load->model('company/guest', 'deco_comments_model');
		
		// 批量删除留言
		// 删除留言的回复，删除留言的短信提醒记录
		$this->deco_comments_model->delete_batch($data);
		
		echo('<script type="text/javascript">location.href="'. $r .'";</script>');
		
	}
	
	
}

?>