<?php

// reserve 简写 rev

class member_biz extends member_base {
	
	private $username;
	
	public function __construct(){
		parent::__construct();
		$info = $this->login;
		$this->username = $info['username'];
		
		$this->tpl->assign('module', 'biz');
		
	}
	
	// 预约信息列表管理
	public function reserve_manage(){
		$this->load->model('publish/reserve', 'rev_model');
		$rev_count = $this->rev_model->get_reserve_count($this->username);	// 当前店铺预约数量
		$this->tpl->assign('rev_count', $rev_count);
		$page = $this->encode->get_request_encode('page');
		$page = empty($page) ? 1 : $page;
		$cfg = array(
			'size'=>20,
			'page'=>$page
		);
		$sql = "select top ". $cfg['size'] ." id , fullname, shenid, localid, area, addtime, deco_type, deco_home_type, deco_bao from zh_booking where id not in (select top ". ($cfg['page']-1)*$cfg['size'] ." id from zh_booking where users like '%,". $this->username .",%' order by addtime desc) and users like '%,". $this->username .",%' order by addtime desc";
		$sql_count = "select count(*) as icount from zh_booking where users like '%,". $this->username .",%'";
		$res = $this->rev_model->get_list($sql, $sql_count);
		$list = $res['list'];
		foreach($list as $key=>$val){
			$list[$key]['view_state'] = $this->rev_model->check_view_state($val['id'], $this->username);
		}
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->url_template_first = 'reserve_manage';
		$this->pagination->url_template = 'reserve_manage?page=<{page}>';
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $res['count']);
		$pagination = $this->pagination->toString(true);
		$this->tpl->assign('pagination', $pagination);
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->assign('list', $list);
		$this->tpl->assign('r', urlencode( $_SERVER['REQUEST_URI'] ));
		$this->tpl->assign('module', 'reserve');
		$this->tpl->display('member/biz/reserve_manage.html');
		
	}
	
	// 详细浏览
	public function reserv_view(){
		
		$r = $this->encode->get_request_encode('r');
		$rid = $this->encode->get_request_encode('id');
		
		$this->load->model('publish/reserve', 'rev_model');
		$object = $this->rev_model->get_rev($rid, $this->username);
		
		$this->rev_model->add_view_record($rid, $this->username);	// 添加浏览记录
		
		$this->tpl->assign('object', $object);
		$this->tpl->assign('r', $r);
		$this->tpl->display('member/biz/reserve_view.html');
		
	}
	
	// 客服发单管理列表
	public function manage(){
		
		$this->load->model('biz/biz_model');
		
		$cfg = array(
			'page'=>$this->gr('page'),
			'size'=>20
		);
		if( ! preg_match('/^[1-9]\d*$/', $cfg['page']) ) $cfg['page'] = 1;
		
		//$sql = "select * from (select id, title, addtime, adduser, bizid, row_number() over(order by id desc) as num from business where ) as temp where num between ". (($cfg['page']-1)*$cfg['size']+1) ." and " . $cfg['page'] * $cfg['size'];
		
		$sql = "select * from (select id, businessid, username, addtime, showstate, state, stateid, row_number() over(order by id desc) as num from distribut where username = '". $this->username ."') as temp where num between ". (($cfg['page']-1)*$cfg['size']+1) ." and " . $cfg['page'] * $cfg['size'];
		
		$sql_count = "select count(*) as icount from distribut where username = '". $this->username ."'";
		$result = $this->biz_model->get_relations($sql, $sql_count);
		$result['list'] = $this->biz_model->biz_assign_distribut($result['list'], 'id, title, addtime, adduser, bizid, content');
		
		if( $result['list'] != false ){
			foreach($result['list'] as $key=>$val){
				$fields = array();
				$content = $val['business']['content'];
				$content = $this->encode->htmldecode($content);
				
				$result['list'][$key]['business']['content'] = $content;
				$fields['area'] = $this->_get_field('bis_area', $content);
				if( empty($fields['area']) ){
					$fields['area'] = $this->_get_field('bis_house_area', $content);
				}
				$fields['budget'] = $this->_get_field('bis_budget', $content);
				$fields['bao'] = $this->_get_field('bis_inv', $content);
				$fields['city'] = $this->_get_field('bis_addr01', $content);
				$fields['name'] = $this->_get_field('bis_xingming', $content);
				$result['list'][$key]['fields'] = $fields;
				
			}
		}
		
		$this->load->library('pagination');
		$this->pagination->currentPage = $cfg['page'];
		$this->pagination->url_template_first = '/member_v2/biz/manage';
		$this->pagination->url_template = '/member_v2/biz/manage?page=<{page}>';
		$this->pagination->pageCount = $this->pagination->getPageCount($cfg['size'], $result['count']);
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('module', 'business');
		$this->tpl->assign('cfg', $cfg);
		$this->tpl->display('member/biz/biz_manage.html');
	}
	
	// 从订单内容中提取元素的值
	private function _get_field($label, $content){
		preg_match('/<span([^>]*?)id=[\'\"]'. $label .'[\'\"](.*?)>(.*?)<\/span>/', $content, $match);
		if( count($match) < 4 ){
			return false;
		} else {
			return $match[3];
		}
	}
	
	// 打开订单 / 订单浏览
	public function open_biz(){
		
		$id = $this->gr('id');
		$r = $this->gr('r');
		
		$this->load->model('biz/biz_model');
		$this->load->model('biz/biz_state_model');	// 订单状态操作类
		
		$relation = $this->biz_model->get_relation($id, $this->username);
		
		
		if( $relation == false ){
			exit('没有找到数据，请确定是否有权限访问');
		}
		
		// 检测是否是新订单
		if( $relation['stateid'] <= 0 || empty($relation['stateid']) ){
			try{
				$this->biz_state_model->open_biz($relation['id'], $this->username);	// 打开订单，将订单状态变更为以查看
			}catch(Exception $e){
				exit($e->getMessage());
			}
		}
		
		$relation = $this->biz_model->biz_assign_distribut($relation, 'id, title, addtime, adduser, bizid, content');
		$relation['business']['content'] = $this->encode->htmldecode($relation['business']['content']);
		
		// 反馈表单中可供选择的进度选项
		$states = $this->biz_state_model->get_states();
		// 跟踪数据
		$tracks = $this->biz_state_model->get_tracks($relation['id']);
		
		// 派单其他会员的信息
		$relations = $this->biz_model->company_assign_biz(array('id'=>$relation['business']['id']));
		if( ! isset($relations['distribut']) || count($relations['distribut']) == 0 ){
			$relations['distribut'] = false;
		}
		$relations = $relations['distribut'];
		
		//echo('<!--');
		//var_dump($relations);
		//echo('-->');
		
		
		$this->tpl->assign('biz', $relation);
		$this->tpl->assign('states', $states);
		$this->tpl->assign('tracks', $tracks);
		$this->tpl->assign('relations', $relations);
		$this->tpl->assign('r', $r);
		$this->tpl->assign('module', 'business');
		$this->tpl->display('member/biz/biz_view.html');
	}
	
	// 提交跟踪信息
	public function track(){
		
		$info = array(
			'did'=>$this->gf('did'),
			'even'=>$this->gf('even'),
			'content'=>$this->gf('content'),
			'username'=>$this->username
		);
		
		$this->load->model('biz/biz_state_model');
		
		$result = array();
		try{
			
			$track_id = $this->biz_state_model->biz_state_change($info['did'], $info['even'], $info['username'], $info['content'], 'user');
			
			// 增加口碑值
			$this->load->model('company/company_koubei_model');
			$this->company_koubei_model->biz_track($this->base_user, $track_id, array(
				'description'		=> '反馈派发记录: ' . $info['did'] . ' - ' . $this->biz_state_model->get_state_name($info['even']) . ' - ' . $info['content']
			));
			
			$result['type'] = 'success';
		}catch(Exception $e){
			$result['type'] = 'error';
			$result['message'] = $e->getMessage();
		}
		
		echo(json_encode($result));
		
	}
	
}
?>