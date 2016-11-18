<?php

// 系统管理员管理
class admin_sys_admin_user extends admin_base {
	public function __construct(){
		parent::__construct();
	}
	
	public function manage(){
		
		$this->load->model('manager/manager_model');
		
		$page = $this->encode->get_page();
		$cfg = array(
			'page'=>$page,
			'size'=>15
		);
		
		$where = "1=1";
		
		if( ! empty($this->admin_city_id) ){
			$where .= " and city_id = '". $this->admin_city_id ."'";
		}
		
		$sql = "select * from ( select id, username, fullname, bmid, puttime, lockstate, row_number() over( order by puttime desc ) as num from admin_manage where ". $where ." ) as temp where num between '". (($cfg['page']-1) * $cfg['size'] + 1) ."' and '" . ($cfg['page'] * $cfg['size']) . "'";
		$sql_count = "select count(*) as icount from admin_manage where " . $where;
		
		$result = $this->manager_model->get_admins($sql, $sql_count);
		$result['list'] = $this->manager_model->bm_assign($result['list']);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->url_template_first = $this->manage_url . 'sys_admin/user/manage';
		$this->pagination->url_template = $this->manage_url . 'sys_admin/user/manage?page=<{page}>';
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $result['count']);
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('module', 'user.manage');
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->display('admin/sys_admin/user_manage.html');
	
	}
	
	// 管理员添加
	public function add(){
		
		$this->load->model('manager/manager_model');
		$this->load->model('manager/manager_module_model');
		
		
		if( empty($this->admin_city_id) ){
			$modules = $this->manager_module_model->get_all_modules();
		} else {
			$modules = $this->manager_module_model->get_fenzhan_module();
		}
		
		$bm = $this->manager_model->get_all_bm();
		
		$this->tpl->assign("bm", $bm);
		$this->tpl->assign("modules", $modules);
		$this->tpl->assign('module', 'user.add');
		
		// 输出分站数据
		$this->tpl->assign('fenzhan', $this->get_fenzhan_city());
		
		$this->tpl->display('admin/sys_admin/user_add.html');
	}
	
	// 添加表单提交处理
	public function add_submit(){
		
		$admin = array(
			'name'			=> $this->gf('name'),
			'username'		=> $this->gf('username'),
			'password'		=> $this->gf('password'),
			'bmid'			=> $this->gf('bmid'),
			'modules'		=> $this->gf('module'),
			'lock'			=> $this->gf('lock'),
			'mobile'		=> $this->gf('mobile'),		// 手机号码
			'code'			=> $this->gf('code')		// 身份证号码
		);
		$admin['modules'] = implode(',', $admin['modules']);
		
		// 2015-07-01
		// 管理员 分站 指派
		// 当前管理员隶属于的分站
		if( empty($this->admin_city_id) ){
			$admin['city_id'] = $this->gf('fenzhan');
		} else {
			$admin['city_id'] = $this->admin_city_id;
			if( empty($admin['city_id']) ) exit('指派分站ID有误');
		}
		
		$this->load->model('manager/manager_model');
		
		try{
			$id = $this->manager_model->admin_add($admin);
			$this->alert('添加成功', $this->manage_url . 'sys_admin/user/manage');
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
	}
	
	// 修改界面
	public function edit(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		$this->load->model('manager/manager_model');
		$this->load->model('manager/manager_module_model');
		
		$admin = $this->manager_model->get_admin($id, array(
			'fields'		=> 'id, username, password, manages, fullname, lockstate, nick, bmid, city_id, mobile, code'
		));
		
		var_dump2( $admin );
		
		if( ! empty($this->admin_city_id) && $admin['city_id'] != $this->admin_city_id  ){
			exit('无法编辑');
		}
		
		// 只读取分站开启了的模块
		if( empty($this->admin_city_id) ){
			$modules = $this->manager_module_model->get_all_modules();
		} else {
			$modules = $this->manager_module_model->get_fenzhan_module();
		}
		
		
		$bm = $this->manager_model->get_all_bm();
		
		foreach($modules as $key=>$item){
			$item['checked'] = false;
			foreach($admin['manages'] as $k=>$v){
				if( $item['id'] == $v ){
					$item['checked'] = true;
					break;
				}
			}
			$modules[$key] = $item;
		}
		
		$this->tpl->assign("bm", $bm);
		$this->tpl->assign("modules", $modules);
		$this->tpl->assign('module', 'user.edit');
		$this->tpl->assign('rurl', $r);
		$this->tpl->assign('admin', $admin);
		
		// 输出分站数据
		$this->tpl->assign('fenzhan', $this->get_fenzhan_city());
		
		$this->tpl->display('admin/sys_admin/user_edit.html');
		
	}
	
	private function get_fenzhan_city(){
		$this->load->model('city_model');
		$fenzhan = $this->city_model->get_fenzhan_city();
		return $fenzhan;
	}
	
	// 编辑提交
	public function edit_submit(){
		
		$r = $this->gf('r');
		
		$admin = array(
			'name'=>$this->gf('name'),
			'username'=>$this->gf('username'),
			'password'=>$this->gf('password'),
			'bmid'=>$this->gf('bmid'),
			'modules'=>$this->gf('module'),
			'lock'=>$this->gf('lock')
		);
		
		$admin['modules'] = implode(',', $admin['modules']);
		$admin['city_id'] = $this->gf('fenzhan');
		
		// 2015-07-01
		// 管理员 分站 指派
		// 当前管理员隶属于的分站
		if( ! empty($this->admin_city_id) ){
			$admin['city_id'] = $this->admin_city_id;
		}
		
		$this->load->model('manager/manager_model');
		
		try{
			$id = $this->manager_model->admin_edit($admin);
			$this->alert('修改成功', $this->manage_url . 'sys_admin/user/manage');
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
		
	}
	
	// 2015-07-01 同步用户和权限关系到 关系表
	//private function _async_admin_module_relation(){
	//	$this->load->model('manager/manager_model');
	//	$this->manager_model->sync_admin_module_relation();
	//}
	
}

?>