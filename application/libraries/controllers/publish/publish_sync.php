<?php

class publish_sync extends publish_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	// 将协会手机网站我要装修同步到快速预约通道中
	public function sida_mobile_sendzh(){
		
		$this->load->library('encode');
		$this->load->model('publish/pubModel', 'pub_model');
		
		$object = array(
			'tel'=>$this->encode->getFormEncode('tel'),
			'true_name'=>$this->encode->getFormEncode('name'),
			'addtime'=>$this->encode->getFormEncode('addtime'),
			'shen'=>$this->encode->getFormEncode('sheng'),
			'city'=>$this->encode->getFormEncode('city'),
			'town'=>$this->encode->getFormEncode('town'),
			'area'=>$this->encode->getFormEncode('area'),
			'rel'=>'{"url":"http://m.snzsxh.org.cn/biz/send", "name":"触屏版-装修招标(协会)"}',
			'ps'=>'[{"key":"房屋状态", "value":"'. $this->encode->getFormEncode('state') .'"}]'
		);
		
		$id = $this->pub_model->express_pipe_add($object);
		echo($id);
	}
	
}

?>