<?php if( !defined('BASEPATH') ) exit('禁止直接浏览');
class worker_comment extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function ajax_submit(){
		$this->load->library('encode');
		$object = array();
		$object['worker_id'] = $this->encode->getFormEncode('worker_id');
		$object['name'] = $this->encode->getFormEncode('name');
		$object['mobile'] = $this->encode->getFormEncode('mobile');
		$object['email'] = $this->encode->getFormEncode('email');
		$object['content'] = $this->encode->getFormEncode('content');
		$object['validate_code'] = $this->encode->getFormEncode('validate_code');
		$object['addtime'] = date('Y-m-d H:i:s');
		session_start();
		$server_validate_code = strtolower( $_SESSION['worker_register_validate'] );
		$client_validate_code = strtolower( $object['validate_code'] );
		$err = array('type'=>'error', 'data'=>'');
		if( $server_validate_code != $client_validate_code ){
			$err['data'] .= '验证码错误';
		}
		if( $object['worker_id'] == '' || $object['name'] == '' || $object['mobile'] == '' ){
			$err['data'] .= '您提交的数据不完整，请检查';
		}
		if( empty($err['data']) ){
			$this->load->model('worker/worker_comment_model');
			$this->worker_comment_model->add($object);
			$err['type'] = 'finish';
			$err['data'] = '提交成功';
		}
		echo(json_encode($err));
	}
	
}
?>