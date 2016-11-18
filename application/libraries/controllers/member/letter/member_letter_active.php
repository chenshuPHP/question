<?php

// 站内信操作
class member_letter_active extends member_letter_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
	// 删除
	public function delete()
	{
		$ids = $this->gf('ids');
		if( empty( $ids ) || ! is_array( $ids ) || count( $ids ) == 0 )
		{
			exit( json_encode( array('type'=>'error', 'message'=>'参数错误') ) );
		}
		
		
		
		try
		{
			$this->member_letter_model->user_delete(array(
				'username'			=> $this->base_user,
				'isVIP'				=> $this->isVIP(),
				'ids'				=> $ids
			));
			echo( json_encode( array('type'=>'success') ) );
		}
		catch(Exception $e)
		{
			echo( json_encode( array('type'=>'error', 'message'=>$e->getMessage()) ) );
		}
		
		
	}
	
	// 已阅
	public function view()
	{
		$ids = $this->gf('ids');
		if( empty( $ids ) || ! is_array( $ids ) || count( $ids ) == 0 )
		{
			exit( json_encode( array('type'=>'error', 'message'=>'参数错误') ) );
		}
		
		
		try
		{
			$this->member_letter_model->user_open(array(
				'username'			=> $this->base_user,
				'isVIP'				=> $this->isVIP(),
				'ids'				=> $ids
			));
			echo( json_encode( array('type'=>'success') ) );
		}
		catch(Exception $e)
		{
			echo( json_encode( array('type'=>'error', 'message'=>$e->getMessage()) ) );
		}
	}
	
	
}


?>