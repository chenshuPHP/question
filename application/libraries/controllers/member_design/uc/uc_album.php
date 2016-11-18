<?php

// 设计师案例管理
class uc_album extends uc_base {
	
	public function __construct(){
		parent::__construct();
		$this->tpl->assign('module', 'album');
	}
	
	public function manage(){
		$this->load->model('sjs/sjs_case', 'sjs_case');
		$list = $this->sjs_case->get_case_list("select id, username, case_name, fm, addtime from sjs_case where username = '". $this->info['username'] ."' order by id desc");
		$this->tpl->assign('list', $list);
		
		$this->tpl->display( $this->get_tpl('album/uc_album_manage.html') );
		
	}
	
	// 案例添加界面
	public function upload(){
		
		$id = $this->gr('id');
		
		$this->load->model('sjs/sjs_case_config_model');
		
		// 加载房屋类型 ID = 2
		$pts = $this->sjs_case_config_model->get(2);
		$pts = $this->sjs_case_config_model->assign_childs($pts);
		$pts = $this->sjs_case_config_model->assign_childs_childs($pts);
		
		// 默认选择 家庭类型 ID = 15
		$default_pt_id = 15;
		$types = NULL;
		
		if( $id != '' ){
			
			$this->load->model('sjs/sjs_case');
			$case = $this->sjs_case->get($id);
			
			// 获取案例所遇分类
			$type_id = $case['type_id'];
			if( ! empty($type_id) ){
				$case_type = $this->sjs_case_config_model->get($type_id);
				$default_pt_id = $case_type['pid'];
			}
			
			$this->sjs_case->image_assign($case);
			
			// var_dump($case);
			$this->tpl->assign('case', $case);
			
		}
		
		
		foreach($pts['childs'] as $item){
			if( $item['id'] == $default_pt_id ) $types = $item;
		}
		// 风格类型 ID = 1
		$style_cats = $this->sjs_case_config_model->get(1);
		$style_cats = $this->sjs_case_config_model->assign_childs($style_cats);
		
		$this->tpl->assign('default_pt_id', $default_pt_id);
		$this->tpl->assign('pts', $pts);
		$this->tpl->assign('types', $types);
		$this->tpl->assign('style_cats', $style_cats);
		
		$this->tpl->display( $this->get_tpl('album/uc_album_upload.html') );
		
		
	}
	
	// 案例上传提交
	public function upload_handler(){
		
		$this->load->model('sjs/sjs_case');
		
		$info = $this->get_form_data();
		
		$info['username'] = $this->info['username'];
		
		try{
			if( $info['id'] == '' ){
				$this->sjs_case->add($info);
			} else {
				$this->sjs_case->edit($info);
			}
			$this->alert('提交成功', $this->get_complete_url('/album/manage'));
		}catch(Exception $e){
			throw new Exception( $e->getMessage() );
		}
		
	}
	
	public function delete(){
		$id = $this->gr('id');
		$this->load->model('sjs/sjs_case');
		$case = $this->sjs_case->get($id, array(
			'username'=>$this->info['username'],
			'format'=>false
		));
		
		$this->sjs_case->image_assign($case);
		
		if( $case['images'] != false ){
			$this->alert('案例内有图片，无法删除');
		} else {
			try{
				$this->sjs_case->delete($id);
				$this->alert('删除成功', $this->get_complete_url('/album/manage'));
			}catch(Exception $e){
				$this->alert($e->getMessage());
			}
		}
		
	}
	
	
	/*
	public function upload(){
		
		$this->load->model('sjs/sjs_case', 'sjs_case');
		$id = $this->gr('id');
		
		if( $id != '' && is_numeric($id) ){
			// 编辑模式
			$object = $this->sjs_case->get_case($id);
			$this->tpl->assign('object', $object);
		}
		
		$this->tpl->assign('category', $this->sjs_case->case_category_enum());
		$this->tpl->assign('price', $this->sjs_case->price_enum());
		$this->tpl->assign('sp_style', $this->sjs_case->style_enum());
		$this->tpl->assign('source', $this->sjs_case->source_enum());
		
		$this->tpl->display( $this->get_tpl('album/uc_album_upload.html') );
	}
	*/
	
	
	// 案例客户端图片上传处理
	// 本处案例上传不通过/common_upload/xxx 来处理
	// 原先由 /member_design/upload/case_image_upload.html ==> /controller/member_design/image_upload.php/case_image_upload 处理，
	// 原处理代码可以删除
	public function case_image_upload(){
		$this->load->model('sjs/sjs_case', 'sjs_case');
		
		$image_url = $this->sjs_case->case_image_upload();	// 返回 url
		
		if($image_url !== false){
			ob_clean();				// 清除未知的BOM => { %EF%BB%BF }
			echo($image_url);
		} else {
			echo('0');
		}
	}
	
	// 添加/编辑提交
	/*
	public function upload_handler(){

		$object = array();
		$this->load->library('encode');
		
		$object['id'] = $this->encode->getFormEncode('id');
		
		$object['username'] = $this->info['username'];
		$object['case_name'] = $this->encode->getFormEncode('case_name');
		$object['category_01'] = $this->encode->getFormEncode('btype');
		$object['category_02'] = $this->encode->getFormEncode('casesj_type');
		$object['category_03'] = $this->encode->getFormEncode('casesj_smalltype');
		$object['User_Shen'] = $this->encode->getFormEncode('User_Shen');
		$object['User_City'] = $this->encode->getFormEncode('User_City');
		$object['caseprice'] = $this->encode->getFormEncode('caseprice');
		$object['area'] = $this->encode->getFormEncode('area');
		$object['artstyle'] = $this->encode->getFormEncode('artstyle');
		
		$object['image_url'] = $this->encode->getFormEncode('image_url');
		$object['order'] = $this->encode->getFormEncode('order');
		$object['image_name'] = $this->encode->getFormEncode('image_name');
		$object['image_description'] = $this->encode->getFormEncode('image_description');
		
		$object['detail'] = $this->encode->getFormEncode('detail');
		$object['source'] = $this->encode->getFormEncode('source');
		
		//var_dump($object);
		//exit();
		
		$this->load->model('sjs/sjs_case', 'sjs_case');
		
		try{
			
			if( empty($object['id']) == true ){
				$caseId = $this->sjs_case->case_add($object);
				echo('<script type="text/javascript">location.href="'. $this->ucenter_url .'/album/manage";</script>');
			} else {
				$caseId = $object['id'];
				$this->sjs_case->case_edit($object);
				echo('<script type="text/javascript">alert("修改成功");location.href="'. $this->ucenter_url .'/album/upload?id='. $caseId .'"</script>');
			}
			
		}catch(Exception $e){
			exit($e->getMessage());
		}

	}
	*/
	
	// 删除
	/*
	public function delete(){
		$id = $this->gr('id');
		
		echo($id);
		
		$this->load->model('sjs/sjs_case', 'sjs_case');
		$this->sjs_case->case_delete($id);
		//echo('<script type="text/javascript">location.href="'. $this->ucenter_url .'/album/manage";</script>');
	}
	*/
	
	
}

?>