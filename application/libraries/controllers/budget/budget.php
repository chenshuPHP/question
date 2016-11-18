<?php
class budget extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _remap($string, $params = array()){
		$string = strtolower($string);
		$method = $string;
		
		$cls = '';
		
		// http://www.shzh.net/budget/ => xxx.net/budget/budgets
		if( $string == '' || $string == 'index' ){
			$string = 'budgets';
		}
		
		// == 2015-06-23 扩展到单独文件 ==
		if( preg_match('/^\w+(\-\d+)+$/', $string) ){
			$temp = explode('-', $string);
			$cls = array_shift($temp);
			$args = $temp;
			array_unshift($args, 'home');
			$args[0] = implode('-', $args);
		} else {
			$cls = $string;
			$args = $params;
		}
		
		$dir = rtrim(dirname(__FILE__), '\\') . '\\';
		if( file_exists( $dir . 'budget_' . $cls . '.php' ) ){
			$this->route($cls, $args, 'budget');
			exit();
		}
		
		// == end ==
		
		
		// 详细页
		if( preg_match('/^view-\d+$/', $string) ){
			$method = 'view';
			$id = str_replace('view-', '', $string);
			$this->$method($id);
			exit();
		}elseif( preg_match('/^budgets-\d+$/', $string) ){
			$method = 'budgets';
			$page = str_replace('budgets-', '', $string);
			$this->$method($page);
		} else {
			$this->$method();
		}
	}
	
	private function index(){
		
		$tpl = 'budget/home.html';
		
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 1;	// 缓存更新周期1小时
		$cache_dir = $this->tpl->cache_dir . 'budget/';
		$this->tpl->cache_dir = $cache_dir;
		
		if( ! $this->tpl->isCached($tpl) ){
			$this->load->model('multi/multi_info_model');
			$url = $this->multi_info_model->get_url_info();
			$this->load->model('budget/budget_model');
			$this->load->model('budget/home_type_model');
			$this->load->model('budget/budget_counter_model');
			$home_types = $this->home_type_model->get_types();
			$quarter_types = $this->home_type_model->get_quarter_types();
			$download_all_count = $this->budget_counter_model->get_all_download_count();
			$decos = $this->multi_info_model->get_koubei_decos();
			$diarys = $this->multi_info_model->get_diarys();
			// 热门下载
			$hots = $this->budget_model->get_list("select top 5 id, home_type, cate, bao, quarter from budget order by view_count desc");
			// 热门装修公司
			$this->tpl->assign('decos', $decos);
			// 监理日记
			$this->tpl->assign('diarys', $diarys['list']);
			$this->tpl->assign('home_types', $home_types);
			$this->tpl->assign('quarter_types', $quarter_types);
			$this->tpl->assign('download_all_count', $download_all_count);
			$this->tpl->assign('multi_url', $url['multi_url']);
			$this->tpl->assign('res_multi_url', $url['res_multi_url']);
			$this->tpl->assign('hots', $hots['list']);
			$this->tpl->assign('hide_kf', '1');
			$this->tpl->assign('title', '2014装修报价');
			$this->tpl->display($tpl);
		} else {
			$this->tpl->display($tpl);
			echo('<!-- from the cache -->');
		}
		
	}
	
	// 列表页面
	/*
	private function _budgets($page = 1){
		$this->load->model('budget/budget_model');
		$this->load->library('thumb');
		$cfg = array(
			'page'=>$page,
			'size'=>12
		);
		$sql = "select top ". $cfg['size'] ." id, name, image, home_type, area, bao, cate from budget where id not in (select top ". ($cfg['page']-1)*$cfg['size'] ." id from budget order by addtime desc) order by addtime desc";
		$count_sql = "select count(*) as icount from budget";
		$result = $this->budget_model->get_list($sql, $count_sql);
		$list = $result['list'];
		$count = $result['count'];
		foreach($list as $key=>$val){
			if( !empty($val['image']) ){
				$thumb = $this->thumb->resize($val['image'], 225, 187);
			} else {
				$thumb = '/resources/budget/images/budgets_image_tmp.jpg';
			}
			$list[$key]['thumb'] = $thumb;
		}
		$this->tpl->assign('list', $list);
		//$this->tpl->assign('count', $result['count']);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $count);
		$this->pagination->url_template = '/budget/budgets-<{page}>';
		$this->pagination->url_template_first = '/budget/budgets';
		$pagination = $this->pagination->toString(true);
		$this->tpl->assign('pagination', $pagination);
		
		$this->load->model('budget/home_type_model');
		$configs = $this->home_type_model->get_all_types();
		$this->tpl->assign('configs', $configs);
		
		$this->tpl->display('budget/budgets.html');
	}
	*/
	
	// 所有报价查看
	/*
	private function budgets(){
		$this->load->library('encode');
		$this->load->model('multi/multi_info_model');
		$url = $this->multi_info_model->get_url_info();
		$decos = $this->multi_info_model->get_koubei_decos();
		$diarys = $this->multi_info_model->get_diarys();
		
		// 报价筛选
		$this->load->model('budget/home_type_model');
		
		$filters = $this->home_type_model->get_all_types();
		
		$args = array(
			'quarter'=>$this->encode->get_request_encode('quarter'),
			'home'=>$this->encode->get_request_encode('home'),
			'bao'=>$this->encode->get_request_encode('bao'),
			'econ'=>$this->encode->get_request_encode('econ')
		);
		
		foreach($args as $key=>$value){
			if( empty($value) ) $args[$key] = 0;	//赋值默认值0
		}
		
		// 根据传入的ID获取对应的值
		// array('quarter'=>'xxx', 'room'=>'xxx', 'bao'=>'xxx', 'econ'=>'xxx');
		$selected_args = $this->home_type_model->get_selected_args($args);
		
		// 检测是否选择了任何一个条件
		$temp = '';
		foreach($selected_args as $value){
			$temp .= $value;
		}
		if( empty($temp) ) $selected_args = false;
		
		$this->load->model('budget/budget_model');
		$cfg = array(
			'page'=>$this->encode->get_request_encode('page'),
			'size'=>20
		);
		$where = array();
		$params = array();
		if( $args['quarter'] != 0 ){
			$where[] = "quarter = '". $selected_args['quarter'] ."'";
			$params[] = "quarter=" . $args['quarter'];
		}
		if( $args['home'] != 0 ){
			$where[] = "home_type = '". $selected_args['home'] ."'";
			$params[] = "home=" . $args['home'];
		}
		if( $args['bao'] != 0 ){
			$where[] = "bao = '". $selected_args['bao'] ."'";
			$params[] = "bao=" . $args['bao'];
		}
		if( $args['econ'] != 0 ){
			$where[] = "cate = '". $selected_args['econ'] ."'";
			$params[] = "econ=" . $args['econ'];
		}
		
		$where = implode(' and ', $where);
		$params = implode('&', $params);
		
		if( !empty($where) ) $where = " where " . $where;
		if( ! preg_match('/^[1-9]\d*$/', $cfg['page']) ) $cfg['page'] = 1;
		$sql = "select * from ( select id, home_type, area, cate, bao, download_count, quarter, row_number() over(order by addtime desc) as num from budget". $where .") as temp where num between ". (($cfg['page']-1) * $cfg['size'] + 1) ." and " . $cfg['page'] * $cfg['size'];
		$sql_count = "select count(*) as icount from budget" . $where;
		
		$result = $this->budget_model->get_list($sql, $sql_count);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $result['count']);
		$this->pagination->url_template = '/budget/budgets?page=<{page}>' . ( empty($params) ? '' : '&'.$params );
		$this->pagination->url_template_first = '/budget/budgets' . ( empty($params) ? '' : '?' . $params );
		$pagination = $this->pagination->toString(true);
		$this->tpl->assign('pagination', $pagination);
		
		
		$this->tpl->assign('filters', $filters);
		$this->tpl->assign('args', $args);
		$this->tpl->assign('selected_args', $selected_args);
		$this->tpl->assign('multi_url', $url['multi_url']);
		$this->tpl->assign('res_multi_url', $url['res_multi_url']);
		$this->tpl->assign('decos', $decos);
		$this->tpl->assign('diarys', $diarys['list']);
		$this->tpl->assign('budgets', $result['list']);
		$this->tpl->assign('hide_kf', 1);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->display('budget/filter.html');
	}
	*/
	
	
	/*
	private function view($id){
		$this->load->model('budget/budget_model');
		$this->load->library('encode');
		$object = $this->budget_model->get_budget($id);
		$object['detail'] = $this->encode->htmldecode($object['detail']);
		$this->tpl->assign('object', $object);
		$this->load->model('budget/home_type_model');
		$configs = $this->home_type_model->get_all_types();
		$this->tpl->assign('configs', $configs);
		$this->tpl->display('budget/view.html');
	}
	*/
	
	// 预算查询 供ajax方式，返回json数据类型
	private function query_budget(){
		$this->load->library('encode');
		$this->load->model('budget/budget_model');
		$args = array();
		$args['home_type'] = $this->encode->getFormEncode('t');
		$args['bao'] = $this->encode->getFormEncode('b');
		$args['cate'] = $this->encode->getFormEncode('c');
		$args['quarter'] = $this->encode->getFormEncode('qu');
		$result = $this->budget_model->query_budget($args);
		$result = json_encode($result);
		echo($result);
	}
	
	// 报价下载界面
	private function download(){
		$this->load->library('encode');
		$id = $this->encode->get_request_encode('id');
		$info = array(
			'name'=>$this->encode->get_request_encode('name'),
			'mobile'=>$this->encode->get_request_encode('mobile')
		);
		if( empty( $id ) ){
			$id = $this->encode->getFormEncode('budget_id');
			if( empty($id) ){
				show_404();
			} else {
				$info['name'] = $this->encode->getFormEncode('name');
				$info['mobile'] = $this->encode->getFormEncode('mobile');
			}
		}
		$this->tpl->assign('id', $id);
		$this->tpl->assign('info', $info);
		
		// 下载滚动数据
		$this->load->model('download/res_download_model');
		$list = $this->res_download_model->custom_gets("select top 12 name, mobile, addtime from [budget_download] where addtime in ( select max(addtime) from budget_download group by mobile ) order by addtime desc");
		$list = $this->res_download_model->format2($list);
		$this->tpl->assign('list', $list);
		
		$this->tpl->display('budget/download.html');
	}
	
	// 报价下载图片验证码
	private function validate(){
		session_start();
		$this->load->library('kaocode');
		$this->kaocode->doimg();
		$_SESSION['budget_download_validate_code'] = $this->kaocode->getCode();
	}
	
	private function check_validate($code){
		session_start();
		if( $_SESSION['budget_download_validate_code'] == '' ) return false;
		if( strtolower( $code ) != strtolower( $_SESSION['budget_download_validate_code'] ) ){
			return false;
		} else {
			return true;
		}
	}
	// 清除验证码，使验证码失效
	private function clear_validate(){
		$_SESSION['budget_download_validate_code'] = '';
	}
	
	// ajax方式检测验证码
	private function ajax_check_validate(){
		$this->load->library('encode');
		$validate = $this->encode->getFormEncode('validate');
		if( $this->check_validate($validate) == false ){
			echo('0');
		} else {
			echo('1');
		}
	}
	
	private function send_message(){
		$this->load->library('encode');
		$this->load->model('sms_model');			// 短信记录/查询类
		$this->load->model('budget/budget_model');
		$info = array();
		$info['budget_id'] = $this->encode->getFormEncode('budget_id');
		$info['name'] = $this->encode->getFormEncode('name');
		$info['mobile'] = $this->encode->getFormEncode('mobile');
		$info['code'] = $this->encode->getFormEncode('validate');
		$info['time'] = date('Y-m-d H:i:s');
		if( $this->check_validate($info['code']) == false ){
			echo('验证码不正确');
		} else {
			if( preg_match('/^1[3-8]\d{9}$/', $info['mobile']) == false ){
				echo('手机号码格式不正确');
			} else {
				$resend_time_limit = $this->budget_model->resend_time_limit;
				$check_resend = $this->sms_model->check_resend_limit($info['mobile'], $resend_time_limit);
				if( $check_resend == false ) {
					echo('您发送的太频繁了, '. $resend_time_limit . '秒内只可发送一次');
				} else {
					if( $this->sms_model->check_send_count($info['mobile']) == false ){
						echo('您短时间内已经发送了太多短信，请休息一下');
					} else {
						$validate = $this->sms_model->get_validate_code();	// 产生随机验证码
						$config = array(
							'tid'=>$info['budget_id'],
							'message'=>urlencode( '【上海室内装饰行业协会】装修报价下载验证码:'. $validate . '。' ),
							'category'=>$this->budget_model->send_message_category,
							'addtime'=>$info['time'],
							'mobile'=>$info['mobile'],
							'validate_code'=>$validate
						);
						// ==== 短信发送 ====
						$this->load->library('send_message');
						$this->send_message->send($config['mobile'], $config['message']);
						// =================
						$this->sms_model->add($config);	// 记录发送的短信内容
						$this->clear_validate();		// 清除验证码，防止重复提交同一个验证码
						echo('1');
					}
				}
			}
		}
	}
	
	private function get_file(){
		$this->load->library('encode');
		$info = array();
		$info['name'] = $this->encode->getFormEncode('name');
		$info['mobile'] = $this->encode->getFormEncode('mobile');
		$info['validate'] = $this->encode->getFormEncode('validate');
		$info['bid'] = $this->encode->getFormEncode('bid');	// budget ID
		$info['addtime'] = date('Y-m-d H:i:s');
		$this->load->model('budget/budget_model');
		// 检测短信码是否有权限下载文件
		$res = $this->budget_model->get_file($info);
		if( $res['type'] == 'success' ){
			$this->budget_model->download_record($info);
		}
		echo(json_encode($res));
	}
	
	// 更新预算浏览次数
	private function update_view_count(){
		$label = 'budget_viewed_ids';
		$this->load->library('encode');
		$id = $this->encode->getFormEncode('budget_id');
		if( empty($id) ) show_404();
		//$id = $this->encode->get_request_encode('budget_id');
		// 已经浏览过了的预算统计
		if( isset( $_COOKIE[$label] ) ){
			$viewed_ids = $_COOKIE[$label];	
			$viewed_ids = explode(',', $viewed_ids);
		} else {
			$viewed_ids = array();
		}
		$is_viewed = false;	// 是否浏览过
		foreach( $viewed_ids as $key=>$val ){
			if( $val == $id ){
				$is_viewed = true;
				break;
			}
		}
		// 未浏览过
		if( $is_viewed == false ){
			$this->load->model('budget/budget_model');
			$res = $this->budget_model->update_view_count($id);
			if( $res == 1 ){
				array_push($viewed_ids, $id);
				setcookie($label, implode(',', $viewed_ids), time() + 3600);
			}
			// else {
			//	echo('更新时发生错误，错误码：' . $res);
			//}
		}
	}
	
}
?>