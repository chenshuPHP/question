<?php

class admin_video_video extends admin_base {
	
	public function __construct(){
		parent::__construct();
		$this->load->model('shipin/shipinmodel', 'video_model');
	}
	
	public function manage(){
		
		$args = array(
			'page'=>$this->encode->get_page(),
			'size'=>20
		);
		
		$sql = "select * from ( select id, title, addtime, showcount, localoption, num = row_number() over( order by addtime desc ) from video where recycle = 0 ) as temp where num between ". (($args['page'] - 1) * $args['size'] + 1) ." and " . ( $args['size'] * $args['page'] );
		$sql_count = "select count(*) as icount from video";
		$result = $this->video_model->gets($sql, $sql_count);
		
		
		$this->load->library('pagination');
		
		$this->pagination->pageSize = $args['size'];
		$this->pagination->recordCount = $result['count'];
		$this->pagination->currentPage = $args['page'];
		$this->pagination->url_template = $this->get_complete_url('video/video/manage?page=<{page}>');
		$this->pagination->url_template_first = $this->get_complete_url('video/video/manage');
		$pagination = $this->pagination->toString(true);
		
		$this->tpl->assign('pagination', $pagination);
		
		$this->tpl->assign('list', $result['list']);
		$this->tpl->assign('args', $args);
		
		$this->tpl->assign('module', 'video.manage');
		
		$this->tpl->display( $this->get_tpl('video/video_manage.html') );
		
	}
	
	public function active(){
		
		$id = $this->gr('id');
		$rurl = $this->gr('r');
		
		$video = $this->video_model->get($id, array(
			'fields'=>'id, title, shorttitle, description, keyword, videourl, content, company, companyuser, designer, designeruser, thumbimg, localoption, showcount'
		));
		
		$this->tpl->assign('video', $video);
		$this->tpl->assign('rurl', $rurl);
		
		if( ! empty($id) ){
			$this->tpl->assign('module', 'video.edit');
		} else {
			$this->tpl->assign('module', 'video.add');
		}
		
		$this->tpl->display( $this->get_tpl('video/video_active.html') );
	}
	
	public function handler(){
	
		$info = $this->get_form_data();
		
		$rurl = $info['rurl'];
		$rurl = empty( $rurl ) ? $this->get_complete_url('video/video/manage') : $rurl;
		
		try{
			if( $info['id'] != '' ){
				$this->video_model->edit($info);
			} else {
				$this->video_model->add($info);
			}
			$this->alert('提交成功', $rurl);
		}catch(Exception $e){
			$this->alert($e->getMessage());
		}
	}
	
	// 删除视频处理
	public function delete(){
		
		$id = $this->gr('id');
		$rurl = $this->gr('r');
		
		try{
			$this->video_model->delete($id);
			$this->alert('', $rurl);
		}catch(Exception $e){
			$this->alert( $e->getMessage() );
		}
		
		
	}
	
}

?>