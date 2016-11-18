<?php

class geocoder extends MY_Controller {
	
	// 获取装修公司坐标
	// http://assist.shzh.net/geocoder/home
	public function home()
	{
		$this->load->library('mapserv');
		
		$this->load->library('mdb');
		$sql = "select top 1 username, company, address from [company] ".
		"where user_shen = '9968' and (address != '' and address is not null) and company <> '' and ".
		"username not in ( select username from company_location ) and register = 2 and hangye = '装潢公司' ".
		"order by id desc";
		$res = $this->mdb->query_single( $sql );
		
		if( ! $res ) exit('done');
		
		$address = $res['address'];
		$address = str_replace(' ', '', $address);
		$address = str_replace('　', '', $address);
		if( strpos($address, '省') == false && strpos($address, '市') == false )
		{
			$address = '上海市' . $address;
		}
		
		$res['location'] = json_decode( $this->mapserv->geocoder( $address ), TRUE );
		
		$_arr = array(
			'username'		=> $res['username'],
			'addr'			=> $res['address'],
			'addtime'		=> date('Y-m-d H:i:s')
		);
		
		if( $res['location']['status'] == 0 )
		{
			$_tmp = $res['location']['result'];
			$_arr['lng'] = $_tmp['location']['lng'];
			$_arr['lat'] = $_tmp['location']['lat'];
			$_arr['similarity'] = $_tmp['similarity'];				// 查询字符串与查询结果的文本相似度 百分比
			$_arr['deviation'] = $_tmp['deviation'];				// 误差距离，单位：米
			
			// 可信度参考：值范围 1 <低可信> - 10 <高可信>
			// 分为1 - 10级，该值>=7时，解析结果较为准确，<7时，会存各类不可靠因素
			$_arr['reliability'] = $_tmp['reliability'];
			
			$_arr['province'] = $_tmp['address_components']['province'];
			$_arr['city'] = $_tmp['address_components']['city'];
			$_arr['district'] = $_tmp['address_components']['district'];
			$_arr['street'] = $_tmp['address_components']['street'];
			$_arr['street_number'] = $_tmp['address_components']['street_number'];
			
		}
		
		//var_dump2( $res['location'] );
		
		$keys = array_keys( $_arr );
		$values = array_values( $_arr );
		$sql = "insert into [company_location](". implode(',', $keys) .")VALUES('". implode("','", $values) ."')";
		$this->mdb->insert( $sql );
		$this->tpl->assign('object', $_arr);
		$this->tpl->display( 'active/geocoder.html' );
	}
	
}

?>