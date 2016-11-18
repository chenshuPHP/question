<?php


// sv = supervision
// 我要申请监理
class multi_woyaojianli extends multi_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'woyao');
		$this->load->model('multi/supervision_model', 'sv_model');	// 监理模型
	}
	
	//public function v2(){
	//	$tpl = 'multi/supervision/woyaojianli_2015.html';
	//	$this->home( $tpl );
	//}
	
	public function home($tpl = 'multi/supervision/woyaojianli_2015.html'){
		$sv_types = $this->sv_model->get_types();
		
		$list = $this->sv_model->get_list("select top 10 id, type, rejion, category_b as t1, category_s as t2, town, area, addtime from [send_jianli] order by addtime desc");
		$list['list'] = $this->sv_model->type_assign($list['list']);
		
		// 监理日记
		$this->load->model('diary/diary_model');
		$this->load->model('diary/diary_extend_model');
		
		$sql = "select top 2 id, title, town, address, area, budget, deco_name, addtime, detail, image_count from diary order by addtime desc";
		$count_sql = "select count(*) as icount from diary";
		$diaries = $this->diary_model->get_list($sql, $count_sql);

		$diaries['list'] = $this->diary_extend_model->image_count_assign($diaries['list']);
		$diaries['list'] = $this->diary_extend_model->image_path_assign($diaries['list'], array('size'=>1));
		foreach($diaries['list'] as $key=>$val){
			$val['desc'] = $this->encode->get_text_description($val['detail'], 150, false);
			$diaries['list'][$key] = $val;
			unset($diaries['list'][$key]['detail']);
		}
		
		$this->load->model('article/article', 'article_model');
		$arts = $this->article_model->getCustomNews("select top 4 id, title, clsid, description, scontent, addtime from art_art where contains((keyword, title), '宋志浩') order by id desc");
		
		//echo('<!--');
		//var_dump($arts);
		//echo('-->');
		
		$this->tpl->assign('arts', $arts);
		
		$this->tpl->assign('diaries', $diaries['list']);
		$this->tpl->assign('list', $list['list']);
		
		$this->tpl->assign('sv_types', $sv_types);
		$this->tpl->display( $tpl );
	}
	
	// 输出验证码
	public function get_validate(){
		
		session_start();
		$this->load->library('kaocode');
		
		$this->kaocode->doimg();
		
		$_SESSION['woyaojianli_validate'] = $this->kaocode->getCode();
		
	}
	
	// 我要申请监理表单提交
	public function handler(){
		
		$info = $this->get_form_data();
		
		if( empty($info['rejion']) || empty($info['tel']) ){
			$this->alert('称呼，电话不能为空');
			exit();
		}
		
		if( ! $this->_check_validate($info['validate']) ){
			$this->alert('验证码错误');
			exit();
		}
		
		try{
			$this->sv_model->add($info);	// 添加我要监理申请
			$this->_clear_validate();
			$this->alert('提交成功', $this->multi_info_model->get_complete_url('woyaojianli'));
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
		
	}
	
	
	private function _clear_validate(){
		if(! isset($_SESSION)) session_start();
		$_SESSION['woyaojianli_validate'] = NULL;
	}
	
	private function _check_validate($code){
		session_start();
		if( empty( $_SESSION['woyaojianli_validate'] ) ) return false;
		return strtolower($code) == strtolower($_SESSION['woyaojianli_validate']);
	}
	
	// ajax 发送验证码验证请求，正确输出1 错误 输出0
	public function check_validate(){
		echo( $this->_check_validate( $this->gf('code') ) ? 1 : 0 );
	}
	
}

?>