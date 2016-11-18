<?php

// 注册 装修公司 会员类型 管理
// 2015-03-09

class admin_member_deco extends admin_base {

	public function __construct(){
		parent::__construct();
	}

	public function manage(){

		$this->tpl->assign('module', 'manage.manage');
		
		$this->load->model('manager/manager_model');
		

		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>20
		);

		$params = array();

		$where = "hangye = '装潢公司' and register = 2";

		$so = array(
			'key'			=> $this->gr('key'),
			'vip'			=> $this->gr('vip'),
			'adv'			=> $this->gr('adv'),
			'delete'		=> $this->gr('delete'),
			'sort'			=> $this->gr('sort') 
		);
		
		if( ! empty($so['key']) ){
			$so['key'] = iconv("gbk", "utf-8", $so['key']);
			$params[] = 'key=' . $so['key'];
			$where .= " and ( company like '%". $so['key'] ."%' or rejion = '". $so['key'] ."' or tel = '". $so['key'] ."' or mobile = '". $so['key'] ."' or username = '". $so['key'] ."' or user_shen = '". $so['key'] ."' or user_city = '". $so['key'] ."' or user_town like '%". $so['key'] ."%' )";
		}

		if( $so['vip'] == 1 ){
			$params[] = 'vip=1';
			$where .= " and flag = 2";
		}
		
		if( $so['adv'] == 1 )
		{
			$params[] = 'adv=1';
			$where .= " and isadv = 1";
		}

		if( $so['delete'] == 1 ){
			$params[] = 'delete=1';
			$where .= " and delcode = 1";
			$this->tpl->assign('module', 'manage.delete');
		} else {
			$where .= " and delcode = 0";
		}
		
		$sort = "order by puttime desc";
		if( ! empty( $so['sort'] ) )
		{
			switch($so['sort'])
			{
				case 'update_time':
					$sort = "order by update_time desc";
					break;
				case 'put_time':
					$sort = "order by puttime desc";
					break;
			}
		}
		
		// 城市分站分权管理
		// 2015-07-02 不会读取其他城市数据
		if( ! empty($this->admin_city_id) ){
			$where .= " and user_city = '". $this->admin_city_id ."'";
		}

		$sql = "select * from ( select username, company, rejion, tel, mobile, flag, puttime, lastlogin, login_num, user_shen, user_city, ".
		"user_town, delcode, fuzeren, update_time, num = row_number() over(". $sort .") ".
		"from company where ". $where ." ) as temp where num between ". (($args['page'] - 1) * $args['size'] + 1) .
		" and " . ($args['page'] * $args['size']);
		
		$sql_count = "select count(*) as icount from company where " . $where;

		$this->load->model('company/company', 'deco_model');

		$result = $this->deco_model->get_list($sql, $sql_count, true);
		
		$result['list'] = $this->manager_model->assign($result['list'], 'username, fullname', 'fuzeren');
		
		// var_dump($result);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->pageSize = $args['size'];
		
		$url = $this->get_complete_url('/member/deco/manage?page=<{page}>');
		$url_first = $this->get_complete_url('/member/deco/manage');

		if( count($params) != 0 ){
			$url .= '&' . implode('&', $params);
			$url_first .= '?' . implode('&', $params);
		}

		$this->pagination->url_template = $url;
		$this->pagination->url_template_first = $url_first;
		
		$this->tpl->assign('pagination', $this->pagination->toString(true));
		$this->tpl->assign('args', $args);
		$this->tpl->assign('so', $so);
		$this->tpl->assign('list', $result['list']);
		
