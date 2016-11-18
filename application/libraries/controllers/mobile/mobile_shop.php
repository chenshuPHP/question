<?php
// 装修公司店铺 触屏版
class mobile_shop extends mobile_base {
	
	public $username;
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _remap($params = array()){
		if( count($params) == 0 ){
			show_404();
			exit();
		}
		
		$this->username = array_shift($params);
		
		$method_name = 'home';
		
		if( count( $params ) != 0 )
		{
			$method_name = array_shift( $params );
		}
		
		if( method_exists( $this, $method_name ) )
		{
			if( count( $params ) != 0 )
			{
				$this->$method_name( $params );
			}
			else
			{
				$this->$method_name();
			}
		}
		else
		{
			$dir = rtrim(dirname(__FILE__), '\\') . '\\';
			
			include( $dir . 'shop\\mobile_shop_base.php' );
			include( $dir . 'shop\\mobile_shop_'. $method_name .'.php' );
			// exit( $dir . 'shop\\mobile_shop_'. $method_name .'.php' );
			$class_name = 'mobile_shop_' . $method_name;
			$class = new $class_name();
			$class->initialize( $this->username );
			if( count( $params ) == 0 )
			{
				$method_name = 'home';
			}
			else
			{
				$method_name = strtolower( array_shift( $params ) );
			}
			
			if( method_exists($class, $method_name) )
			{
				if( count( $params ) == 0 )
				{
					$class->$method_name();
				}
				else
				{
					$class->$method_name( $params );
				}
			}
			else
			{
				if( method_exists($class, '_remap') )
				{
					$class->_remap($method_name, $params);
				}
			}
			
		}
		
	}
	
}



























?>