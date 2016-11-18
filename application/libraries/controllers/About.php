<?php

	if(! defined('BASEPATH')) exit(' basepath error!');
	
	class About extends MY_Controller{
		function __construct(){
			
			parent::__construct();
		}
		
		// 关于我们首页
		function index(){
			$this->tpl->display('about/index.html');
		}
		
	
		//商务合作 by袁仙增 20160805
		function advert(){
			$this->tpl->display('about/advert.html');
		}
		
		public function jiameng()
		{
			$this->tpl->display('about/jiameng.html');
		}
		
		// 关于我们 广告服务
		function ads($id=''){
			
			//$this->load->model('ad');
			//$content = $this->ad->getAboutAd($id);
			//$this->tpl->assign('content',$content);
			$this->tpl->display('about/ads.html');
			
		}
		
		// 网站建设
		function website(){
			//$this->tpl->display('head.tpl');
			$this->tpl->display('about/website.tpl');
		}
		
		// 友情链接
		function link(){
			$this->tpl->caching = true;
			$this->tpl->cache_lifetime = 60*60*0.5;	// 0.5 小时
			$cache_dir = $this->tpl->cache_dir . 'about/';	// 设置这个模版的缓存目录
			$this->tpl->cache_dir = $cache_dir;
			//$this->tpl->clearCache('about/link.html');
			if(! $this->tpl->isCached('about/link.html') ){
				
				if(! isset($this->mdb)) $this->load->library('mdb');
				
				$this->load->model('Firlink', 'firlink');
				$result = $this->firlink->getMoreLinks();	// 获取首页更多， 友情链接
				$this->tpl->assign('data', $result);
				echo('<!-- caching 0.5小时更新 -->');
				
				
			}
			$this->tpl->display('about/link.html');
		}
		
		// 网站导航
		function webdao(){
			$this->tpl->display('about/webdao.tpl');
		}
		
		// 服务条款
		function service(){
			$this->tpl->display('about/service.html');
		}
		
		// 银行信息
		function blanks(){
			$this->tpl->display('about/blanks.tpl');
		}
		
		// 法律声明
		function legal(){
			$this->tpl->display('about/legal.html');
		}
		
		//招聘
		function talents(){
			
			$this->tpl->caching = true;
			$this->tpl->cache_lifetime = 36000; // 缓存10小时
			$cache_dir = $this->tpl->cache_dir . 'about/';	// 设置这个模版的缓存目录
			$this->tpl->cache_dir = $cache_dir;
			
			if(! $this->tpl->isCached('about/talents.html') ){
				// 标记开始时间戳
				//$this->benchmark->mark('code_start');
				
				if(! isset($this->mdb)) $this->load->library('mdb');
				
				$this->load->model('about/Act', 'about');
				$data = $this->about->talents();
				$this->tpl->assign('data', $data);
				//$this->benchmark->mark('code_end');
				/*
					显示页面执行时间,
					可以在视图中直接调用 $this->benchmark->mark() 来显示完全执行时间，
					但我们这里使用Smarty模版引擎，所以用不了
				*/
				//echo('<!--' . $this->benchmark->elapsed_time('code_start', 'code_end') . ';' . 'create caching!' . '-->');
			}
			//else {
			//	echo('123');
			//}
			$this->tpl->display('about/talents.html');
			//显示调试信息
			//$this->output->enable_profiler(TRUE);
		}

	// ================= 联系我们 =================
	// 联系我们
	function relation(){
		$this->tpl->display('about/relation.html');
	}
	
	// 联系我们验证码
	public function get_relation_validate_code(){
		session_start();
		$this->load->library('kaocode');
		$this->kaocode->doimg();
		$_SESSION['relation_validate'] = $this->kaocode->getCode();
	}
	
	// 2016-08-05 重写此方法
	public function relation_submit()
	{
		$info = $this->get_form_data();
		
		$this->load->model('about/Act', 'about_model');
		
		$result = array();
		$result['error'] = array();
		
		// 检测验证码
		if( ! $this->_relation_validate_check($info['validate']) )
		{
			$result['error'][] = '验证码错误';
		}
		$this->_reset_relation_validate();	// 重置验证码防止重复提交
		
		if( ! isset( $info['fullname'] ) || ! isset( $info['tel'] ) || ! isset( $info['content'] ) || ! isset( $info['type'] ) )  
		{
			$result['error'][] = '业务类型,称呼,电话,内容不能为空';
		}
		else
		{
			if( $info['fullname'] == '' || $info['tel'] == '' || $info['content'] == '' || $info['type'] == '' )
			{
				$result['error'][] = '业务类型,称呼,电话,内容不能为空!';
			}
		}
		
		$id = 0;
		
		if( count( $result['error'] ) == 0 )
		{
			try
			{
				$id = $this->about_model->relationSubmit( $info );
			}
			catch(Exception $e)
			{
				$result['error'][] = $e->getMessage();
			}
		}
		
		if( count( $result['error'] ) == 0 && $id != 0 )
		{
			$result = array('success'=>1, 'id'=>$id);
		}
		
		if( isset( $info['ajax'] ) && $info['ajax'] == 1 )
		{
			echo( json_encode( $result ) );
		}
		else
		{
			// var_dump($result);
			$this->tpl->assign('result', $result);
			$this->tpl->display('about/relation.html');
		}
		
	}



	// 联系我们提交
	/*
	function relation_submit(){
		
		// == 2015-02-28 新增验证码验证
		$validate = $this->gf('validate');

		if( ! preg_match('/^[0-9a-zA-Z]{4}$/', $validate) ){
			$this->alert('验证码格式错误');
			exit();
		} else {
			if( ! $this->_relation_validate_check($validate) ){
				$this->alert('验证码错误');
				exit();
			}
		}

		$this->_reset_relation_validate();	// 重置验证码防止重复提交

		//  == end

		$this->load->model('about/Act','about');
		$result = $this->about->relationSubmit();
		
		if( $this->gf('ajax') != 1 )
		{
			$this->tpl->assign('result', $result);
			$this->tpl->display('about/relation.html');
		}
		else
		{
			if( ! isset( $result['err'] ) )
			{
				$result['success'] = 1;
			}
			echo( json_encode( $result ) );
		}
		
	}
	*/
	
	// 2015-02-28 新增验证码安全验证
	private function _relation_validate_check($code = ''){
		
		session_start();

		$code = strtolower($code);
		$server_code = strtolower( $_SESSION['relation_validate'] );

		if( empty( $server_code ) ) return false;

		if( $code == $server_code ){
			return true;
		}

		return false;
	}

	// 重置验证码
	private function _reset_relation_validate(){
		// session_start();
		$_SESSION['relation_validate'] = '';
	}
	
	// 客户端验证
	public function relation_validate_check(){
		$code = $this->gf('validate');
		
		if( ! $this->_relation_validate_check($code) ){
			echo(0);
		} else {
			echo(1);
		}
	}
	// ===========================================

}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
?>