<?php

// 设计师频道 - 项目主页
class sjs_project extends sjs_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		
		$this->load->model('sjs/sjs_case_config_model');
		$this->load->model('sjs/sjs_case', 'sjs_case_model');
		
		// ID = 1 为风格
		$styles = $this->sjs_case_config_model->get(1);
		$styles = $this->sjs_case_config_model->assign_childs($styles);
		
		$settings = array(
			'type_id'=>$this->gr('tid'),
			'tsid'=>$this->gr('tsid'),
			'style_id'=>$this->gr('sid')
		);
		
		$parent_type_id = $settings['type_id'];
		if( $parent_type_id == '' ) $parent_type_id = 14;
		$this->tpl->assign('jiazhuang', $parent_type_id == 14 ? 'gongzhuang' : 'jiazhuang');
		
		// ID = 2 房型
		$types = $this->sjs_case_config_model->get(2);
		$types = $this->sjs_case_config_model->assign_childs($types);
		$types = $this->sjs_case_config_model->assign_childs_childs($types);
		
		$current_types = NULL;
		foreach($types['childs'] as $key=>$item){
			if( $item['id'] == $parent_type_id ){
				$current_types = $item;
				break;
			}
		}
		
		$args = array(
			'size'=>16,
			'page'=>$this->encode->get_page()
		);
		
		if( $settings['tsid'] != '' ){
			$type = $this->sjs_case_config_model->get($settings['tsid']);
		} else {
			$type = $current_types['childs'][0];
		}
		
		
		
		$where = "fm <> '' and recycle = 0";
		// $where .= " and type_id = " . $type['id'];
		
		$params = array();
		
		if( $settings['style_id'] != '' ){
			$where .= " and style_id = '". $settings['style_id'] ."'";
			$params[] = "sid=" . $settings['style_id'];
		}
		
		if( $settings['tsid'] != '' ){
			$params[] = "tsid=" . $settings['tsid'];
		}
		
		if( $settings['type_id'] != '' ){
			$params[] = "tid=" . $settings['type_id'];
		}
		
		// 案例列表
		$sql = "select * from ( select id, username, case_name, type_id, style_id, fm, num = row_number() over( order by addtime desc ) from [sjs_case] where ". $where ." ) as temp where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['page'] * $args['size'] );
		$sql_count = "select count(*) as icount from sjs_case where " . $where;
		$result = $this->sjs_case_model->gets($sql, $sql_count);
		
		$result['list'] = $this->sjs_case_config_model->assign_config($result['list']);
		
		$this->load->library('pagination');
		
		$this->pagination->currentPage = $args['page'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->recordCount = $result['count'];
		
		if( count($params) == 0 ){
			$this->pagination->url_template = $this->get_complete_url('/project?page=<{page}>');
			$this->pagination->url_template_url = $this->get_complete_url('/project');
		} else {
			$this->pagination->url_template = $this->get_complete_url('/project?page=<{page}>&' . implode('&', $params));
			$this->pagination->url_template_url = $this->get_complete_url('/project?' . implode('&', $params));
		}
		
		
		
		$this->tpl->assign('pagination', $this->pagination->toString(true));
		$this->tpl->assign('def_type_id', $parent_type_id);
		$this->tpl->assign('cls_styles', $styles);
		$this->tpl->assign('types', $types);
		$this->tpl->assign('current_types', $current_types);
		
		$this->tpl->assign('settings', $settings);
		$this->tpl->assign('list', $result['list']);
		$this->tpl->display('sjs/project.html');
		
	}
	
	
}

?>