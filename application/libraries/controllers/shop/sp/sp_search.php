<?php

class sp_search extends sp_base {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function home()
	{
		$tpl = $this->get_tpl('search/home.html');
		
		$keyword = $this->gr('key');
		
		if( empty( $keyword ) ) exit('搜索关键词不允许为空');
		
		if( $keyword != '' )
		{
			$keyword = $this->encode->gbk_to_utf8($keyword);
		}
		
		$args = array(
			'page'		=> $this->encode->get_page(),
			'size'		=> 12
		);
		
		$this->load->model('company/project_category_model');
		
		$where = "username = '". $this->user ."' and fm_image <> '' and recycle = 0 and (casename like '%". $keyword ."%' OR address like '%". $keyword ."%')";
		
		$sql = "select * from ( select id, username, casename, address, edate, fm_image, build_type_1, ".
		"build_type_2, num = row_number() over( order by edate desc, addtime desc ) ".
		"from [user_case] where ". $where ." ) as tmp where num between ". (( $args['page'] - 1 ) * $args['page'] + 1) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from [user_case] where " . $where;
		
		$result = $this->project_model->get_case_list( $sql, $sql_count );
		$result['list'] = $this->project_category_model->assign_to_project( $result['list'] );
		$result['list'] = $this->project_model->image_count_assign_case( $result['list'] );
		
		// 缩略图
		foreach($result['list'] as $key=>$value)
		{
			$result['list'][$key]['thumb'] = $this->thumb->crop($value['fm_image'], 310, 195);
		}
		
		$this->load->library('pagination');
		$this->pagination->pageSize = $args['size'];
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->url_template = $this->get_complete_url('search?page=<{page}>key=' . $keyword);
		$this->pagination->url_template_first = $this->get_complete_url('search?key=' . $keyword);
		
		var_dump2( $this->pagination->toString( TRUE ) );
		var_dump2( $result['list'] );
		var_dump2( $keyword );
		
		$this->tpl->assign('pagination', $this->pagination->toString( TRUE ));
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('keyword', $keyword);
		
		$this->tpl->display( $tpl );
	}
	
}


?>