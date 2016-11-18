<?php

// 装修公司定位
class deco_position extends deco_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function home( $args = array() )
	{
		$keys= config_item('keys');
		
		$this->tpl->assign('key', $keys['qq_map_webclient']);
		$this->tpl->display( $this->get_tpl('position/home.html') );
	}
	
	public function get_bounds_decos()
	{
		$info = $this->get_form_data();
		$info['ne'] = explode(',', $info['ne']);		// 右上角 (纬度大, 经度大)
		$info['sw'] = explode(',', $info['sw']);		// 左下角 (纬度小, 经度小)
		
		$lat = array($info['sw'][0], $info['ne'][0]);
		$lng = array($info['sw'][1], $info['ne'][1]);
		
		$this->load->library('mdb');
		$sql = "select top 50 LOCAL.username, LOCAL.lat, LOCAL.lng, COMPANY.company, COMPANY.logo, COMPANY.address from [company_location] as LOCAL LEFT JOIN [company] as COMPANY ".
		"ON COMPANY.username = LOCAL.username ".
		"where lat > '". $lat[0] ."' and lat < '". $lat[1] ."' and lng > '". $lng[0] ."' and lng < '". $lng[1] ."' and COMPANY.delcode = 0";
		$res = $this->mdb->query( $sql );
		echo( json_encode( $res ) );
	}
	
}

?>