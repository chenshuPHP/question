<?php

class admin_sjs_register extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function manage(){
		
		$this->load->model('sjs/sjs_user_model');
		$this->load->model('sjs/sjs_config_model');
		
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>20
		);
		
		$filter = array(
			'recycle'=>''
		);
		
		$filter['recycle'] = $this->gr('recycle');
		if( empty($filter['recycle']) ) $filter['recycle'] = $this->gf('recycle');
		
		$where = 'register = 3';
		$param = array();
		
		if( $filter['recycle'] != 1 ){
			$where .= " and delcode = 0";
			$this->tpl->assign('module', 'manage');
		} else {
			$where .= " and delcode = 1";
			$this->tpl->assign('module', 'recycle');
			$param[] = "recycle=1";
		}
		
		
		$sql = "select * from ( select username, rejion, adept, puttime, num = row_number() over(order by puttime desc) from company where ". $where ." ) as temp where num between ". ( ($args['page'] - 1) * $args['size'] + 1 ) ." and " . ( $args['size'] * $args['page'] );
		$sql_count = "select count(*) as icount from [company] where " . $where;
		
		$result = $this->sjs_user_model->gets($sql, $sql_count);
		$result['list'] = $this->sjs_user_model->assign($result['list'], array(
			'fields'=>'username, true_name, shen, city, town, tel, mobile'
		));
		$result['list'] = $this->sjs_config_model->assign_adept($result['list']);
		
		// var_dump($result);
		
		$this->load->library('pagination');
		$this->pagination->pageSize = $args['size'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->currentPage = $args['page'];
		if( count($param) == 0 ){
			$this->pagination->url_template = $this->get_complete_url( '/sjs/register/manage?page=<{page}>' );
			$this->pagination->url_template_first = $this->get_complete_url( '/sjs/register/manage' );
		} else {
			$this->pagination->url_template = $this->get_complete_url( '/sjs/register/manage?page=<{page}>&' . implode("&", $param) );
			$this->pagination->url_template_first = $this->get_complete_url( '/sjs/register/manage?' . implode("&", $param) );
		}
		
		$this->tpl->assign('pagination', $this->pagination->toString(true)); 
		$this->tpl->assign('args', $args); 
		$this->tpl->assign('list', $result['list']);
		
		$this->tpl->assign('filter', $filter);
		$this->tpl->display( $this->get_tpl('sjs/register/manage.html') );
		
	}
	
	// 设置用户到回收站
	public function handler(){
		$info = $this->get_form_data();
		$this->load->model('sjs/sjs_user_model');
		try{
			
			switch($info['active']){
				case 'recycle':
					$this->sjs_user_model->recycle($info['users']);
					echo('success');
					break;
				case 'recovery':
					$this->sjs_user_model->recovery($info['users']);
					echo('success');
					break;
				default:
					echo("未知的处理指令");
					break;
			}
			
			
		}catch(Exception $e){
			echo($e->getMessage());
		}
	}
	
	
}

?>