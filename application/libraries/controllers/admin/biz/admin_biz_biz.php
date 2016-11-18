<?php

// 派发订单管理模型
class admin_biz_biz extends admin_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 订单派发管理
	public function manage(){
		
		$cfg = array('page'=>$this->gr('page'), 'size'=>20);
		if( ! preg_match('/^\d+$/', $cfg['page']) ) $cfg['page'] = 1;
		
		$this->load->model('biz/biz_model');
		$sql = "select * from (select id, title, addtime, adduser, bizid, row_number() over(order by id desc) as num from business) as temp where num between ". (($cfg['page']-1)*$cfg['size']+1) ." and " . $cfg['page'] * $cfg['size'];
		
		$sql_count = "select count(*) as icount from business";
		$result = $this->biz_model->get_list($sql, $sql_count);
		$result['list'] = $this->biz_model->company_assign_biz($result['list']);	// 附加公司信息到关联信息
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->url_template_first = $this->manage_url . 'biz/biz/manage';
		$this->pagination->url_template = $this->manage_url . 'biz/biz/manage?page=<{page}>';
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $result['count']);
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('module', 'manage');
		
		$this->tpl->display('admin/business/biz_manage.html');
		
	}
	
	// 订单基本信息预览
	public function detail(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		$this->load->model('biz/biz_model');
		$biz = $this->biz_model->get_biz($id);
		$biz = array_change_key_case($biz);
		$biz = $this->biz_model->company_assign_biz($biz);
		
		$biz['content'] = $this->encode->htmldecode($biz['content']);
		
		$r = str_replace('&amp;', '&', $r);
		$r = str_replace(';=', '=', $r);
		
		//var_dump2($r);
		
		
		$this->tpl->assign('biz', $biz);
		$this->tpl->assign('rurl', $r);
		
		
		
		
		$this->tpl->assign('module', 'detail');
		$this->tpl->display('admin/business/biz_detail.html');
		
	}
	
	// 派发详情
	public function relation(){
		
		$this->load->model('biz/biz_model');
		$this->load->model('biz/biz_state_model');
		
		$cfg = array('page'=>$this->gr('page'), 'size'=>20);
		if( ! preg_match('/^\d+$/', $cfg['page']) ) $cfg['page'] = 1;
		
		// 搜索参数
		$args = array(
			'name'=>iconv('gbk', 'utf-8', $this->gr('name')),
			'cmp'=>iconv('gbk', 'utf-8', $this->gr('cname')),
			'event'=>$this->gr('event'),
			'rebate'=>$this->gr('rebate')
		);
		
		$where = "1=1";
		$params = array();
		
		if( !empty($args['name']) ){
			$where .= " and businessid in (select id from business where title like '%". $args['name'] ."%')";
			array_push($params, 'name=' . $args['name']);
		}
		if( !empty($args['cmp']) ){
			$where .= " and username in (select username from company where company like '%". $args['cmp'] ."%')";
			array_push($params, 'cname=' . $args['cmp']);
		}
		if( !empty($args['event']) ) {
			$where .= " and stateid in (". $args['event'] .")";
			array_push($params, 'event=' . $args['event']);
		}
		
		if( $args['rebate'] != '' ){
			$where .= " and rebate in (". $args['rebate'] .")";
			array_push($params, 'rebate=' . $args['rebate']);
		}
		
		$params = implode('&', $params);
		
		// 查询派发 订单 <=> 会员 的记录
		$sql = "select * from (select id, businessid, username, distributuser, addtime, state, stateid, rebate, row_number() over(order by id desc) as num from distribut where ". $where .") as temp where num between ". (($cfg['page']-1)*$cfg['size']+1) ." and " . $cfg['page'] * $cfg['size'];
		
		$sql_count = "select count(*) as icount from distribut where " . $where;
		
		$result = $this->biz_model->get_relations($sql, $sql_count);
		$result['list'] = $this->biz_model->company_assign_distributs($result['list']);		// 附加会员单位资料
		$result['list'] = $this->biz_model->biz_assign_distribut($result['list']);			// 附加订单资料
		
		// 获取一条跟踪记录( 状态为已签约, 和记录提交时间 ) 为了获取签约时间
		$result['list'] = $this->biz_state_model->tracks_assign($result['list'], array(
			'size'=>1,
			'fields'=>'id, did, stateid, addtime',
			'where'=>'stateid=4',
			'order'=>'order by addtime asc'
		));
		
		if( $args['cmp'] != '' ){
			// 如果是公司搜索, 则需要附加其他公司是否量房状态到订单
			$result['list'] = $this->biz_model->company_assign_biz($result['list'], array(
				'key_name'=>'businessid',
				'fields'=>'id, businessid, username, state, stateid',
				'where'=>'stateid >= 3'
			));
			// 检测一下是否有量房以上状态的
			foreach($result['list'] as $key=>$value){
				$value['valid'] = false;
				if( $value['distribut'] ){
					foreach($value['distribut'] as $item){
						if( $item['stateid'] >= 3 ){
							$value['valid'] = true;
							break;
						}
					}
				}
				$result['list'][$key] = $value;
			}
			// var_dump2($result['list']);
		}
		
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		
		if( empty($params) ){
			$this->pagination->url_template_first = $this->manage_url . 'biz/biz/relation';
			$this->pagination->url_template = $this->manage_url . 'biz/biz/relation?page=<{page}>';
		} else {
			$this->pagination->url_template_first = $this->manage_url . 'biz/biz/relation?' . $params;
			$this->pagination->url_template = $this->manage_url . 'biz/biz/relation?page=<{page}>&' . $params;
		}
		
		
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $result['count']);
		$pagination = $this->pagination->toString(true);
		
		$states = $this->biz_state_model->get_states();
		
		$this->tpl->assign('module', 'relation');
		
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('states', $states);
		$this->tpl->assign('args', $args);
		
		$this->tpl->display('admin/business/biz_relation.html');
		
	}
	
	
}

?>