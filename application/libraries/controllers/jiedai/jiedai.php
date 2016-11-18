<?php

	class jiedai extends MY_Controller {
		function __construct(){
			parent::__construct();
			
			error_reporting(E_ALL);
			
		}
		// jiedai首页
		function index(){
			$this->tpl->assign('title', '无抵押贷款-合作介绍-上海装潢网');
			$this->tpl->assign('t', 'hezuojianjie');
			$this->tpl->display('jiedai/hezuojianjie.html');
			//$this->tpl->display('jiedai/index.html');
		}
		
		function huiyuanquanli(){
			$this->tpl->assign('title', '无抵押贷款-会员权利-上海装潢网');
			$this->tpl->assign('t', 'huiyuanquanli');
			$this->tpl->display('jiedai/hezuojianjie.html');
		}
		
		function hezuofanwei(){
			$this->tpl->assign('title', '无抵押贷款-合作范围-上海装潢网');
			$this->tpl->assign('t', 'hezuofanwei');
			$this->tpl->display('jiedai/hezuojianjie.html');
		}

		function hezuogongying(){
			$this->tpl->assign('title', '合作共赢-上海装潢网');
			$this->tpl->display('jiedai/hezuo.html');
		}
		
	}


// 回溯函数
//print_r( debug_backtrace() );
//debug_print_backtrace();
//error_get_last() 获取最后一次发生的错误
?>