		$this->tpl->display('admin/member/deco/manage.html');
	}
	
	// 修改 会员资料
	public function edit(){
		
		$level = 10;		// 设定权限
		
		$username = $this->gr('username');
		$r = $this->gr('r');
		
		$this->load->model('company/company', 'deco_model');
		$this->load->model('company/company_config');
		
		
		$deco = $this->deco_model->get_company($username, 'username, company, flag, xinyu, koubei, koubei_total, amountpoint, delcode, fuzeren, topflag, topflag_desc, zhibaojin, open_time, end_time, shortname, biz_type, company_date, slogen, usercode, logo, company_pic, rejion, sex, manager, address, email, mobile, qq, website, user_shen, user_city, user_town, isadv, adv_start_date, adv_end_date');
		
		$this->tpl->assign('module', 'deco.edit');
		$this->tpl->assign('deco', $deco);
		$this->tpl->assign('rurl', $r);
		
		$this->load->model('manager/manager_model');
		
		$admins = $this->manager_model->get_admins('select username, fullname, bmid from admin_manage where lockstate = 0 order by puttime desc');
		
		$this->tpl->assign('admins', $admins['list']);
		$this->tpl->assign('level', $level);
		$this->tpl->assign('zhibaojin', $this->company_config->get_zhibaojin());
		$this->tpl->display('admin/member/deco/edit.html');

	}

	public function edit_handler(){

		$rurl = $this->gf('r');
		$rurl = str_replace('&amp;', '&', $rurl);

		$info = array(
			'username'				=> $this->gf('username'),
			'name'					=> $this->gf('company_name'),
			'pswd'					=> $this->gf('pswd'),
			'flag'					=> $this->gf('flag'),
			'koubei'				=> $this->gf('koubei'),
			'amount'				=> $this->gf('amount'),
			'delcode'				=> $this->gf('delcode'),
			'admin'					=> $this->gf('admin_user'),
			'topflag'				=> $this->gf('topflag'),			// 推荐星级
			'topflag_desc'			=> $this->gf('topflag_desc'),	// 推荐理由
			'zhibaojin'				=> $this->gf('zhibaojin'),		// 质保金
			'sdate'					=> $this->gf('sdate'),				// 高级会员开始~结束日期
			'edate'					=> $this->gf('edate'),
			'isadv'					=> $this->gf('isadv'),
			'adv_start_date'		=> $this->gf('adv_start_date'),
			'adv_end_date'			=> $this->gf('adv_end_date')
		);
		
		if( ! empty( $this->admin_city_id ) ){
			$info['admin_city_id'] = $this->admin_city_id;
		}

		$this->load->model('company/company', 'deco_model');

		try{
			
			$_tmp = $this->deco_model->getCompany($info['username'], array(
				'fields'		=> 'flag, isadv'
			));
			
			// 后台管理员设置装修公司 (高级会员，订单量，等资料修改)
			$this->deco_model->deco_admin_set($info);
			
			$this->load->model('company/company_koubei_model');
			
			if( $info['flag'] == 1 && $_tmp['flag'] != 2 )
			{
				// 增加 口碑值 加入高级会员
				$this->company_koubei_model->vip($info['username'], 0, array(
					'description'		=> '加入高级会员',
					'admin'				=> $this->admin_username
				));
			}
			
			if( $info['isadv'] == 1 && $_tmp['isadv'] != 1 )
			{
				// 增加 口碑值 加入6900广告会员
				$this->company_koubei_model->adv($info['username'], 0, array(
					'description'		=> '加入6900广告会员',
					'admin'				=> $this->admin_username
				));
			}
			
			//if( $info['flag'] != 1 && $info['isadv'] != 1 )
			//{
				$this->company_koubei_model->update_user_koubei( $info['username'] );
			//}
			
			$this->alert('修改成功', $rurl);
		}catch(Exception $e){
			$this->alert('错误' . $e->getMessage());
		}

	}
	
	// 进入装修公司会员中心
	public function assist(){
		
		$aid = $this->gr('aid');
		$uid = $this->gr('uid');
		
		$this->load->model('company/company', 'deco_model');
		
		$deco = $this->deco_model->get_company($uid, 'username, hangye, flag, delcode, register, fuzeren');
		
		if( ! $deco ) exit('没有找到公司资料');
		
		if( $deco['hangye'] != '装潢公司' ){
			exit('非装修公司禁止操作');
		}
		
		if( $this->admin_username != 'ccy' ){
			if( strtolower( $this->admin_username ) != strtolower( $deco['fuzeren'] ) ){
				exit('客服代表不一致，不能登录, 需要先设置客服代表');
			}
		}
		
		if( $deco['delcode'] == 1 ){
			exit('此会员已经被禁止登录');
		}
		
		$this->load->model('LoginModel', 'login_model');
		
		$this->login_model->create_cookie(array(
			'username'=>$deco['username'],
			'hangye'=>$deco['hangye'],
			'register'=>$deco['register']
		));
		
		$urls = $this->config->item('url');
		
		$this->alert('成功', $urls['www'] . '/member/main.asp');
		
	}
	
}
?>