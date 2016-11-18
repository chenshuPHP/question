<?php
class mobile_biz extends mobile_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function index(){
		
		$tpl = $this->get_tpl('biz/home.html');
		
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60 * 60 * 2;	// 缓存2小时
		$this->tpl->cache_dir = $this->get_cache_dir("biz");
		
		if( ! $this->tpl->isCached($tpl) ){
			$this->load->model('publish/pubModel', 'pub_model');
			$count = $this->pub_model->get_total_count();	
			$this->tpl->assign('count', $count['count2']);
			$this->tpl->assign('title', '装修设计方案');
			$this->tpl->display( $tpl );
		} else {
			$this->tpl->display( $tpl );
			echo('<!-- cached -->');
		}
		
	}
	
	public function send_submit(){
		
		$this->load->library('encode');
		$this->load->model('publish/pubModel', 'pub_model');
		$object = array();
		$object['tel'] = $this->encode->getFormEncode('tel');
		$object['true_name'] = $this->encode->getFormEncode('name');
		$object['shen'] = $this->encode->getFormEncode('User_Shen');
		$object['city'] = $this->encode->getFormEncode('User_City');
		$object['town'] = $this->encode->getFormEncode('User_Town');
		$object['area'] = $this->encode->getFormEncode('area');
		$object['budget'] = $this->encode->getFormEncode('budget');
		
		$object['rel'] = $this->gf('rel');
		
		if( empty( $object['rel'] ) ){
			$object['rel'] = '{"url":"'. $this->mobile_url .'biz", "name":"触屏版-发布招标"}';
		} else {
			$object['rel'] = $this->gf('rel');
		}
		
		$ptype = $this->gf('ptype');
		
		$error = '';
		
		if( $object['tel'] != '' && $object['true_name'] != '' ){
			$this->pub_model->express_pipe_add($object);
		} else {
			$error = '您提交的信息不完整';
		}
		
		if( $ptype == 'ajax' ){
			
			if( $error == '' ){
				echo( 'success' );
			} else {
				echo( $error );
			}
			
		} else {
			
			if($error == ''){
				echo('<script type="text/javascript">alert("添加成功!");location.href="'. $this->mobile_url .'biz";</script>');
			} else {
				exit($error);
			}
			
		}
		
		
	}
}
?>