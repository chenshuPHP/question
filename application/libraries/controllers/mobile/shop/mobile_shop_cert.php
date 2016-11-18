<?php

// 公司证书
class mobile_shop_cert extends mobile_shop_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('module', 'cert');
	}
	
	// 证书主页
	public function home()
	{
		$tpl = $this->get_tpl('/shop/cert/home.html');
		
		$this->load->model('company/zizhi');
		
		$where = $this->zizhi->where( array(
			"senduser = '". $this->username ."'"
		) );
		$sql = "select id, senduser, imgpath as path, zizhiname as name from [". $this->zizhi->table_name ."] where " . $where . " order by sortid asc";
		$cert = $this->zizhi->gets( $sql, '', array(
			'format'		=> FALSE
		) );
		$cert = $cert['list'];
		
		// 前端要求, 附加图片尺寸信息
		if( $cert )
		{
			foreach( $cert as $key=>$value )
			{
				$cert[$key]['imageInfo'] = $this->thumb->getFileInfo( $value['path'] );
			}
		}
		
		var_dump2( $cert );
		
		$this->mobile_url_model->assign_sp_cert_url( $cert );

		$this->tpl->assign('cert', $cert);
		$this->tpl->display( $tpl );
	}
	
}

?>