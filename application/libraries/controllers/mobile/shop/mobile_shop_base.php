<?php

class mobile_shop_base extends mobile_base {
	
	public $username = '';
	public $company = NULL;
	
	public function __construct()
	{
		parent::__construct();
		$this->tpl->assign('module', 'shop');
		
		$this->load->library('thumb');
		
	}
	
	public function initialize( $username )
	{
		$this->username = $username;
		$this->load->model('company/company', 'deco_model');
		$this->load->model('company/company_config');
		$this->load->model('company/promotion_model');
		$this->load->model('mobile/mobile_url_model');
		
		$this->company = $this->deco_model->getCompany($this->username, array(
			'fields'		=> 'username, company, shortname, logo, biz_type, user_shen, user_city, user_town, update_time'
		));
		
		$this->company_config->biz_type_assign( $this->company );

		if( isset( $this->company['logo'] ) && ! empty( $this->company['logo'] ) )
		{
			$this->thumb->setPathType(1);
			$this->company['logo_thumb'] = $this->thumb->resize($this->company['logo'], 200, 140);
			$this->thumb->setPathType(0);
		}
		
		$this->mobile_url_model->assign_shop_base_url($this->company);
		
		//获取 优惠 活动 的标题
		$company = $this->company;		
		$arr = array(
			'fields'	=>	'id,title,addtime,username',
			'username' 	=>	$company['username'],
			'num'		=>	5      // 获取 活动 的数量
		);	
		$company['promotion'] = $this->promotion_model->getTitleList($arr);

		$this->mobile_url_model->assign_sp_promotion_url($company['promotion']);
		
		// var_dump2($company);
		
		$this->tpl->assign('cmp', $company);
		$this->tpl->registerPlugin('modifier', 'shop_complete_url', array($this, 'get_shop_complete_url'));
	}
	
	public function get_shop_complete_url($path)
	{
		if( empty( $path ) ) return '';
		return $this->company['shop_base_url'] . ltrim($path, '/');
	}
	
}

?>