<?php

// 资质证书
class member_certificate_base extends member_base {
	
	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('module', 'certificate');
	}
	
}

?>