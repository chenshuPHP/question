<?php

// 企业促销 编辑
class member_promotion_active extends member_promotion_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		$tpl = $this->get_tpl('promotion/active.html');
		
		$this->tpl->assign('page_module', 'active');
		
		$id = $this->gr('id');
		$rurl = $this->gr('r');
		
		$promotion = FALSE;
		if( $id != '' )
		{
			$this->load->model('company/promotion_model');
			$sql = "select id, title, sdate, edate, jianjie, ImgPath as path ".
			"from [". $this->promotion_model->table_name ."] where id = '". $id ."' and username = '". $this->base_user ."'";
			$promotion = $this->promotion_model->get( $sql );
			$this->tpl->assign('page_module', 'edit');
		}
		$this->tpl->assign('rurl', $rurl);
		$this->tpl->assign('promotion', $promotion);
		$this->tpl->display( $tpl );
	}
	
	// 提交
	public function handler()
	{
		$info = $this->get_form_data();
		if( ! isset( $info['id'] ) ) $info['id'] = '';
		
		// 数据合法性验证
		if( $info['title'] == '' )
		{
			exit('标题不能为空');
		}
	 	
		if( $info['sdate'] != '' && ! preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $info['sdate']) )
		{
			exit('开始日期格式错误');
		}
		 
		if( $info['edate'] != '' && ! preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $info['edate']) )
		{
			exit('结束日期格式错误');
		}
		
		if( $info['poster'] == '' && $info['tmp_upload_path'] == '' )
		{
			exit('请上传海报');
		}
		
		if( $this->encode->utf8_strlen( $info['detail'] ) < 50 )
		{
			exit('活动文字数量太少');
		}
		
		$this->load->model('company/promotion_model');
		
		// 转换成可写入数据库的数组键值对
		$_arr = array(
			'id'			=> $info['id'],
			'title'			=> $info['title'],
			'sdate'			=> $info['sdate'],
			'edate'			=> $info['edate'],
			'jianjie'		=> $info['detail']
		);
		
		// 海报新旧替换处理
		$this->load->model('tempfile_move_model');
		$path = '';
		if( $info['tmp_upload_path'] != '' )
		{
			$path = $this->tempfile_move_model->tempfile_move( $info['tmp_upload_path'] );
		}
		
		if( $info['poster'] != '' )
		{ 
			if( $path != '' )
			{
				$this->tempfile_move_model->delete_file( $info['poster'] );
			}
			else
			{
				$path = $info['poster'];
			}
		}
		
		$_arr['imgpath'] = $path;	// 保存海报到数组 准备写入数据库
		
		try
		{
			// 新增模式
			if( $_arr['id'] == '' )
			{
				unset( $_arr['id'] );
				$_arr['addtime'] = date('Y-m-d H:i:s');
				$_arr['username'] = $this->base_user;
				$id = $this->promotion_model->add( $_arr );
				
				// 增加口碑值
				$this->load->model('company/company_koubei_model');
				$this->company_koubei_model->promotion($this->base_user, $id, array(
					'description'		=> '增加优惠活动 - ' . $_arr['title']
				));
				
				
				$this->alert('添加成功', $this->get_complete_url('/promotion/manage'));
			}
			else	// 编辑模式
			{
				 
				if( isset( $info['update_addtime'] ) && $info['update_addtime'] == 1 )
				{
					$_arr['addtime'] = date('Y-m-d H:i:s');
					unset( $info['update_addtime'] );
				}
				
				$this->promotion_model->update( $_arr, array(
					'username'		=> $this->base_user
				) );
				$this->alert('修改完成', $info['rurl']);
			}
			
			// 店铺更新日期
			$this->deco_model->company_update($this->base_user);
			
		}
		catch(Exception $e)
		{
			exit( $e->getMessage() );
		}
		 
		
		
	}
	
	// 删除到回收站
	public function recycle()
	{
		$id = $this->gr('id');
		$this->load->model('company/promotion_model');
		$error = array();
		try
		{
			$this->promotion_model->recycle($id, array(
				'username'		=> $this->base_user
			));
		}
		catch(Exception $e)
		{
			$error[] = $e->getMessage();
		}
		if( count( $error ) == 0 )
		{
			
			// 店铺更新日期
			$this->deco_model->company_update($this->base_user);
			
			echo( json_encode( array(
				'type'		=> 'success'
			) ) );
		}
		else
		{
			echo( json_encode( array(
				'type'		=> 'error',
				'message'	=> $error
			) ) );
		}
	}
	
}

?>