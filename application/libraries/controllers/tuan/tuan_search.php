<?php
if( ! defined('BASEPATH') ) exit('禁止直接浏览');

class tuan_search extends tuan_base {

	public function __construct(){
		parent::__construct();
	}

	// 商品检索
	public function home(){
		
		$filter = array(
			'key'=>$this->gr('key')
		);

		$filter['key'] = iconv('gbk', 'utf-8', $filter['key']);

		$args = array(
			'size'=>20,
			'page'=>$this->gr('page')
		);

		echo( $filter['key'] );
		
	}

}

?>