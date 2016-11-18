<?php

if( ! defined('BASEPATH') ) exit('禁止直接浏览');

class mobile_mall extends mobile_base {

	public function __construct(){
		parent::__construct();
	}

	public function _remap($params){
		
		$method = array_shift($params);

		if( preg_match('/^\d+$/', $method) ){
			$this->page($method);
		} else {
			if( method_exists($this, $method) ){
				if( count($params) > 0 ){
					$this->$method($params);
				} else {
					$this->$method();
				}
			}
		}

	}
	
	private function _get_res_url(){
		return 'http://m-365zxtg.app2biz.com/';
	}

	private function _build_url($page_id){
		return 'http://m-365zxtg.app2biz.com/activity_'. $page_id .'.html';
	}

	private function __build_shzh_url($page_id){
		return 'http://www.shzh.net/mobile/mall/' . $page_id;
	}

	private function _get_contents($url){
		$string = @file_get_contents($url);
		return $string;
	}

	private function _complete_replace($pattern, $content){
		preg_match_all($pattern, $content, $matchs);
		foreach($matchs[0] as $key=>$item){
			$new_item = str_replace($matchs[1][$key], $this->_get_complete_url($matchs[1][$key]), $item);
			$content = str_replace($item, $new_item, $content);
		}
		return $content;
	}

	private function _link_replace($content){
		preg_match_all('/<a[^>]*href=[\'\"](http:\/\/.*?activity\_(\d+?)\.html.*?)[\'\"][^>]*?\/?>/i', $content, $matchs);

		foreach($matchs[0] as $key=>$value){
			$new_url = $this->__build_shzh_url($matchs[2][$key]);
			$new_tab = str_replace($matchs[1][$key], $new_url, $value);
			$content = str_replace($matchs[0][$key], $new_tab, $content);
		}

		return $content;
	}

	private function _set_charset($content, $charset='UTF-8'){
		$content = str_replace('<meta charset="utf-8" />', '<meta http-equiv="Content-Type" content="text/html; charset='. $charset .'"/>', $content);
		return $content;
	}

	private function format($content){

		// 替换链接
		$content = $this->_link_replace($content);

		// 补全资源url
		$content = $this->_complete_replace('/<script[^>]*?src=[\'\"](.*?)[\'\"][^>]*?\/?>/i', $content);
		$content = $this->_complete_replace('/<link[^>]*?href=[\'\"](.*?)[\'\"][^>]*?\/?>/i', $content);
		$content = $this->_complete_replace('/<img[^>]*?src=[\'\"](.*?)[\'\"][^>]*?\/?>/i', $content);
		$content = $this->_complete_replace('/background:url\(([^\)]*?)\)/i', $content);

		// 替换编码, 默认 utf-8
		$content = $this->_set_charset($content);


		return $content;
	}

	private function _get_complete_url($path){
		if( preg_match('/^(http:\/\/|ftp:\/\/|tel:|https:\/\/|javascript:).*$/i', $path) ){
			return $path;
		}
		return $this->_get_res_url() . ltrim($path, '/');
	}

	private function _get_head_content($content){
		
		preg_match_all('/<head[^>]*>(.*)<\/head>/is', $content, $matchs);

		return $matchs[1][0];
	}

	private function _get_body_content($content){
		
		preg_match_all('/<body[^>]*>(.*)<\/body>/is', $content, $matchs);

		return $matchs[1][0];
	}

	// 移除表单
	private function _remove_login_form($content){

		$pattern = '/<script[^<]*?(WdatePicker\.js|var\scloseInterval_).*?<\/script>/is';
		$content = preg_replace($pattern, '', $content);
		$content = preg_replace('/<style[^<].*?>\s*<\/style>/is', '', $content);

		
		$content = preg_replace('/<link[^<]*?editor_ul\.css.*?\/?>/is', '', $content);
		

		$content = preg_replace('/onclick="saveParticipate[^(]*?\(\);"/is', 'onclick="bm_form_send();"', $content);
		return $content;

	}

	// 移除 统计代码
	private function _remove_stats($content){
		$pattern = '/<script[^<]*?stats\.jsp.*?<\/script>/is';
		$content = preg_replace($pattern, '', $content);
		return $content;
	}

	public function page($page_id){

		$url = $this->_build_url($page_id);

		$content = $this->_get_contents($url);
		$content = $this->format($content);
		$content = $this->_remove_login_form($content);

		$content = $this->_remove_stats($content);
		
		$head = $this->_get_head_content($content);
		$body = $this->_get_body_content($content);

		unset($content);

		$this->tpl->assign('head', $head);
		$this->tpl->assign('body', $body);
		$this->tpl->assign('page_id', $page_id);

		$this->tpl->display('mobile/mall/page.html');
	}

	// 表单提交处理
	public function page_form_submit(){
		
		$object = array(
			'page_id' => $this->gf('page_id'),
			'page_name' => $this->gf('page_name'),
			'data' => json_encode( $_POST['data'] ),
			'addtime' => date('Y-m-d H:i:s'),
			'ip' => $this->encode->get_ip(),
			'agent' => $this->encode->htmlencode( $_SERVER['HTTP_USER_AGENT'] )
		);

		$result = array();

		if( empty($object['page_id']) ){
			$result['type'] = 'error';
			$result['message'] = 'page_id不能为空';
		} else {
		
			$object['data'] = $this->encode->htmlencode( $object['data'] );

			$this->load->model('mobile/mobile_mall_model');

			try{
				$id = $this->mobile_mall_model->add($object);
				$result['type'] = 'success';
			}catch(Exception $e){
				$result['type'] = 'error';
				$result['message'] = $e->getMessage();
			}

		}

		echo(json_encode($result));

	}

}


?>