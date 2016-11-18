<?php

// 线下体验馆，装修样板房
class archive_sample extends archive_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _my_remap($method, $args = array()){
		
		switch(true){
			//case preg_match('/home/', $method):
			//	$this->$method();
			//	break;
			case preg_match('/detail\-\d+/', $method):
				$temp = explode('-', $method);
				$method = array_shift($temp);
				$this->$method($temp);
				break;
			case preg_match('/view\-\d+/', $method):
				$temp = explode('-', $method);
				$method = array_shift($temp);
				$this->$method($temp);
				break;
			case preg_match('/image\-\d+/', $method):
				$temp = explode('-', $method);
				$method = array_shift($temp);
				$this->$method($temp);
				break;
			default:
				if( method_exists($this, $method) ){
					$this->$method();
				} else {
					show_404();
				}
		}
		
		//echo('<!-- _my_remap -->');
		
	}
	
	public function home(){
		
		
		$this->tpl->cache_lifetime = 20*60;	// 20分钟
		$cache_dir = $this->tpl->cache_dir . 'archive/sample/';	// 设置这个模版的缓存目录
		$this->tpl->cache_dir = $cache_dir;
		
		//if( $this->gr('v2') == '1' ){
			$tpl = 'archive/sample/home2015.html';
			$this->tpl->caching = true;
		//} else {
		//	$tpl = 'archive/sample/home.html';
		//	$this->tpl->caching = true;
		//}
		
		if(! $this->tpl->isCached($tpl) ){
		
		$this->load->library('thumb');
		
		$this->load->model('archive/archive_cas_model');
		$this->load->model('archive/archive_album_model');
		$this->load->model('mall/mall_brand_model');
		$this->load->model('company/company', 'deco_model');
		
		$samples = $this->archive_cas_model->get_list('select id, tid, name, huxing, area, budget, fm, style, wages, video, detail from archive_cas where sample = 1 order by name Asc');
		$samples = $samples['list'];
		$samples = $this->archive_cas_model->format_batch_sample($samples);
		$samples = $this->archive_album_model->image_count_assign_albums($samples);	// 获取图片数量
		
		foreach($samples as $key=>$value){
			$samples[$key]['thumb'] = $this->thumb->crop($value['fm'], 330, 190);
		}
		
		/*
		$brands = $this->mall_brand_model->get_list("select top 6 id, brand, image from mall_brandlib where image <> '' order by id desc");
		$brands = $brands['list'];
		foreach($brands as $key=>$value){
			$brands[$key]['thumb'] = $this->thumb->resize($value['image'], 115, 40);
		}
		
		$decos = $this->deco_model->get_list("select top 8 username, company, shortname from company where delcode = 0 order by koubei desc", '', true);
		$decos = $decos['list'];
		
		// 预约客户
		$this->load->model('publish/deco_orders_model', 'deco_orders_model');
		$biz_count = $this->deco_orders_model->get_count("select count(*) as icount from sendzh");
		
		$biz = $this->deco_orders_model->get_deco_order_list("select top 5 town, sroom as room, housearea, budget, fullname, addtime from sendzh where hide = 0 order by id desc");
		
		$this->tpl->assign('biz_count', $biz_count);
		$this->tpl->assign('biz', $biz['list']);
		*/
		$this->tpl->assign('samples', $samples);
		//$this->tpl->assign('brands', $brands);
		//$this->tpl->assign('decos', $decos);
		$this->tpl->display($tpl);
		
		} else {
			$this->tpl->display($tpl);
			echo('<!-- cached -->');
		}
		
	}
	
	public function detail($args = array()){
		
		//if( $this->gr('v2') == 1 ){
			$this->tpl->caching = true;
			$tpl = 'archive/sample/detail2015.html';
		//} else {
		//	$this->tpl->caching = true;
		//	$tpl = 'archive/sample/detail.html';
		//}
		
		$id = $args[0];
		
		//$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60 * 60 * 12; 			// 单位 分钟, 缓存12小时
		$this->tpl->cache_dir .= 'archive/sample/view/';	// 缓存目录
		
		if(! $this->tpl->isCached($tpl, $id) ){
			$this->load->model('archive/archive_cas_model');
			$this->load->model('archive/archive_album_model');
			$this->load->model('archive/archive_prod_model');
			
			$this->load->library('thumb');
			
			$sample = $this->archive_cas_model->get_cas($id, 'id, address, name, community, huxing, area, budget, detail, fm, sample, style, wages, video');
			
			$sample['stages'] = $this->archive_album_model->get_album_stages( $sample['id'] );
			$sample['stages'] = $this->archive_album_model->assign_images( $sample['stages'] );
			$sample['prds'] = $this->archive_prod_model->get_cas_prds( $sample['id'] );
			
			$sample = $this->archive_cas_model->format_sample( $sample );
			
			$samples = $this->archive_cas_model->get_list('select top 7 id, name, tid, fm, style, wages from archive_cas where sample = 1 and id <> '. $sample['id'] .' order by addtime desc');
			$samples = $samples['list'];
			$samples = $this->archive_cas_model->format_batch_sample($samples);
			//foreach($samples as $key=>$value){
			//	$samples[$key]['style'] = str_replace('风格', '', $value['style']);
			//}
			
			// 滚动图片
			$images = array();
			foreach($sample['stages'] as $stage){
				foreach($stage['images'] as $image){
					//if( count( $images ) == 4 ) break;
					$image['thumb'] = $this->thumb->crop($image['image'], 381, 249);
					$images[] = $image;
				}
			}
			
			$this->load->model('mobile/mobile_url_model');
			$sample = $this->mobile_url_model->format_single_sample($sample);
			
			$this->tpl->assign('sample', $sample);
			$this->tpl->assign('samples', $samples);
			$this->tpl->assign('images', $images);
			
			$this->tpl->display( $tpl, $id );
		} else {
			$this->tpl->display( $tpl, $id );
			echo('<!-- cached -->');
		}
		
		
	}
	
	// 图片浏览
	public function image( $args = array() ){
		
		$id = $args[0];
		
		$this->load->model('archive/archive_cas_model');
		$this->load->model('archive/archive_album_model');
		
		$image = $this->archive_album_model->get_image($id, 'id, stage_id, image, image_name, description');
		
		$sample = $this->archive_cas_model->get_album_by_image($image['id'], 'id, tid, huxing, area, budget, fm, style, wages');
		$sample = $this->archive_cas_model->format_sample($sample);
		
		$sample['stages'] = $this->archive_album_model->get_album_stages( $sample['id'] );
		$sample['stages'] = $this->archive_album_model->assign_images( $sample['stages'] );
		
		
		$this->tpl->assign('current', $id);
		$this->tpl->assign('sample', $sample);
		$this->tpl->display('archive/sample/image.html');
	}
	
	
	public function validate(){
		session_start();
		$this->load->library('kaocode');
		$this->kaocode->doimg();
		$_SESSION['sample_visit_validate'] = $this->kaocode->getCode();
	}
	
	// 客户端检测验证码
	public function check_validate(){
		$validate = $this->gf('validate');
		echo( $this->_check_validate($validate) ? 1 : 0 );
	}
	
	private function _check_validate($validate){
		session_start();
		if( empty($validate) ) return false;
		return strtolower($validate) == strtolower( $_SESSION['sample_visit_validate'] );
	}
	
	public function sign(){
		$this->tpl->display('archive/sample/sign.html');
	}
	
	
	// 数据提交
	public function handler(){
		$visit = array(
			'name'=>$this->gf('name'),
			'mobile'=>$this->gf('tel'),
			'tid'=>$this->gf('sample_id'),
			'address'=>'',
			'validate'=>$this->gf('validate')
		);
		
		if( empty($visit['name']) || empty($visit['mobile']) ){
			exit('数据不完整');
		}
		
		if( ! $this->_check_validate($visit['validate']) ){
			exit('验证码错误');
		}
		
		$this->load->model('archive/archive_visit_model');
		
		try{
			
			$this->archive_visit_model->add($visit);
			echo('success');
			
		}catch(Exception $e){
			echo($e->getMessage());
		}
	}
	
	// 装修信息快速提交
	public function sendzh(){
		
		$info = array(
			'name'=>$this->gf('name'),
			'mobile'=>$this->gf('mobile'),
			'state'=>$this->gf('state'),
			'validate'=>$this->gf('validate')
		);
		
		if( ! $this->_check_validate($info['validate']) ){
			exit('验证码错误');
		}
		
		if( empty($info['name']) || empty($info['mobile']) ){
			exit('信息不完整');
		}
		
		$this->load->model('publish/pubModel', 'pub_model');
		
		try{
			
			$urls = $this->config->item('url');
			
			$url = rtrim($urls['archive'], '/') . '/sample';
			
			$this->pub_model->express_pipe_add(array(
				'true_name'=>$info['name'],
				'tel'=>$info['mobile'],
				'rel'=>$this->encode->htmlencode( '{"url":"'. $url .'", "name":"样板房599装修优惠报名"}' ),
				'ps'=>$this->encode->htmlencode('[{"key":"房屋状态", "value":"'. $info['state'] .'"}]'),
				'addtime'=>date('Y-m-d H:i:s')
			));
			
			echo('success');
			
		}catch(Exception $e){
			exit($e->getMessage());
		}
		
		
	}
}






































?>