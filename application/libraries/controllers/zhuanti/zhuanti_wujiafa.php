<?php

class Zhuanti_wujiafa extends zhuanti_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function handler(){
		
		$this->active->load->model('article/article', 'article_model');
		$arts = $this->active->article_model->getCustomNews("select top 10 id, title, clsid, addtime from art_art where contains((keyword, title), '伍嘉发') order by id desc");
		//var_dump($arts);
		$this->active->tpl->assign('arts', $arts);
		$this->active->tpl_name = 'zhuanti/wujiafa/home.html';
	}
	
	public function validate(){
		
		session_start();
		
		$this->active->load->library('kaocode');
		$this->active->kaocode->doimg();
		
		$_SESSION['zhuanti_wujiafa_validate'] = $this->active->kaocode->getCode();
		
		$this->active->tpl_name = '';
	}
	
	private function get_validate(){
		$this->cancel_tpl();
		session_start();
		$validate  = '';
		if( isset( $_SESSION['zhuanti_wujiafa_validate'] ) ){
			$validate = $_SESSION['zhuanti_wujiafa_validate'];
		}
		return strtolower( $validate );
	}
	
	public function form_handler(){
		
		$this->cancel_tpl();
		
		$data = array(
			'name'=>$this->active->gf('name'),
			'mobile'=>$this->active->gf('mobile'),
			'validate'=>strtolower( $this->active->gf('validate') )
		);
		
		$validate = $this->get_validate();
		$errors = array();
		
		if( $validate != $data['validate'] ){
			$errors[] = '验证码错误';
		}
		
		if( empty($data['name']) || empty( $data['mobile'] ) ){
			$errors[] = '请输入姓名和联系电话';
		}
		
		$zhuanti = $this->active->zhuanti_info;
		
		if( count($errors) == 0 ){
			$this->active->load->model('publish/pubModel', 'pub_model');
			try{
				$this->active->pub_model->express_pipe_add(array(
					'true_name'=>$data['name'],
					'tel'=>$data['mobile'],
					'rel'=>$this->active->encode->htmlencode( '{"url":"'. $zhuanti['link'] .'", "name":"伍嘉发风水专题"}' )
				));
				echo( json_encode( array('type'=>'success') ) );
			}catch(Exception $e){
				$errors[] = $e->getMessage();
			}
		}
		
		if( count($errors) > 0 ){
			echo( json_encode( array('type'=>'err', 'message'=>$errors) ) );
		}
		
		
	}
	
}

?>