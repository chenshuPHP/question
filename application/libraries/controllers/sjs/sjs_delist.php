<?php

// 设计师列表页 
class sjs_delist extends sjs_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		
		$this->load->model('city_model');
		$this->load->model('sjs/sjs_user_model', 'sjs_user_model');
		$this->load->model('sjs/sjs_config_model');
		$this->load->model('sjs/sjs_case');
		
		$styles_cls = $this->sjs_user_model->get_style_enum();
		$types = $this->sjs_user_model->get_field_enum();
		$this->tpl->assign('styles_cls', $styles_cls);
		$this->tpl->assign('types', $types);
		
		// 读取设计师数据
		$args = array(
			'size'=>20,
			'page'=>$this->encode->get_page()
		);
		
		$where = "cpy.username = info.username and cpy.register = 3 and cpy.delcode = 0";
		
		$sql = "select * from ( select cpy.username, info.true_name, info.oth_style, info.oth_field, info.face_image, info.city, num = row_number() over( order by cpy.puttime desc ) from [company] as cpy, sjs_info as info where ". $where ." ) as temp where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['size'] * $args['page'] );
		$sql_count = "select count(*) as icount from [company] as cpy, sjs_info as info where " . $where;
		$result = $this->sjs_user_model->gets($sql, $sql_count, array(
			'format'=>true
		));
		
		$result['list'] = $this->sjs_config_model->assign_field($result['list']);
		$result['list'] = $this->city_model->assign($result['list'], array(
			'in_label'=>'city',
			'out_label'=>'city_o'
		));
		
		// 获取每个用户的 5 个案例
		$users = array();
		foreach($result['list'] as $item) $users[] = $item['username'];
		$case_sql = "select * from ( select id, username, fm, num = row_number() over(partition by username order by addtime desc) from [sjs_case] where username in ('". implode("','", $users) ."') and fm <> '' and recycle = 0) as tmp where num <= 5";
		
		$cases = $this->sjs_case->gets($case_sql);
		$cases = $cases['list'];
		foreach($result['list'] as $key=>$value){
			$value['cases'] = array();
			foreach($cases as $case){
				if( $value['username'] == $case['username'] ){
					$value['cases'][] = $case;
				}
			}
			$result['list'][$key] = $value;
		}
		unset($cases);
		
		$this->load->library('pagination');
		$this->pagination->recordCount = $result['count'];
		$this->pagination->currentPage = $args['page'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->url_template = $this->get_complete_url('delist?page=<{page}>');
		$this->pagination->url_template_first = $this->get_complete_url('delist');
		
		$this->tpl->assign('pagination', $this->pagination->toString(true));
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('title', '设计师列表');
		$this->tpl->display('sjs/delist.html');
		
	}
	
}






























?>