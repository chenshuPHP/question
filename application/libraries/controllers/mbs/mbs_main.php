<?php

// 主页面
class mbs_main extends mbs_base {

	public function __construct(){
		parent::__construct();
	}

	public function home(){
		
		$this->load->model('mobile/mobile_mall_model');

		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>21
		);

		$admin = $this->admin;

		$sql = "select * from ( select id, page_id, page_name, data, addtime, ip, num = row_number() over(order by addtime desc) from zxtg_bm_mobile where page_id = '". $admin['page_id'] ."' ) as temp where num between ". (($args['page'] - 1) * $args['size'] + 1) ." and " . ( $args['page'] * $args['size'] );

		$sql_count = "select count(*) as icount from zxtg_bm_mobile where page_id = '". $admin['page_id'] ."'";

		$res = $this->mobile_mall_model->get_bm_list($sql, $sql_count);

		$this->load->library('pagination');

		$this->pagination->currentPage = $args['page'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->recordCount = $res['count'];
		$this->pagination->template_url = $this->get_mbs_complete_url('/main?page=<{page}>');
		$this->pagination->template_url_first = $this->get_mbs_complete_url('/main');
		
		$this->tpl->assign('pagination', $this->pagination->toString(true));

		$this->tpl->assign('list', $res['list']);
		$this->tpl->assign('count', $res['count']);

		$this->tpl->assign('module', 'bm.data');
		$this->tpl->display('mbs/main.html');
	}
	
	// 登出
	public function logout(){
		try{
			$this->load->model('mobile/mbs_model');
			$this->mbs_model->logout();
			$this->alert('', $this->get_mbs_complete_url('/login'));
		}catch(Excepion $e){
			$this->alert($e->getMessage());
		}
	}
	
}

?>