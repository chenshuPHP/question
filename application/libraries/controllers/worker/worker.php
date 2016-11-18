<?php if( !defined('BASEPATH') ) exit('禁止直接浏览');

class worker extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _remap($class, $params){
		
		if( count($params) == 0 ){
			
			$class = strtolower($class);
			
			if( preg_match('/^index(\-\d+){2}$/', $class) ){
				$params = str_replace('index-', '', $class);
				$params = explode('-', $params);
				$this->index($params);
			} elseif ( preg_match('/^detail\-\d+$/', $class) ){
				$id = str_replace('detail-', '', $class);
				$this->detail($id);
			} else {
				if( method_exists($this, $class) ){
					$this->$class();
				} else {
					show_404();
				}
			}
		} elseif ( strtolower($class) == 'manage' ) {
			
			$class_name = 'worker_manage';
			$method_name = array_shift($params);
			
			include('worker_manage.php');
			$object = new $class_name();
			
			if( count($params) == 0 ){
				$object->$method_name();
			} else {
				$object->$method_name($params);
			}
		} elseif ( strtolower($class) == 'comment' ) {
			$class_name = 'worker_comment';
			$method_name = array_shift($params);
			include('worker_comment.php');
			$object = new $class_name();
			if( count($params) == 0 ){
				$object->$method_name();
			} else {
				$object->$method_name($params);
			}
		}
		
	}
	
	public function index($params = array(0, 1)){
		$type_id = $params[0];
		$page = $params[1];
		if( $page < 1 ) $page = 1;
		
		$this->load->library('thumb');
		$this->load->library('pagination');
		
		// 职位数据
		$this->load->model('worker/types_model', 'worker_types_model');
		$types = $this->worker_types_model->get_all();
		array_push($types, array('id'=>0, 'type_name'=>'全部'));
		
		$type = $this->worker_types_model->get_type($type_id);
		$this->tpl->assign('types', $types);
		$this->tpl->assign('type', $type);
		
		// 数据
		$this->load->model('worker/worker_model');
		$cfg = array('size'=>15, 'page'=>$page);
		$where_sql = '1=1';
		if( $type_id != 0 ){
			$where_sql .= " and worker_type = '" . $type_id . "'";
		}
		$sql = "select top ". $cfg['size'] ." id, name, sex, mobile, tel, worker_type, address, sheng, city, town, face from worker where id not in (select top ". ( $cfg['page'] - 1 ) * $cfg['size'] ." id from worker where ". $where_sql ." order by addtime desc) and ". $where_sql ." order by addtime desc";
		$count_sql = "select count(*) as icount from worker where " . $where_sql;
		$result = $this->worker_model->get_list($sql, $count_sql);
		
		$list = $result['list'];
		$count = $result['count'];
		if( $list != false ){
			foreach($list as $key=>$val){
				if( !empty($val['face']) ){
					$val['thumb'] = $this->thumb->crop($val['face'], 80, 80);
				} else {
					$val['thumb'] = '/resources/worker/noimg.gif';
				}
				if( $type_id == 0 ){
					$tmp = $this->worker_types_model->get_type($val['worker_type']);
					$val['type_name'] = $tmp['type_name'];
				} else {
					$val['type_name'] = $type['type_name'];
				}
				if( !empty($val['mobile']) ){
					$code = substr($val['mobile'], 3, 4);
					$val['mobile'] = str_replace($code, '****', $val['mobile']);
				}
				$list[$key] = $val;
			}
		}
		$this->tpl->assign('list', $list);
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $count);
		$this->pagination->url_template_first = '/worker/index-'.$type_id.'-1';
		$this->pagination->url_template = '/worker/index-'.$type_id.'-<{page}>';
		$pagination = $this->pagination->toString(true);
		$this->tpl->assign('pagination', $pagination);
		
		$phb = $this->worker_model->get_list("select top 10 id, name, sheng, city, town, face, worker_type from worker order by recommend desc");
		$phb = $phb['list'];
		$phb = $this->worker_types_model->fill($phb);
		foreach($phb as $key=>$val){
			if( !empty($val['face']) ){
				$val['thumb'] = $this->thumb->crop($val['face'], 80, 80);
			} else {
				$val['thumb'] = '/resources/worker/noimg.gif';
			}
			$phb[$key] = $val;
		}
		$this->tpl->assign('phb', $phb);
		
		$this->tpl->display('worker/index.html');
	}
	
	// 装修工人详细页
	public function detail($id){
		
		$this->load->library('thumb');
		
		$this->load->model('worker/worker_model');
		$this->load->model('worker/types_model', 'worker_types_model');
		$object = $this->worker_model->get_single($id);
		
		if( empty($object['face']) ){
			$object['face_thumb'] = '/resources/worker/noimg2.gif';
		} else {
			$object['face_thumb'] = $this->thumb->crop($object['face'], 100, 115);
		}
		$type = $this->worker_types_model->get_type($object['worker_type'], true);
		$object['worker_type'] = $type;
		
		$code = substr($object['card_code'], 6, 8);
		$object['card_code'] = str_replace($code, '********', $object['card_code']);
		if( !empty($object['mobile']) ){
			$code = substr($object['mobile'], 3, 4);
			$object['mobile'] = str_replace($code, '****', $object['mobile']);
		}
		$object['address'] = preg_replace('/\d/U', '*', $object['address']);
		//$object['card_code'] = $code;
		foreach($object as $key=>$val){
			if( empty($val) ){
				$object[$key] = '-';
			}
		}
		
		
		$phb = $this->worker_model->get_list("select top 10 id, name, sheng, city, town, face, worker_type from worker order by recommend desc");
		$phb = $phb['list'];
		$phb = $this->worker_types_model->fill($phb);
		foreach($phb as $key=>$val){
			if( !empty($val['face']) ){
				$val['thumb'] = $this->thumb->crop($val['face'], 80, 80);
			} else {
				$val['thumb'] = '/resources/worker/noimg.gif';
			}
			$phb[$key] = $val;
		}
		$this->tpl->assign('phb', $phb);
		
		$this->tpl->assign('object', $object);
		$this->tpl->display('worker/detail.html');
	}
	
	
	// 显示登记页面
	private function _show_register_page(){
		$this->load->model('worker/types_model', 'worker_types_model');
		$types = $this->worker_types_model->get_all();
		$this->tpl->assign('types', $types);
		$this->tpl->display('worker/register.html');
	}
	
	// 注册界面
	public function register(){
		
		$this->load->library('encode');
		$validate_code = $this->encode->getFormEncode('validate_code');
		
		if( $validate_code == '' ){
			$this->_show_register_page();
		} else {
			
			session_start();
			$validate_code_client = strtolower($validate_code);
			$validate_code_server = strtolower($_SESSION['worker_register_validate']);
			if( $validate_code_server == $validate_code_client ){
				$object = array();
				$object['name'] = $this->encode->getFormEncode('name');
				$object['card_code'] = $this->encode->getFormEncode('card_code');
				$object['sex'] = $this->encode->getFormEncode('sex');
				$object['worker_type'] = $this->encode->getFormEncode('worker_type');
				$object['cmp_name'] = $this->encode->getFormEncode('cmp_name');
				$object['address'] = $this->encode->getFormEncode('address');
				$object['mobile'] = $this->encode->getFormEncode('mobile');
				$object['tel'] = $this->encode->getFormEncode('tel');
				
				//$object['subsidy'] = $this->encode->getFormEncode('subsidy');
				//$object['hold_post'] = $this->encode->getFormEncode('hold_post');
				$object['subsidy'] = '未知';
				$object['hold_post'] = '未知';
				
				$object['sheng'] = $this->encode->getFormEncode('User_Shen');
				$object['city'] = $this->encode->getFormEncode('User_City');
				$object['town'] = $this->encode->getFormEncode('User_Town');
				
				$object['addtime'] = date('Y-m-d H:i:s');
				
				$error_message = array();
				
				if( empty($object['name']) ){
					$error_message['name'] = '姓名不能为空';
				}
				
				//if( empty( $object['card_code'] ) ){
				//	$error_message['card_code'] = '身份证号码不能为空';
				//}
				
				if( empty($object['mobile']) && empty($object['tel']) ){
					$error_message['tel'] = '手机号码/固定电话不能为空';
				}
				
				if( count($error_message) == 0 ){
					$this->load->model('worker/worker_model');
					$id = $this->worker_model->add($object);
					
					// 添加完成 登录
					$this->load->model('worker/worker_login_model');
					$this->worker_login_model->set_login($id);
					
					// 导向到 完善信息
					echo('<script type="text/javascript">alert("注册成功");location.href="/worker/manage/index.html";</script>');
					
				} else {
					echo('<div>数据不完整</div>');
					var_dump($error_message);
				}
				
			} else {
				echo('验证码不正确');
			}
			
		}
		
	}
	
	// 登录界面
	public function login(){
		$this->load->library('encode');
		$validate_code = $this->encode->getFormEncode('validate_code');
		$object = array();
		$object['card_code'] = $this->encode->getFormEncode('card_code');
		
		if( $validate_code == '' ){
			$this->tpl->display('worker/login.html');
		} else {
			session_start();
			$validate_code_client = strtolower($validate_code);
			$validate_code_server = strtolower($_SESSION['worker_register_validate']);
			if( $validate_code_server == $validate_code_client ){
				
				$error_message = array();
				
				if( empty( $object['card_code'] ) ){
					$error_message['card_code'] = '身份证号码不能为空';
				}
				
				if( count($error_message) == 0 ){
					$this->load->model('worker/worker_model');
					$id = $this->worker_model->get_worker_id($object['card_code']);	// 根据身份证号码获取用户ID
					if( $id ){
						// 添加完成 登录
						$this->load->model('worker/worker_login_model');
						$this->worker_login_model->set_login($id);
						echo('<script type="text/javascript">alert("登录成功");location.href="/worker/manage/index.html";</script>');
					} else {
						$this->tpl->assign('err', '您输入的身份证号码还未登记');
					}
				} else {
					echo('<div>数据不完整</div>');
					var_dump($error_message);
				}
			} else {
				$this->tpl->assign('code', $object['card_code']);
				$this->tpl->assign('err', '验证码不正确');
			}
			$this->tpl->display('worker/login.html');
		}
		
	}
	
	// 验证码
	public function validate_code(){
		session_start();
		$this->load->library('kaocode');
		$this->kaocode->doimg();
		$_SESSION['worker_register_validate'] = $this->kaocode->getCode();
	}
	
	public function check_validate_ajax(){
		session_start();
		$this->load->library('encode');
		$client_code = strtolower( $this->encode->getFormEncode('code') );
		$server_code = strtolower($_SESSION['worker_register_validate']);
		
		if( $client_code == $server_code ){
			echo('1');
		} else {
			echo('0');
		}
		
	}
	
	
}

?>