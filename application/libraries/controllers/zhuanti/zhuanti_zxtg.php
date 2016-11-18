<?php

class zhuanti_zxtg extends zhuanti_base {
	
	public $qq = '2487835313';
	
	public function __construct(){}
	
	public function terminate(){
		$this->active->tpl->assign('zhuanti_qq', $this->qq);
	}
	
	private function check_mobile_request(){
		
		$this->active->load->model('mobile/mobile_checker');
		return $this->active->mobile_checker->is_mobile();
	}
	
	
	public function handler(){
		
		if( $this->check_mobile_request() != false ){
			$this->active->alert('', 'http://www.shzh.net/mobile/mall/11980');
			exit();
		}
		
		$this->active->load->model('mall/mall_zxtg_model');
		
		$brands = $this->active->mall_zxtg_model->get_prds(array(
			'type'=>'brand',
			'fields'=>'image, name, link'
		));
		
		$prds = $this->active->mall_zxtg_model->get_prds(
			array(
				'type'=>'prd',
				'fields'=>'id, name, image, price, webprice, link'
			)
		);
		
		$workers = $this->active->mall_zxtg_model->get_prds(
			array(
				'type'=>'workers',
				'fields'=>'id, name, image, description'
			)
		);
		
		//$sjs = $this->active->mall_zxtg_model->get_prds(
		//	array(
		//		'type'=>'designs',
		//		'fields'=>'id, name, image, description'
		//	)
		//);
		
		$decos = $this->active->mall_zxtg_model->get_prds(
			array(
				'type'=>'decos',
				'fields'=>'id, image, name, username'
			)
		);
		
		//$robs = $this->active->mall_zxtg_model->get_prds(
		//	array(
		//		'type'=>'rob',
		//		'fields'=>'id, image, name, labels, price, webprice, link'
		//	)
		//);
		
		// 积分兑换
		$exchanges = $this->active->mall_zxtg_model->get_prds(
			array(
				'type'=>'exchange',
				'fields'=>'id, image, name, score, link'
			)
		);
		
		// 赠品
		$gifts = $this->active->mall_zxtg_model->get_prds(
			array(
				'type'=>'gift',
				'fields'=>'id, image, name'
			)
		);
		
		$this->active->load->model('mall/mall_bm_model');
		
		$bm_count = $this->active->mall_bm_model->get_count();
		
		$this->active->load->model('company/company', 'deco_model');
		$decos = $this->active->deco_model->fill_collection($decos, array(
			'fields'=>array('username', 'address', 'logo', 'koubei')
		));
		
		//$this->active->tpl->assign('robs', $robs);
		
		$this->active->tpl->assign('brands', $brands);
		$this->active->tpl->assign('prds', $prds);
		$this->active->tpl->assign('workers', $workers);
		//$this->active->tpl->assign('sjs', $sjs);
		$this->active->tpl->assign('decos', $decos);
		$this->active->tpl->assign('bm_count', $bm_count);
		
		$this->active->tpl->assign('exchanges', $exchanges);
		$this->active->tpl->assign('gifts', $gifts);
		
		//if( $this->active->gr('test') == '1' ){
		//	$this->active->tpl_name = 'zhuanti/' . $this->active->zhuanti_name . '/index3.html';
		//} else {
			$this->active->tpl_name = 'zhuanti/' . $this->active->zhuanti_name . '/index.html';
		//}
	}
	
	private function _dialog_common($config = array()){
	}
	
	public function validate(){
		
		$this->active->tpl_name = '';
		
		session_start();
		
		$this->active->load->library('kaocode');
		$this->active->kaocode->doimg();
		
		$_SESSION['zhuanti_zxtg_validate'] = $this->active->kaocode->getCode();
		
	}
	
	private function _check_validate($validate){
		
		$this->active->tpl_name = '';
		
		session_start();
		if( strtolower($validate) != strtolower( $_SESSION['zhuanti_zxtg_validate'] ) ){
			return false;
		}
		return true;
	}
	
	public function check_validate(){
		
		$this->active->tpl_name = '';
		
		$v = $this->active->gr('validate');
		if( $this->_check_validate($v) == false ){
			echo(0);
		} else {
			echo(1);
		}
	}
	
