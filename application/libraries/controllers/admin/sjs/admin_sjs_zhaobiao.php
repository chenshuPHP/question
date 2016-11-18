<?php

// 招标管理
class admin_sjs_zhaobiao extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function manage(){
		
		$this->load->model('sjs/sjs_zhaobiao_model');
		
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>20
		);
		
		$where = "1=1";
		$params = array();
		
		$filter = array(
			'recycle'=>$this->gr('recycle')
		);
		if( empty( $filter['recycle'] ) ) $filter['recycle'] = 'normal';
		
		$params[] = "recycle=" . $filter['recycle'];
		
		switch($filter['recycle']){
			case 'recycle':
				$where .= " and recycle = 1";
				break;
			case 'normal':
				$where .= " and recycle = 0";
				break;
			case 'all':
				$where .= "";
				break;
		}
		
		
		$sql = "select * from ( select id, username, type, pay_type, score_value, recycle, addtime, num = row_number() over( order by addtime desc ) from sjs_zhaobiao where ". $where ." ) as temp where num between ". (( $args['page'] - 1 ) * $args['size'] + 1) ." and " . ( $args['size'] * $args['page'] );
		$sql_count = "select count(*) as icount from [sjs_zhaobiao] where " . $where;
		
		$result = $this->sjs_zhaobiao_model->gets($sql, $sql_count);
		
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('module', 'manage');
		$this->tpl->assign('args', $args);
		$this->tpl->assign('filter', $filter);
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $args['page'];
		$this->pagination->pageSize = $args['size'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->url_template = $this->get_complete_url( 'sjs/zhaobiao/manage?page=<{page}>&' . implode('&', $params) );
		$this->pagination->url_template_first = $this->get_complete_url( 'sjs/zhaobiao/manage?' . implode('&', $params) );
		$pagination = $this->pagination->toString(true);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->display( $this->get_tpl('sjs/zhaobiao/manage.html') );
		
	}
	
	public function handler(){
		
		$this->load->model('sjs/sjs_zhaobiao_model');
		
		$info = $this->get_form_data();
		
		try{
			switch($info['active']){
				case 'recycle':
					$this->sjs_zhaobiao_model->recycle($info['id']);
					break;
				case 'recovery':
					$this->sjs_zhaobiao_model->recovery($info['id']);
					break;
				default:
			}
			$this->alert('提交成功', $info['rurl']);
		}catch(Exception $e){
			$this->alert( $e->getMessage() );
		}
		
		
		
	}
	
}

?>