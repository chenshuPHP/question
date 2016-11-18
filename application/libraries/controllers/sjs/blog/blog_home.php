<?php

// BLOG home
class blog_home extends blog_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		
		$this->load->model('sjs/sjs_case');
		
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>5
		);
		
		$where = "recycle = 0 and fm <> '' and username = '". $this->blog_user ."'";
		
		// 读取案例
		$sql = "select * from ( select id, username, case_name, detail, addtime, type_id, style_id, price, num = row_number() over(order by addtime desc) from [sjs_case] where ". $where ." ) as temp where num between ". (( $args['page'] - 1 ) * $args['size'] + 1) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from [sjs_case] where " . $where;
		
		$pros = $this->sjs_case->gets($sql, $sql_count);
		
		$this->sjs_case->image_assign($pros['list'], array(
			'size'=>4
		));
		$pros['list'] = $this->sjs_case->assign_image_count($pros['list']);
		
		foreach($pros['list'] as $key=>$val){
			$pros['list'][$key]['detail'] = $this->encode->htmldecode($val['detail'], true);
		}
		
		$this->tpl->assign('pros', $pros['list']);
		
		$this->tpl->display('sjs/blog/home.html');
		
	}
	
}


?>