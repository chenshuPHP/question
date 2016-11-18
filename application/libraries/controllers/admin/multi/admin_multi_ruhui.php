<?php

// 入会申请管理
class admin_multi_ruhui extends admin_base {
	
	public function __construct(){
		parent::__construct();
		$this->load->model('multi/ruhui_model');
		$this->tpl->assign('module', 'ruhui');
		$this->tpl->assign('module_manage_level', $this->ruhui_model->manage_level);
	}
	
	public function manage(){
		
		$this->load->library('pagination');
		
		$args = array(
			'size'=>30,
			'page'=>$this->encode->get_page()
		);
		
		$search = array(
			'key'=>$this->gr('key'),
			'admin'=>$this->gr('admin'),
			'state'=>$this->gr('state')
		);
		
		if( is_array($search['state']) ) $search['state'] = implode(',', $search['state']);
		
		$where_sql = "1=1";
		$params_string = "1=1";
		if( ! empty( $search['key'] ) ){
			$search['key'] = $this->encode->gbk_to_utf8($search['key']);
			$where_sql .= " and ( deco_name like '%". $search['key'] ."%' or name like '%". $search['key'] ."%' or addr like '%". $search['key'] ."%' or mobile = '". $search['key'] ."' )";
			$params_string .= "&key=" . $search['key'];
		}
		
		if( ! empty( $search['admin'] ) ){
			
			// 部分用户的用户名是中文的,所以这里也需要转码
			$search['admin'] = $this->encode->gbk_to_utf8($search['admin']);
			
			$where_sql .= " and id in ( select fid from [admin_manage_relation] where tab = 'send_ruhui' and admin = '". $search['admin'] ."' )";
			$params_string .= "&admin=" . $search['admin'];
		}
		
		if( $search['state'] != '' ){
			if( preg_match('/^\d+(\,\d+)*$/', $search['state']) ){
				$where_sql .= " and stateid in (". $search['state'] .")";
			} else {
				echo('[检索状态]参数格式错误');
			}
			$params_string .= "&state=" . $search['state'];
		}
		
		
		// 管理员搜索指派
		$this->load->model('manager/manager_model');
		$admins = $this->manager_model->get_admins("select id, username, fullname from admin_manage where lockstate = 0 order by id desc");
		$this->tpl->assign('admins', $admins['list']);
		// 订单状态搜索, 加载搜索选项到页面
		$this->load->model('multi/ruhui_track_model');
		$this->tpl->assign('states', $this->ruhui_track_model->get_types());
		
		
		// 如果权限小于既定的管理权限，则只可以看到自己的数据
		// 取消了这里的限制 2016-08-30
		//if( $this->admin_level > $this->ruhui_model->manage_level ){
		//	$where_sql .= " and id in ( select fid from [admin_manage_relation] where tab = 'send_ruhui' and admin = '". $this->admin_username ."' )";
		//}
		$sql = "select * from ( select id, deco_name, name, mobile, addtime, next_visit_date, stateid, rel, qq, num = row_number() over( order by addtime desc ) from send_ruhui where ". $where_sql ." ) as temp where num between ". (($args['page'] - 1) * $args['size'] + 1) ." and " . ( $args['page'] * $args['size'] );
		
		$sql_count = "select count(*) as icount from send_ruhui  where " . $where_sql;
		
		$list = $this->ruhui_model->gets($sql, $sql_count);
		
		// 附加分配的管理员数据
		$this->load->model('manager/manager_relation_model');
		$this->load->model('multi/ruhui_track_model');
		
		$list['list'] = $this->manager_relation_model->assign_list($list['list'], 'send_ruhui');	// 附加分配的管理员数据
		$list['list'] = $this->ruhui_track_model->state_assign($list['list']);
		
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $list['count'];
		$this->pagination->pageSize = $args['size'];
		
		if( empty($params_string) ){
			$this->pagination->url_template = $this->get_complete_url('/multi/ruhui/manage?page=<{page}>');
			$this->pagination->url_template_first = $this->get_complete_url('/multi/ruhui/manage');
		} else {
			$this->pagination->url_template = $this->get_complete_url('/multi/ruhui/manage?page=<{page}>&' . $params_string);
			$this->pagination->url_template_first = $this->get_complete_url('/multi/ruhui/manage?' . $params_string);
		}
		
		
		$this->tpl->assign('pagination', $this->pagination->toString(true));
		$this->tpl->assign('list', $list['list']);
		$this->tpl->assign('module', 'ruhui.manage');
		$this->tpl->assign('args', $args);
		
		$this->tpl->assign('search', $search);
		
		//$uri = $_SERVER['REQUEST_URI'];
		//echo( urlencode($uri) );
		//echo( urldecode($uri) );
		
		$this->tpl->display( $this->get_tpl('multi/ruhui/manage.html') );
	}
	
