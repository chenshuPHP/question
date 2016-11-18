<?php
// misc = Miscellaneous = 杂, 繁杂, 冗杂, 什, 遝, 杂项
// 一些奇怪的，杂乱无章的策划要求 都来这里处理
// 因为不知道该属于那个频道，所以统一放这里
class misc extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 申请下载效果图
	// 戴一策划，起初在我要装修页面中有出现
	// 2013-12-12
	public function download_album(){
		$this->tpl->display('active/misc/download_album.html');
	}
	
	
}
?>