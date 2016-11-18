<?php

// 购买相关功能控制器
class tuan_trade extends tuan_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 订单创建界面
	public function create_order(){
		
		$id = $this->gr('id');
		$count = $this->gr('count');
		
		if( ! preg_match('/^\d+$/', $id) ){
			show_404();
			exit();
		}
		
		$this->load->model('mall/mall_product_model');
		//$this->load->model('mall/mall_cmp_model');
		$prd = $this->mall_product_model->get_product($id, 'id, title, senduser, flag');
		
		if( ! $prd ) {
			show_404();
			exit();
		}
		
		if( $prd['flag'] < 1 ){
			show_error('本产品无法购买，产品未通过审核', 404);
		}
		
		$this->tpl->assign('prd', $prd);
		$this->tpl->assign('count', $count);
		
		$this->tpl->display('tuan/trade/create_order2.html');
	}
	
	// 输出验证码
	public function validate(){
		$this->load->library('kaocode');
		$this->kaocode->doimg();
		session_start();
		$_SESSION['trade_validate_code'] = $this->kaocode->getCode();
	}
	
	private function _validate_check($validate){
		
		if( empty( $validate ) ) return false;
		$validate = strtolower( $validate );
		
		session_start();
		
		if( isset( $_SESSION['trade_validate_code'] ) ){
			$v2 = strtolower( $_SESSION['trade_validate_code'] );
		} else {
			$v2 = '';
		}
		
		if( $v2 == '' ){
			return false;
		}
		
		if( $validate == $v2 ){
			return true;
		}
		
		return false;
		
	}
	
	// 清除验证码
	private function _validate_clear(){
		if( ! isset( $_SESSION ) ) session_start();
		$_SESSION['trade_validate_code'] = '';
	}
	
	// ajax 检测验证码
	public function validate_check(){
		if( ! $this->_validate_check($this->gf('validate')) ){
			echo(0);
		} else {
			echo(1);
		}
	}
	
	// 订单表单提交
	public function order_handler(){
		
		$order = array(
			'pid'=>$this->gf('pid'),
			'count'=>$this->gf('count'),
			'name'=>$this->gf('name'),
			'mobile'=>$this->gf('mobile'),
			'sheng'=>$this->gf('User_Shen'),
			'city'=>$this->gf('User_City'),
			'town'=>$this->gf('User_Town'),
			'address'=>$this->gf('address'),
			'validate'=>$this->gf('validate')
		);
		
		if( ! preg_match('/^\d+$/', $order['pid']) ){
			show_error('目标产品参数错误', 404);
		}
		
		if( ! preg_match('/^\d+$/', $order['count']) || $order['count'] < 0 ){
			show_error('购买数量不明确', 404);
		}
		
		// 检测验证码
		if( ! $this->_validate_check( $order['validate'] ) ){
			show_error('验证码错误或已失效', 404);
		}
		
		if( empty($order['mobile']) || empty( $order['name'] ) ){
			show_error('请输入您的联系方式和称呼', 404);
		}
		
		$this->load->model('mall/mall_product_model');
		$prd = $this->mall_product_model->get_product($order['pid'], 'id, title, price, webprice, flag');
		
		if( ! $prd ){
			show_error('找不到产品信息', 404);
		}
		if( $prd['flag'] < 1 ){
			show_error('该产品无法购买，未审核', 404);
		}
		
		$order['prd'] = $prd;	// 订单产品信息
		
		$this->load->model('mall/mall_order_model');
		
		try{
			$id = $this->mall_order_model->add($order);
			$this->_validate_clear();						// 清除验证码方式重复提交
			$this->tpl->assign('submit_success', '1');
			$this->tpl->display('tuan/trade/create_order2.html');
			
		}catch(Exception $e){
			show_error($e->getMessage(), 404);
		}
		
	}
	
	
}

?>