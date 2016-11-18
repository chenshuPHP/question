<?php

// 同步信息类
class sync 
{
	public function __construct()
	{}
	
	public function ajax( $option = array() )
	{
		$config = array(
			'url'				=> '',
			'type'				=> 'POST',
			'data'				=> '',
			'return'			=> TRUE,
			'dataType'			=> 'json',			// [json, string]
			'token'				=> ''
		);
		$config = array_merge($config, $option);
		
		if( $config['url'] == '' ) throw new Exception('URL不能为空');
		
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, $config['url']);
		
		if( strtolower( $config['type'] ) == 'post' )
		{
			curl_setopt($curl, CURLOPT_POST, 1);
		}
		
		if( $config['return'] === TRUE )
		{
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		}
		
		if( ! empty( $config['data'] ) )
		{
			curl_setopt($curl, CURLOPT_POSTFIELDS, $config['data']);
		}
		
		$result = curl_exec($curl);
		if( ! empty( $result ) && is_string( $result ) )
		{
			// $result = str_replace("\xEF\xBB\xBF", '', $result);
			// 过滤 BOM
			$result = trim($result, "\xEF\xBB\xBF");
		}
		
		if( strtolower( $config['dataType'] ) == 'json' )
		{
			$result = json_decode( $result, TRUE );
		}
		
		return $result;
	}
	
}


?>