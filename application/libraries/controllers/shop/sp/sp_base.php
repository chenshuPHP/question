<?php

class sp_base extends MY_Controller {
	
	// 当前访问店铺所属的公司 用户名
	var $user = '';
	
	// 模板目录
	var $tpl_dir = 'shop/laconic/';
	
	// 店铺基本信息
	var $infomation = NULL;
	
	public function __construct(){
		
		parent::__construct();
		
		$urls = $this->config->item('url');
		
		$this->tpl->assign('tpl_dir', $this->tpl_dir);
		$this->tpl->assign('sp_res_url', $urls['res'] . 'shop/laconic/');
		
		$this->load->library('thumb');

		$this->tpl->registerPlugin('modifier', 'sp_url', array($this, 'sp_url'));
		
	}
	
	public function sp_url( $url = '' )
	{
		return rtrim($this->infomation['sp_base_url'], '/') . '/' . ltrim($url, '/');
	}

	// 根据局部路径生成店铺全局URL
	public function get_complete_url($path)
	{
		return rtrim($this->infomation['sp_base_url'], '/') . '/' . ltrim($path, '/');
	}
	
	// 获取完整模板路径
	protected function get_tpl($tpl_name){
		return $this->tpl_dir . ltrim($tpl_name, '/');
	}
	
	// 店铺数据初始化
	// 加载公共区域数据
	// 在 sp_controller 类中调用, 调用时会传递从地址栏取得的 username 作为参数
	public function initialize($user){
		
		// 店铺头部底部公共区域数据需要用的模型
		// 继承类中无需再次加载这些模型
		$this->load->model('company/company', 'deco_model');
		$this->load->model('company/company_config', 'company_config_model');
		$this->load->model('company/usercase', 'project_model');
		$this->load->model('company/userteam', 'employee_model');
		$this->load->model('company/zizhi', 'zizhi_model');
		
		if( empty($user) ) show_error('找不到店铺, 请检查地址是否有误 1', 404);
		
		$this->user = strtolower( $user );
		
		$deco = $this->deco_model->get_company($this->user, "username, company, mobile, tel, email, address, website, delcode, flag, ".
		"shortname, update_time, logo, koubei, koubei_total, user_shen, user_city, user_town, rejion, slogen, biz_type");
		
		if( ! $deco ) show_error('找不到店铺, 请检查地址是否有误 2', 404);
		
		if( $deco['delcode'] == 1 ) show_error('该店铺被锁定, 请联系管理员解锁', 404);
		
		// 普通会员, 3 天内无更新 不显示真实联系方式
		/*
		if( $deco['flag'] != 2 ){
			$check_update_limit = true;
			if( empty( $deco['update_time'] ) ){
				$check_update_limit = false;
			} else {
				$update_time_limit = floor( ( strtotime( date('Y-m-d H:i:s') ) - strtotime($deco['update_time']) ) / 86400 );
				if( $update_time_limit > 30 ) $check_update_limit = false;
			}
			
			if( $check_update_limit == false ){
				$tel = $this->config->item('tel');
				$deco['tel'] = $tel['400'];			// 将固定电话设置为本站的 400
				$deco['mobile'] = '';
			}
		}
		*/
		
		// 2016-10-25
		// 任何会员, 店铺更新日期超过三天后, 联系方式 屏蔽
		/*
		$update_time_limit = floor( ( strtotime( date('Y-m-d H:i:s') ) - strtotime($deco['update_time']) ) / 86400 );
		if( $update_time_limit > 3 )
		{
			$def = $this->config->item('default_deco_info');
			$deco = array_merge($deco, $def);
		}
		*/
		$this->deco_model->conver( $deco );
		$this->company_config_model->biz_type_assign( $deco );
		
		
		
		// == 附加数据统计 ==
		$deco = $this->project_model->case_count_assign_users($deco);	// 为 $deco 附加案例数量统计
		$deco = $this->employee_model->count_assign($deco);				// 为 $deo 附加员工数量统计
		
		$this->infomation = $deco;	// 保存到属性中供继承类调用
		
		$this->tpl->assign('object', $deco);
		
		unset( $deco );
		
		// 头部需要的4个证书图片
		$zizhi_list = $this->zizhi_model->get_list(array(
			'top'				=> 4,
			'username'			=> $this->user
		));
		$this->tpl->assign('zizhi_list', $zizhi_list);
		
		// var_dump2( $this->infomation );

	}
	
}

?>