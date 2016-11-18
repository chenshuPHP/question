<?php

// 资质查询相关
class sida_qualifications extends sida_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function home()
	{
	}
	
	// 资质查询
	public function search()
	{
		
		$option = array(
			'key'		=> $this->gf('key')
		);
		
		$error = '';
		
		// $option['key'] = '东顺';
		
		if( $this->encode->utf8_strlen($option['key']) <= 1 )
		{
			$error = '关键词不符合规范, 至少两个汉字';
		}
		
		
		
		if( $error == '' )
		{
			$this->load->model('sida/qualifications_model');
			$result = $this->qualifications_model->search( $option );
			//$result = json_encode( $result );
			if( $result['type'] == 'error' )
			{
				$error = $result['error'];
			}
		}
		
		if( $error == '' )
		{
			echo( json_encode( array(
				'type'		=> 'success',
				'data'		=> $result['data']
			) ) );
		}
		else
		{
			echo( json_encode( array(
				'type'		=> 'error',
				'error'		=> $error
			) ) );
		}
		
		
	}
	
	
}



?>