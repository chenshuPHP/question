<?php

// 装修活动报名
class admin_mall_bm extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function manage(){
		$page = $this->encode->get_page();
		$cfg = array(
			'size'=>15,
			'page'=>$page
		);
		
		$this->load->model('mall/mall_bm_model');
		$this->load->library('pagination');
		
		$sql = "select * from ( select id, name, mobile, address, qq, source, addtime, ip, row_number() over( order by id desc ) as num from zxtg_bm ) as tmp where num between ". ( ($cfg['page']-1) * $cfg['size'] + 1 ) ." and " . ( $cfg['size'] * $cfg['page'] );
		
		$sql_count = "select count(*) as icount from zxtg_bm";
		
		$result = $this->mall_bm_model->get_list($sql, $sql_count);
		
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->url_template_first = $this->manage_url . 'mall/bm/manage';
		$this->pagination->url_template = $this->manage_url . 'mall/bm/manage?page=<{page}>';
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $result['count']);
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('module', 'bm.manage');
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->display('admin/mall/bm/bm_manage.html');
	}
	
	// 移动手机
	public function mobile_bm(){

		$this->load->model('mobile/mobile_mall_model');
		$this->load->model('mobile/mobile_url_model');
		$this->load->library('pagination');

		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>20
		);

		$sql = "select * from ( select id, page_id, page_name, data, addtime, ip, num = row_number() over( order by id desc ) from zxtg_bm_mobile ) as temp where num between ". (($args['page'] - 1) * $args['size'] + 1) ." and " . ($args['page'] * $args['size']);

		$sql_count = "select count(*) as icount from zxtg_bm_mobile";

		$result = $this->mobile_mall_model->get_bm_list($sql, $sql_count);
		$result['list'] = $this->mobile_url_model->format_batch_mall_page($result['list']);
	
		///echo('<!--');
		//var_dump($result);
		//echo('-->');

		$this->pagination->currentPage = $args['page'];
		$this->pagination->url_template_first = $this->manage_url . 'mall/bm/mobile_bm';
		$this->pagination->url_template = $this->manage_url . 'mall/bm/mobile_bm?page=<{page}>';
		$this->pagination->pageCount = $this->pagination->getPageCount($args['size'], $result['count']);
		$pagination = $this->pagination->toString(true);

		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('args', $args);
		$this->tpl->assign('list', $result['list']);


		$this->tpl->assign('module', 'mobile.bm.manage');
		$this->tpl->display('admin/mall/bm/bm_mobile_manage.html');
		
	}

	// 微网页用户设置
	public function mobile_page_user_manage(){

		$this->load->model('mobile/mobile_mall_model');
		
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>20
		);

		$sql = "select * from ( select id, comp_name, page_id, pswd, addtime, num = row_number() over( order by addtime desc ) from zxtg_bm_admin ) as temp where num between ". (($args['page'] - 1) * $args['size'] + 1) ." and " . ($args['page'] * $args['size']);

		$sql_count = "select count(*) as icount from zxtg_bm_admin";

		$result = $this->mobile_mall_model->get_mobile_bm_admins($sql, $sql_count);

		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->url_template = $this->get_complete_url('/mall/bm/mobile_page_user_manage?page=<{page}>');
		$this->pagination->url_template_first = $this->get_complete_url('/mall/bm/mobile_page_user_manage');
		
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('module', 'mobile.bm.user_manage');
		$this->tpl->assign('args', $args);
		$this->tpl->display('admin/mall/bm/bm_mobile_user_manage.html');

	}

	// 添加 & 编辑 微网页商户查看密码
	public function mobile_user_active(){
		$rurl = $this->gr('r');

		$id = $this->gr('id');
		
		$admin = false;

		// 编辑模式
		if( ! empty($id) ){

			$this->load->model('mobile/mobile_mall_model');

			$admin = $this->mobile_mall_model->get_mobile_bm_admin($id);

			if( ! $admin ){
				$this->alert('找不到该商户');
				exit();
			}
		}

		//var_dump($admin);

		$this->tpl->assign('admin', $admin);
		$this->tpl->assign('r', $rurl);
		$this->tpl->assign('module', 'mobile.bm.user_manage');
		$this->tpl->display('admin/mall/bm/bm_mobile_user_add.html');
	}

	// 添加&编辑 微网页商户查看密码设置 表单提交
	public function mobile_user_active_submit(){

		$r = $this->gf('r');

		$info = array();
		$info['id'] = $this->gf('id');
		$info['comp_name'] = $this->gf('comp_name');
		$info['page_id'] = $this->gf('page_id');
		$info['pswd'] = $this->gf('pswd');
		
		$this->load->model('mobile/mobile_mall_model');
		
		if( $info['page_id'] == '' ){
			$this->alert('微网页ID不能为空');
			exit();
		}

		if( $info['pswd'] == '' ){
			$this->alert('密码不能为空');
			exit();
		}

		// 新添加
		if( $info['id'] == 0 ){
			try{
				$this->mobile_mall_model->add_mobile_bm_admin($info);
				$this->alert('添加成功', $r);
			}catch(Exception $e){
				$this->alert($e->getMessage());
			}
		} else {	// 编辑模式
			try{
				$this->mobile_mall_model->edit_mobile_bm_admin($info);
				$this->alert('修改成功', $r);
			}catch(Exception $e){
				$this->alert($e->getMessage());
			}
		}


	}

	// 检测查看密码存在，以免重复添加
	private function _mobile_bm_admin_exists_check($pageid, $id=0){

		if( ! preg_match('/^\d+$/', $pageid) ) return false;

		$this->load->model('mobile/mobile_mall_model');

		return $this->mobile_mall_model->check_page_id_exists($pageid, $id);

	}
	public function mobile_bm_admin_exists_check(){

		$pageid = $this->gf('pageid');

		$id = $this->gf('id');

		if( ! $this->_mobile_bm_admin_exists_check($pageid, $id) ){
			echo('exists');
		} else {
			echo('0');
		}

	}
	
	// 删除一条查看密码
	public function mobile_user_delete(){
		$r = $this->gr('r');
		$id = $this->gr('id');
		try{
			$this->load->model('mobile/mobile_mall_model');
			$this->mobile_mall_model->delete_bm_admin($id);
			$this->alert('', $r);
		}catch(Exception $e){
			$this->alert($e->getMessage(), $r);
		}
	}
	
}

?>