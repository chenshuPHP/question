<?php

class jiancai extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
		//$this->tpl->assign('hide_kf', '1');
	}
	
	private function check_mobile_request(){
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		$agent_types = array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly " ,"fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
		foreach($agent_types as $val){
			if( strpos($agent, $val) != false ){
				return true;
			}
		}
		return false;
	}
	
	//public function index(){
	//	if( $this->check_mobile_request() == false ){
	//		$this->tpl->display('zxtg/zhuanti/2014.11.01.html');
	//	} else {
	//		$this->alert('', 'http://m-365zxtg.app2biz.com/activity_11980.html');
	//	}
	//}
	
	// 第二场活动预告
	//public function mjcsjt(){
	//	$this->tpl->display('zxtg/zhuanti/2014.11.18.html');
	//}
	
	// 报名处理
	public function bm(){
		
		$object = array(
			'name'=>$this->gf('name'),
			'mobile'=>$this->gf('mobile'),
			'address'=>$this->gf('address'),
			'qq'=>$this->gf('qq'),
			'source'=>'',
			'tid'=>$this->gf('tid')
		);
		
		$source = $this->gf('source');
		
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
			$this->load->model('mall/mall_bm_model');
			$result = $this->mall_bm_model->add($object);
		} else {
			$result = json_encode( array('type'=>'error', 'data'=>$errors) );
		}
		
		echo($result);
		
	}
}

?>