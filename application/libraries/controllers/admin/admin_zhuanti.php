<?php

	class admin_zhuanti extends admin_base {
		
		function __construct(){
			parent::__construct();
		}
		
		// 专题管理列表页
		function index(){
			
			$page = $this->encode->get_page();
			
			$settings = array(
				'page'=>$page,
				'size'=>20
			);
			
			$this->load->model('zhuanti/ZhuantiModel', 'zhuanti_model');
			$result = $this->zhuanti_model->get_list($settings);
			
			$this->tpl->assign('list', $result['list']);
			$this->tpl->assign('count', $result['count']);
			$this->tpl->assign('settings', $settings);
			
			// 分页代码
			$this->load->library('pagination');
			
			$this->pagination->currentPage = $settings['page'];
			$this->pagination->recordCount = $result['count'];
			$this->pagination->pageSize = $settings['size'];
			
			
			$this->pagination->url_template = $this->get_complete_url( '/zhuanti/index?page=<{$page}>' );
			$this->pagination->url_template_first = $this->get_complete_url( '/zhuanti/index' );
			$pagination = $this->pagination->toString(true);
			
			$this->tpl->assign('pagination', $pagination);
			
			$this->tpl->assign('module', 'manage');
			
			$this->tpl->display('admin/zhuanti/index.html');
			
		}
		
		// 修改表单 界面
		public function edit(){
			
			$id = $this->gr('id');
			$r = $this->gr('r');
			
			$this->load->model('zhuanti/ZhuantiModel', 'zhuanti_model');
			$object = $this->zhuanti_model->get_object($id);
			$referer = urlencode( $_SERVER['HTTP_REFERER'] );	// 来源页面
			$this->tpl->assign('object', $object);
			$this->tpl->assign('rurl', $r);
			$this->tpl->assign('module', 'edit');
			$this->tpl->display('admin/zhuanti/edit.html');
		}
		
		
		function edit_submit(){
			$error = array();
			
			$this->load->library('encode');
			
			$object = array(
				'id'=>$this->encode->getFormEncode('id'),
				'name'=>$this->encode->getFormEncode('name'),
				'label'=>$this->encode->getFormEncode('label'),
				'planner'=>$this->encode->getFormEncode('planner'),
				'oth_users'=>$this->encode->getFormEncode('oth_users'),
				'addtime'=>date('Y-m-d H:i:s')
			);
			
			if( $object['name'] == '' || $object['label'] == '' ){
				array_push($error, "专题名称和标签不都不能为空");
			} else {
			
				$this->load->model('zhuanti/ZhuantiModel', 'zhuanti_model');
				
				$check_exists = $this->zhuanti_model->check_label_exists($object['label'], $object['id']);	// 检测label是否有重复
				
				if( $check_exists == true ){
					$this->zhuanti_model->edit($object);
				} else {
					array_push($error, "已经存在的标识符,请重新选择");
				}
			}
			
			if( count( $error ) == 0 ){
				echo('<script type="text/javascript">alert("修改成功");location.href="'.  $this->gf('rurl') .'";</script>');
			} else {
				$this->tpl->assign('error', $error);
				$this->tpl->assign('object', $object);
				$content = $this->tpl->fetch('admin/zhuanti/edit.html');
				$this->display($content);
			}
			
		}
		
		// 添加专题页面
		public function add(){
			$this->tpl->assign('module', 'add');
			$this->tpl->display('admin/zhuanti/add.html');
		}
		
		// 添加专题页面 表单提交处理程序
		function add_submit(){
			
			$error = array();
			
			$this->load->library('encode');
			
			$object = array(
				'name'=>$this->encode->getFormEncode('name'),
				'label'=>$this->encode->getFormEncode('label'),
				'planner'=>$this->encode->getFormEncode('planner'),
				'oth_users'=>$this->encode->getFormEncode('oth_users'),
				'addtime'=>date('Y-m-d H:i:s')
			);
			
			if( $object['name'] == '' || $object['label'] == '' ){
				array_push($error, "专题名称和标签不都不能为空");
			} else {
			
				$this->load->model('zhuanti/ZhuantiModel', 'zhuanti_model');
				
				$check_exists = $this->zhuanti_model->check_label_exists( $object['label'] );	// 检测label是否有重复
				
				if( $check_exists == true ){
					$this->zhuanti_model->add( $object );
				} else {
					array_push($error, "已经存在的标识符,请重新选择");
				}
			}
			
			if( count( $error ) == 0 ){
				echo('<script type="text/javascript">alert("添加成功");location.href="index";</script>');
			} else {
				$this->tpl->assign('error', $error);
				$this->tpl->assign('object', $object);
				$content = $this->tpl->fetch('admin/zhuanti/add.html');
				$this->display($content);
			}
		}
		
		
	}

?>