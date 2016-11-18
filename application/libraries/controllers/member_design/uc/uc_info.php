<?php

// 用户资料修改
class uc_info extends uc_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 修改资料界面
	public function edit(){
		
		$infomation = $this->infomation->getInfomation( $this->info['username'] );
		
		$this->tpl->assign('infomation', $infomation);
		$this->tpl->assign('wyear_enum', $this->infomation->get_wyear_enum());
		$this->tpl->assign('xueli_enum', $this->infomation->get_xueli_enum());
		// $this->tpl->assign('type_enum', $this->sjs_info_model->get_type_enum());
		$this->tpl->assign('style_enum', $this->infomation->get_style_enum());
		$this->tpl->assign('field_enum', $this->infomation->get_field_enum());
		$this->tpl->assign('adepts', $this->infomation->get_adepts());
		
		$this->tpl->assign('module', 'info.edit');
		$this->tpl->display( $this->get_tpl('uc_info_edit.html') );
		
	}
	
	// 修改资料提交
	public function edit_handler(){
		
		$object = array();
		$this->load->library('encode');
		
		$object['username'] = $this->info['username'];
		
		$object['true_name'] = $this->encode->getFormEncode('true_name');
		$object['sex'] = $this->encode->getFormEncode('sex');
		$object['brd'] = $this->encode->getFormEncode('brd');
		
		if( empty($object['brd']) ) $object['brd'] = '1900-01-01';
		
		
		$object['wyear'] = $this->encode->getFormEncode('wyear');
		$object['school'] = $this->encode->getFormEncode('school');
		$object['zhuanye'] = $this->encode->getFormEncode('zhuanye');
		$object['xueli'] = $this->encode->getFormEncode('xueli');
		$object['shen'] = $this->encode->getFormEncode('User_Shen');
		$object['city'] = $this->encode->getFormEncode('User_City');
		$object['town'] = $this->encode->getFormEncode('User_Town');
		
		// 家装/工装设计师区别已经取消
		// $object['type'] = $this->encode->getFormEncode('type');
		$object['type'] = 0;
		
		$object['zhiwei'] = $this->encode->getFormEncode('zhiwei');
		$object['tel'] = $this->encode->getFormEncode('tel');
		$object['mobile'] = $this->encode->getFormEncode('mobile');
		$object['mobile_show_option'] = $this->encode->getFormEncode('mobile_show_option');
		$object['email'] = $this->encode->getFormEncode('email');
		$object['qq'] = $this->encode->getFormEncode('qq');
		$object['msn'] = $this->encode->getFormEncode('msn');
		$object['address'] = $this->encode->getFormEncode('address');
		$object['shoufei'] = $this->encode->getFormEncode('shoufei');
		$object['style'] = $this->encode->getFormEncode('style');
		$oth_style = '';
		
		if( isset($_POST['otherstyle']) ){
			$oth_style = $_POST['otherstyle'];
			$oth_style = implode(',', $oth_style);
		}
		
		$object['oth_style'] = $oth_style;
		$object['field'] = $this->encode->getFormEncode('field');
		$oth_field = '';
		if( isset($_POST['otherfield']) ){
			$oth_field = $_POST['otherfield'];
			$oth_field = implode(',', $oth_field);
		}
		$object['oth_field'] = $oth_field;
		$object['lilun'] = $this->encode->getFormEncode('lilun');
		$object['yeji'] = $this->encode->getFormEncode('yeji');
		$object['jieshao'] = $this->encode->getFormEncode('jieshao');
		$object['update_time'] = date('Y-m-d H:i:s');
		$object['insert_time'] = date('Y-m-d H:i:s');
		
		$this->load->model('sjs/info', 'infomation');
		$this->infomation->edit_info($object);
		
		echo('<script type="text/javascript">alert("提交成功");location.href="'. $this->ucenter_url .'/info/edit";</script>');
			
	}
	
	// 修改密码界面
	public function password(){
		$this->tpl->assign('module', 'info.password');
		$this->tpl->display( $this->get_tpl('uc_info_password.html') );
	}
	
	// 修改密码提交处理
	public function password_handler(){
		
		$object = array();
		$this->load->library('encode');
		$object['username'] = $this->encode->getFormEncode('username');
		$object['original'] = $this->encode->getFormEncode('original');
		$object['password'] = $this->encode->getFormEncode('password');
		$object['password2'] = $this->encode->getFormEncode('password2');
		
		if( $object['username'] != $this->info['username'] ){
			exit('提交的用户名和当前登录的用户名不符合');
		}
		if(empty($object['original'])){
			exit('原始密码不能为空');
		}
		if( empty($object['password']) ){
			exit('空密码错误');
		} else {
			if( $object['password2'] !== $object['password'] ){
				exit('两次密码不一致');
			}
		}
		$this->load->model('sjs/info', 'infomation');
		
		try{
			$this->infomation->edit_pass($object);
			$this->tpl->assign('return_info', '密码修改成功!');
		}catch(Exception $e){
			$this->tpl->assign('return_info', $e->getMessage());
		}
		$this->password();
	}
	
	public function face(){
		
		$this->load->model('sjs/info', 'sjs_info_model');
		
		$face = $this->sjs_info_model->get_face($this->info['username']);
		
		$this->tpl->assign('face_image', $face);
		$this->tpl->assign('module', 'info.face');
		$this->tpl->display('member_design/ucenter/uc_info_face.html');
	}
	
	public function face_handler(){
		
		$face = $this->gf('face');
		$path = $this->gf('path');
		
		if( $path != '' ){
			try{
				$this->load->model('tempfile_move_model');
				$path = $this->tempfile_move_model->tempfile_move($path);
				$this->tempfile_move_model->delete_file($face);
				$this->load->model('sjs/info', 'sjs_info_model');
				$this->sjs_info_model->edit_face($this->info['username'], $path);
				echo('success');
			}catch(Exception $e){
				echo( $e->getMessage() );
			}
		} else {
			echo('图片未修改');
		}
		
	}
	
	
}





















?>