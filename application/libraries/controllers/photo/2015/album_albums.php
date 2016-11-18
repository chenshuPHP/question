<?php

class album_albums extends album_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home(){
		$this->encode->moved_permanently('/imglist?id=' . $this->gr('id'));
	}
	
}



?>