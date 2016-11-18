<?php

class member extends MY_Controller {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function get_user_info()
	{
		
		// 使 XHR POST 跨域请求支持
		$urls = config_item('url');
		
		header("Access-Control-Allow-Origin:" . rtrim($urls['mobile'], '/'));
		
		//phpinfo();
		//exit();
		
		$error = '';
		$option = $this->gf('option', array('retain_key'=>TRUE));
		
		if( ! is_array($option) ) $error = '参数错误';
		
		if( $error == '' )
		{
			
			$config = array(
				'username'		=> '',
				'fields'		=> 'id,username',
				'thumb'			=> FALSE,					// 是否生成缩略图
				'thumb_option'	=> array(					// 生成缩略图选项
					'type'		=> 'crop',					// resize, crop
					'w'			=> 0,
					'h'			=> 0
				)
			);
			
			$config = array_merge($config, $option);
			
			if( $config['username'] == '' )
			{
				$error = '参数错误, 用户名必选';
			}
			
		}
		
		// 检测读取的数据
		if( $error == '' )
		{
			// 注册允许读取的数据类型, 类型之外的不读取
			$_allow_fields = array('id', 'username', 'rejion', 'sex', 'manager', 'email', 
			'tel', 'mobile', 'fax', 'company', 'logo', 'company_pic', 'address', 'slogen', 'shortname', 'flag', 'delcode');
			if( is_array( $config['fields'] ) ) $config['fields'] = implode(",", $config['fields']);
			if( ! preg_match('/^\w+?(\,\w+)*$/', $config['fields']) ) $error = '读取的数据类型错误.1';
			if( $error == '' )
			{
				$fields = explode(',', $config['fields']);
				$_arr = array();
				foreach($fields as $item)
				{
					$_item = strtolower( trim($item) );
					if( in_array( $_item, $_allow_fields ) != FALSE )
					{
						$_arr[] = $_item;
					}
				}
				if( count( $_arr ) == 0 )
				{
					$error = '读取的数据类型错误.2';
				}
				else
				{
					$config['fields'] = implode(',', $_arr);
				}
			}
		}
		
		if( $error == '' )
		{
			$this->load->model('company/company', 'deco_model');
			$sql = "select ". $config['fields'] ." from [company] where username = '". $config['username'] ."'";
			$result = $this->deco_model->get($sql);
			if( $result == FALSE ) $error = '找不到数据';
		}
		
		// LOGO 缩略图 处理
		if( $error == '' )
		{
			if( isset( $result['logo'] ) && !empty( $result['logo'] ) )
			{
				$result['logo_src'] = $this->upload_complete_url($result['logo'], 'logo');
				if( $config['thumb'] === TRUE )
				{
					$_thumb_type = strtolower( isset( $config['thumb_option']['type'] ) ? strtolower( $config['thumb_option']['type'] ) : '' );
					$_w = isset( $config['thumb_option']['w'] ) ? $config['thumb_option']['w'] : 0;
					$_h = isset( $config['thumb_option']['h'] ) ? $config['thumb_option']['h'] : 0;
					if(
						in_array($_thumb_type, array('crop', 'resize')) &&
						is_numeric($_w) && is_numeric($_h) && ( $_w != 0 || $_h != 0 )
					)
					{
						$this->load->library('thumb');
						$result['logo_thumb'] = $this->thumb->crop($_thumb_type);
					}
				}
			}
		}
		
		if( $error == '' )
		{
			json_echo( array(
				'type'		=> 'success',
				'data'		=> $result
			) );
		}
		else
		{
			json_echo( array(
				'type'		=> 'error',
				'error'		=> $error
			) );
		}
		
	}

}




















?>