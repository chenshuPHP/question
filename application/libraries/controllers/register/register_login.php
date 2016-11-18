<?php

// 用户登录
// 2015-03-16 可用 手机号码 登录
// 兼容旧版登录 可用 用户名 登录

class register_login extends register_base {

	public function __construct(){
		parent::__construct();
		$this->tpl->assign('hide_kf', '1');
		$this->tpl->assign('hide_bdshare', '1');
	}

	public function home(){
		$this->tpl->display('reg/register_login.html');
	}

}

?>