<?php

// 城市分站管理 - 城市管理
// 2015-06-09
class admin_sc_city extends admin_base {
	
	public function __construct(){
		parent::__construct();
		
		$this->load->model('city_model');
		
	}
	
	public function manage(){
		$tree = $this->city_model->tree();
		$this->tpl->assign('tree', $tree);
		$this->tpl->assign('module', 'manage');
		$this->tpl->display( $this->get_tpl('sc/city/manage.html') );
	}
	
	public function settings(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		$city = $this->city_model->get($id);
		
		//echo('<!--');
		//var_dump($city);
		//echo('-->');
		$this->tpl->assign('module', 'edit');
		$this->tpl->assign('city', $city);
		$this->tpl->assign('rurl', $r);
		$this->tpl->display( $this->get_tpl('sc/city/settings.html') );
		
	}
	
	public function handler(){
		
		$info = $this->get_form_data();
		if( empty($info['name']) || empty($info['label']) ){
			exit('名称和标签不能为空');
		}
		
		if( ! isset($info['isopen']) ){
			$info['isopen'] = 0;
		}
		
		try{
			$this->city_model->edit($info);
			$this->city_model->clear();	// 更新缓存
			$this->alert('修改成功', $info['rurl']);
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
		
		
	}
	
}

?>