	// 返回的URL结果几次传递之后需要格式化
	private function _format_rurl($url){
		// 返回URL的格式化
		$url = urldecode( $url );
		$url = str_replace('&amp;', '&', $url);
		$url = str_replace(';', '', $url);
		return $url;
	}
	
	public function view(){
		
		$id = $this->gr('id');
		
		$r = $this->_format_rurl( $this->gr('r') );
		
		$info = $this->ruhui_model->get($id, array(
			'fields'=>'id, deco_name, name, mobile, addtime, addr, cert, type, stateid, capital, rel, qq, email'
		));
		
		$this->tpl->assign('config', $this->ruhui_model->config);
		$this->tpl->assign('info', $info);
		$this->tpl->assign('rurl', $r);
		$this->tpl->assign('module', 'ruhui.view');
		
		// 管理员指派
		$this->load->model('manager/manager_model');
		$admins = $this->manager_model->get_admins("select id, username, fullname from admin_manage where lockstate = 0 order by id desc");
		$this->tpl->assign('admins', $admins['list']);
		
		$this->load->model('manager/manager_relation_model');
		$this->tpl->assign('rels', $this->manager_relation_model->get($id, 'send_ruhui'));
		
		// 跟踪
		$this->load->model('multi/ruhui_track_model');
		$this->load->model('manager/manager_model');
		$track_types = $this->ruhui_track_model->get_types();
		
		$tracks = $this->ruhui_track_model->gets($info['id']);
		$tracks = $this->manager_model->assign($tracks);
		
		$this->tpl->assign('tracks', $tracks);
		$this->tpl->assign('track_types', $track_types);
		$this->tpl->display( $this->get_tpl('multi/ruhui/view.html') );
	}
	
	
	public function handler(){
		
		if( $this->admin_level > 10 ) exit('无法修改');
		
		$data = $this->get_form_data();
		
		$id = $data['id'];
		if( ! isset($data['admin']) )
			$admins = array();
		else
			$admins = $data['admin'];
		
		// 用户指派
		$this->load->model('manager/manager_relation_model');	// 管理员关系模型
		try{
			$this->manager_relation_model->set(array(
				'id'=>$id, 'admins'=>$admins, 'table'=>'send_ruhui'
			));
			$this->alert('设置成功', $this->_format_rurl( $data['r'] ));
		}catch(Exception $e){
			$this->alert( $e->getMessage() );
			exit();
		}
		
	}
	
	// 跟踪
	public function track_handler(){
		$info = $this->get_form_data();
		$info['admin'] = $this->admin_username;
		$this->load->model('multi/ruhui_track_model');
		
		$rurl = $info['rurl'];
		$rurl = str_replace('&amp;', '&', $rurl);
		
		try{
			$this->ruhui_track_model->add($info);
			$this->alert('', $rurl);
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
	}
	
	private function move()
	{
		$this->load->library('mdb');
		$this->mdb->select_db('xiehui');
		
		$res = $this->mdb->query("select jid, track, admin, TK.addtime, JOINUS.tel from ".
		"[sz_joinus_track] as TK left join [sz_joinus] as JOINUS ON JOINUS.id = TK.jid");
		
		$tels = array();
		foreach( $res as $item )
		{
			$tels[] = $item['tel'];
		}
		
		$this->mdb->select_db('shzh');
		
		$_tmp = $this->mdb->query("select id, mobile from [send_ruhui] where mobile in ('". implode("','", $tels) ."')");
		
		foreach($res as $key=>$value)
		{
			$value['jid'] = 0;
			foreach( $_tmp as $item )
			{
				if( $item['mobile'] == $value['tel'] )
				{
					$value['jid'] = $item['id'];
					break;
				}
			}
			$res[$key] = $value;
		}
		
		var_dump2( $res );
		
		
		
		foreach( $res as $item )
		{
			 //$this->mdb->insert("insert into send_ruhui_track(fid, detail, stateid, admin, addtime)".
			//"VALUES('". $item['jid'] ."', '". $item['track'] ."', '10', 'daiyi', '". $item['addtime'] ."')");
		}
		
		echo('done');
		
	}
	
}






















?>