	// 产品对话框
	public function prd_dialog(){
		
		$id = $this->active->gr('id');
		$this->active->load->model('mall/mall_zxtg_model');
		$prd = $this->active->mall_zxtg_model->get_prd(
			$id,
			'*'
		);
		if( ! $prd ){
			show_error('找不到目标对象', 404);
			exit();
		}
		$this->active->tpl->assign('prd', $prd);
		
		$this->active->tpl_name = 'zhuanti/' . $this->active->zhuanti_name . '/prd_dialog.html';		// 阻止调用默认模版
	}
	
	// 施工队
	public function worker_dialog(){
		
		$id = $this->active->gr('id');
		$this->active->load->model('mall/mall_zxtg_model');
		$prd = $this->active->mall_zxtg_model->get_prd(
			$id,
			'*'
		);
		if( ! $prd ){
			show_error('找不到目标对象', 404);
			exit();
		}
		
		if( ! empty($prd['image']) ){
			$this->active->load->library('thumb');
			$prd['thumb'] = $this->active->thumb->crop($prd['image'], 135, 153);
		} else {
			$prd['thumb'] = false;
		}
		
		$this->active->tpl->assign('prd', $prd);
		$this->active->tpl_name = 'zhuanti/' . $this->active->zhuanti_name . '/worker_dialog.html';
	}
	
	// 设计师
	public function sjs_dialog(){
		$id = $this->active->gr('id');
		$this->active->load->model('mall/mall_zxtg_model');
		$prd = $this->active->mall_zxtg_model->get_prd(
			$id,
			'*'
		);
		if( ! $prd ){
			show_error('找不到目标对象', 404);
			exit();
		}
		$this->active->tpl->assign('prd', $prd);
		$this->active->tpl_name = 'zhuanti/' . $this->active->zhuanti_name . '/sjs_dialog.html';
	}	
	
	// 装修公司
	public function deco_dialog(){
		$id = $this->active->gr('id');
		$this->active->load->model('mall/mall_zxtg_model');
		$prd = $this->active->mall_zxtg_model->get_prd(
			$id,
			'*'
		);
		if( ! $prd ){
			show_error('找不到目标对象', 404);
			exit();
		}
		
		if( empty($prd['username']) ){
			show_error('找不到装修公司数据', 404);
			exit();
		}
		
		$decos = array($prd);
		
		$this->active->load->model('company/company', 'deco_model');
		$decos = $this->active->deco_model->fill_collection($decos, array(
			'fields'=>array('username', 'company', 'address', 'logo', 'koubei', 'flag', 'user_shen', 'user_city', 'user_town')
		));
		
		$deco = $decos[0];
		unset($decos);
		unset($prd);
		
		// 查找协会编号
		$this->active->load->model('sida/SidaModel', 'sida_model');
		$temp = $this->active->sida_model->code_assign_decos(
			array($deco['deco_info'])
		);
		
		$deco['deco_info'] = $temp[0];
		
		$this->active->tpl->assign('prd', $deco);
		$this->active->tpl_name = 'zhuanti/' . $this->active->zhuanti_name . '/deco_dialog.html';
	}
	
	// 报名表单提交
	public function bm_handler(){
		$object = array(
			'name'=>$this->active->gf('name'),
			'mobile'=>$this->active->gf('mobile'),
			'address'=>$this->active->gf('address'),
			'qq'=>$this->active->gf('qq'),
			'source'=>'',
			'tid'=>$this->active->gf('tid'),
			'validate'=>$this->active->gf('validate')
		);
		
		$source = $this->active->gf('source');
		
		if( ! empty( $source ) ){
			$object['source'] = array(
				'name'=>$source[0],
				'link'=>$source[1]
			);
		} else {
			$object['source'] = array( 'name'=>'早期活动', 'link'=>'' );
		}
		
		$object['source'] = json_encode($object['source']);
		
		$errors = array();
		
		if( ! $this->_check_validate($object['validate']) ){
			$errors[] = '验证码错误；2';
		}
		
		if( $object['name'] == '' ){
			$errors[] = '称呼不能为空';
		}
		
		if( $object['mobile'] == '' ){
			$errors[] = '手机号码不能为空';
		}
		
		if( $object['address'] == '' ){
			$errors[] = '联系地址不能为空';
		}
		
		if( $object['qq'] == '' ){
			$errors[] = 'QQ号码不能为空';
		}
		
		if( count($errors) == 0 ){
			$this->active->load->model('mall/mall_bm_model');
			$result = $this->active->mall_bm_model->add($object);
		} else {
			$result = json_encode( array('type'=>'error', 'data'=>$errors) );
		}
		
		echo($result);
	}

}

















?>