<?php

// 图库分类管理
class admin_album_category extends admin_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'home');
		$this->load->model('photo/album_category_model');
	}
	
	public function manage_old(){
		
		$module = $this->gr('tp');
		$module = $module == '' ? 'home' : $module;
		
		$tp = $module == 'home' ? 0 : 1;
		
		$cats = $this->album_category_model->get_list("select id, name, pid, hot from photo_category where pid = 0 and isgong = '". $tp ."'");
		$cats = $cats['list'];
		$cats = $this->album_category_model->assign_childs($cats);
		$this->tpl->assign('cats', $cats);
		$this->tpl->assign('module', $module);
		$this->tpl->display( $this->get_tpl('album/category/manage.html') );
		
	}
	
	// 分类添加 / 编辑
	public function active(){
		
		$this->tpl->assign('module', 'add');
		
		$info = array(
			'r'=>$this->gr('r'),
			'id'=>$this->gr('id')
		);
		
		$cat = false;
		
		if( $info['id'] != '' ){
			$cat = $this->album_category_model->get_cat($info['id'], array(
				'fields'=>'id, pid, name, name2, description, hot',
				'format'=>false
			));
			$this->tpl->assign('module', 'edit');
		}
		
		$parents = $this->album_category_model->getAllRoot();
		
		$this->tpl->assign('cat', $cat);
		$this->tpl->assign('rurl', $info['r']);
		$this->tpl->assign('parents', $parents);
		$this->tpl->display( $this->get_tpl('album/category/active.html') );
		
	}
	
	// 分类提交处理
	public function handler(){
		
		$info = $this->get_form_data();
		
		if( $info['name'] == '' ){
			$this->alert('名称不能为空');
			exit();
		}
		
		if( ! isset($info['hot']) ) $info['hot'] = 0;
		
		try{
			if( $info['id'] == '' ){		// 新增模式
				$this->album_category_model->add($info);
			} else {		// 修改模式
				$this->album_category_model->edit($info);
			}
			$this->alert('提交完成', $info['rurl']);
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
		
	}

	public function delete(){
		
		$info = array(
			'id'=>$this->gr('id'),
			'r'=>$this->gr('r')
		);
		
		try{
			$this->album_category_model->delete($info['id']);
			$this->alert('提交成功', $info['r']);
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
		
	}

	public function get_tp_struct(){
		
		$tp = $this->gf('tp');
		
		$config = array(
			'fields'=>'id, name, pid',
			'tp'=>'0'
		);
		
		$config['tp'] = ($tp == 'home') ? 0 : 1;
		
		$this->load->model('photo/album_category_model');
		$sql = "select ". $config['fields'] ." from [photo_category] where isgong = '". $config['tp'] ."' and target = 'album' and pid = 0";
		$parents = $this->album_category_model->get_list($sql);
		$parents = $this->album_category_model->assign_childs($parents['list'], array(
			'fields'=>'id, pid, name',
			'format'=>false
		));
		
		echo(json_encode($parents));
		
	}
	
	// 分类排序
	public function sorter(){
		
		$info = $this->get_form_data();
		$this->load->model('photo/album_category_model');
		
		try{
			$this->album_category_model->sorter(array(
				'pid'=>$info['pid'],
				'data'=>$info['data']
			));
			echo('success');
		}catch(Exception $e){
			echo( $e->getMessage() );
		}
		
	}

	// 第二版 标签库 管理
	public function manage() {
		
		$so = array(
			'key'=>$this->gr('key')
		);
		
		
		$where = array("pid <> 0");
		if( ! empty( $so['key'] ) ){
			$so['key'] = iconv("gbk", "utf-8", $so['key']);
			$where[] = "(name like '%". $so['key'] ."%' OR name2 like '%". $so['key'] ."%')";
		}
		
		$this->load->model('photo/album_category_model');
		$this->load->model('photo/album_image_model');
		
		$cats = $this->album_category_model->get_list("select id, name, pid, hot from [photo_category] where " . implode(" and ", $where), '', array(
			'format'=>false
		));
		
		$cats = $cats['list'];
		$cats = $this->album_image_model->count_assign($cats);
		
		// var_dump2($cats);
		
		$e = array();
		
		foreach($cats as $key=>$item){
			$cats[$key]['py'] = $this->get_py( $item['name'] );
			if( ! in_array($cats[$key]['py'], $e) ){
				$e[] = $cats[$key]['py'];
			}
		}
		sort($e);
		$data = array();
		foreach($e as $key=>$value){
			$data[$value] = array();
		}
		
		foreach($data as $key=>$value){
			foreach($cats as $item){
				if( $item['py'] == $key ) $value[] = $item;
			}
			$data[$key] = $value;
		}
		
		$this->tpl->assign('data', $data);
		$this->tpl->assign('module', 'manage');
		$this->tpl->assign('so', $so);
		$this->tpl->display( $this->get_tpl('album/category/manage_20160512.html') );
		
		
	}

	// 获取字母的首拼音
	
	private function get_py($str){
		
		$this->load->library('pinyin');
		$res = $this->pinyin->ChineseToPinyin($str);
		
		return strtoupper( $res[0] );
		
	}
	
	public function get_refs() {
		$label = '客厅';
		$this->load->model('photo/image_label_model');
		$this->image_label_model->ref($label);
	}




}

?>