<?php

class album_base extends MY_Controller {
	public function __construct(){
		parent::__construct();
	}
	protected function get_tpl($tpl){
		return 'photo/2015/' . ltrim($tpl, '/');
	}
}

?>