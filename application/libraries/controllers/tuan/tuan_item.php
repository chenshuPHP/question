<?php

class tuan_item extends tuan_base {
	
	//public function __construct(){
	//	parent::__construct();
	//}
	
	public function home(){
		include('tuan_shop.php');
		$object = new tuan_shop();
		$object->item($this->gr('id'));
	}
	
	
	
}

?>