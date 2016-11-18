<?php

class sp_home extends sp_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function home( $args = array() )
	{
		
		$tpl = $this->get_tpl('index.html');
		
		$this->load->model('company/Company', 'company_model');
		
		$object = $this->company_model->getCompany($this->user);
		
		if( $object['delcode'] == 1 ) show_error('店铺已经被删除', 404);
		
		if( !$object ){ show_404(); }
		if( empty($object['logo']) ){
			$object['logo_url'] = '';
		} else {
			$this->thumb->setPathType(1);
			$object['logo_url'] = $this->thumb->resize($object['logo'], 245, 160);
			$this->thumb->setPathType(0);
		}
		
		if( !empty($object['company_pic']) ){
			$object['company_pic_url'] = $this->thumb->resize($object['company_pic'], 465, 260);
			//$object['company_pic_url'] = $this->thumb->crop($object['company_pic'], 465, 260);
            //$object['company_pic_url'] = $this->thumb->getNarrow($object['company_pic'], 465, 260);
		} else {
			$object['company_pic_url'] = 'http://www.shzh.net/resources/shop/laconic/images/t/3.jpg';
		}

		var_dump2($object);

		// 2015-06-24
		// 屏蔽电话号码
		// 模版中已经设置 普通会员不显示手机号码了，所以这里不需要替换手机号码
		$object['tel_original'] = $object['tel'];
		/*
		if( $object['flag'] != 2 ){
			
			$check_update_limit = true;
			if( empty( $object['update_time'] ) ){
				$check_update_limit = false;
			} else {
				$update_time_limit = floor( ( strtotime( date('Y-m-d H:i:s') ) - strtotime($object['update_time']) ) / 86400 );
				if( $update_time_limit > 30 ) $check_update_limit = false;
			}
			
			if( $check_update_limit == false ){
				$tel = $this->config->item('tel');
				$object['tel'] = $tel['400'];	// 将固定电话设置为本站的 400
				$object['mobile'] = '';
			}
			
		}
		*/
		
		$this->info = $object;
		
		$this->load->model('company/company_config');
		$this->info = $this->company_config->zhibaojin_assign($this->info);
		
		// 案例
		$this->load->model('company/usercase', 'usercase_model');
		$this->load->model('company/project_category_model');
		$cases = $this->usercase_model->getUserCases($this->user, array(
			'top' 		=> 3,
			'fields'	=> 'id, username, casename as name, fm_image as fm, sheng, city, town, build_type_1 as t1, build_type_2 as t2, area, budget, style_name as style, styletype'
		));
		foreach($cases as $key=>$val){
			$val['thumb'] = $this->thumb->crop($val['fm'], 312, 200);
			$cases[$key] = $val;
		}
		$cases = $this->project_category_model->assign_to_project($cases);
		$this->tpl->assign('cases', $cases);
		
       // var_dump2( $cases );
		
		// 公司动态部分
		$this->load->model('company/usernews', 'usernews_model');
		$news_list = $this->usernews_model->get_list(array(
			'top'=>4,
			'username'=>$this->user,
			'fields'=>array('id', 'title', 'detail', 'addtime')
		));
		$this->tpl->assign('news_list', $news_list);
		
		// 员工信息
		$this->load->model('company/userteam', 'employee_model');
		$this->load->model('company/employee_category_model');
		
		$employee = $this->employee_model->gets("select top 3 id, username, true_name, geyan, detail, job_id, course, face_image as face from [user_team_member] where face_image <> '' and recycle = 0 and username = '". $this->user ."' order by sort_id asc");
		
		$employee = $employee['list'];
		$employee = $this->usercase_model->case_count_assign_employee($employee);
		$employee = $this->employee_category_model->assign_employee($employee);
		$this->tpl->assign('employee', $employee);
		
		//$this->info = $this->project_model->case_count_assign_users($this->info);
		//$this->info = $this->employee_model->count_assign($this->info);
		
		$this->info = array_merge($this->info, $this->infomation);

        var_dump2($this->info);

       // $pic_img['company_pic_url'] = $this->thumb->getNarrow($pic_img['company_pic_url'],465,260);



		$this->tpl->assign('object', $this->info);


		
		// 2016-09-18 店铺优惠活动
		$this->load->model('company/promotion_model');
		$where = "username = '". $this->user ."' and edate > '". date('Y-m-d') ."' and " . $this->promotion_model->where;
		$promotion = $this->promotion_model->get("select top 1 id, title, sdate, edate from [". $this->promotion_model->table_name ."] where " . $where);
		$this->tpl->assign('promotion', $promotion);
		
		
		// seo infomation
		$object = $this->info;
		$this->load->library('encode');
		$description = $this->encode->get_text_description($object['content'], 100, false);
		$this->tpl->assign('title', $object['company'] . '-上海装潢网');
		$this->tpl->assign('keywords', $object['company'] . ',装修');
		$this->tpl->assign('description', $description);
		
		$this->tpl->assign('module', 'home');
		$this->tpl->display( $tpl );
		
		
	}
	
}


?>