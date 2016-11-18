<?php
class mobile_contract extends mobile_base {
	private $data;
	public function __construct(){
		parent::__construct();
		$this->data = array(
			array('id'=>1,'image_name'=>'hetong0.jpg')
			,array('id'=>2,'image_name'=>'hetong1.jpg')
			,array('id'=>3,'image_name'=>'hetong2.jpg')
			,array('id'=>4,'image_name'=>'hetong3.jpg')
			,array('id'=>5,'image_name'=>'hetong4.jpg')
			,array('id'=>6,'image_name'=>'hetong5.jpg')
			,array('id'=>7,'image_name'=>'hetong6.jpg')
			,array('id'=>8,'image_name'=>'hetong7.jpg')
			,array('id'=>9,'image_name'=>'hetong8.jpg')
			,array('id'=>10,'image_name'=>'hetong9.jpg')
		);
	}
	
	public function index(){
		
		//if( $this->gr('v') == 2016 ){
			
			$tpl = $this->get_tpl('contract/home.html');
			$this->tpl->assign('title', '装修合同');
			$this->tpl->display( $tpl );
		
		/*
		} else {
			$this->load->library('encode');
			$id = $this->encode->get_request_encode('id');
			if( empty($id) ) $id = 1;
			$item = $this->get_item($id);
			$this->tpl->assign('item', $item);
			$this->tpl->assign('pagi', $this->get_pagination($id));
			$this->tpl->display('mobile/contract/home.html');
		}
		*/
		
	}
	
	private function get_item($id){
		foreach($this->data as $key=>$val){
			if( $val['id'] == $id ){
				return $val;
			}
		}
		return false;
	}
	
	private function get_count(){
		return count($this->data);
	}
	
	private function get_pagination($current = 1){
		$count = $this->get_count();
		$array = array();
		for($i=1; $i < $count+1; $i++){
			if( $i == $current ){
				$item = array('id'=>$i, 'current'=>true);
			} else {
				$item = array('id'=>$i, 'current'=>false);
			}
			
			$item['link'] = $this->mobile_url . 'contract?id=' . $i;
			array_push($array, $item);
		}
		return $array;
	}
	
}
?>