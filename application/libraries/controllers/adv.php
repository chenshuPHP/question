<?php if( !defined('BASEPATH') ) exit('prohibition of direct view /adv.controller.php');
class adv extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function get_infomation(){
		$this->load->library('encode');
		$this->load->model('advertisement/advertisement', 'adv_model');
		$id = $this->encode->getFormEncode('id');
		$sets = array('id'=>$id, 'fields'=>'id,name,price,start_date,end_date');
		$object = $this->adv_model->get_object($sets);
		echo( json_encode($object) );
	}
	
	public function main(){
		
		$this->tpl->display('temp_adv.html');
	}
	
	
}
?>