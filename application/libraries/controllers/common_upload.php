<?php if(!defined('BASEPATH')) exit('~/controllers/common_upload.php');

// 通用图片上传控制器类
class common_upload extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->model('upload_model');
	}
	
	public function index(){}
	
	// 装修公司案例图片上传
	public function usercase_upload(){
		$this->load->library('encode');
		
		// 2016-10-20 启用临时目录
		//$folder = 'usercase';
		$folder = 'temp\\usercase';
		
		$version = $this->gf('version');
		
		if( $version == '2.0' ){
			$folder = 'temp\\usercase';
		}
		
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		
		$upload_settings = array(
			'folder'=>$folder,
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>2048,
			'watermark'=>true,		// 添加水印
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 装修公司logo上传
	public function deco_logo_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'logo',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'is_logo_image'=>true,		// * 是否为Logo目录 *
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 装修公司企业形象图片上传
	public function deco_company_pic_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'companypic',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 装修公司 设计团队人头像
	public function deco_employee_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'temp\\sjs_list',	// 临时目录
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 投诉信息 编辑器上传
	public function tousu_editor_upload(){
		$this->load->library('encode');
		$image_name = $this->encode->getFormEncode('pictitle');
		$upload_settings = array(
			'folder'=>'tousu_edit',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024
		);
		$result = $this->upload_model->do_upload($upload_settings);
		//echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
		//echo('{"url":"http://www.jiayumuye.com/resources/images/about_us.jpg", "original":"original content", "title":"image title!", "state":"SUCCESS"}');
		echo('{"url":"'. $result['url'] .'", "original":"original content", "title":"'. $image_name .'", "state":"SUCCESS"}');
	}
	
	// 移动端投诉
	private function _tousu_upload()
	{
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'			=> 'temp\\complaint',	// 临时目录
			'field'				=> 'upfile',
			'allowed_types'		=> array('jpg', 'png', 'gif', 'jpeg'),
			'max_size'			=> 1024 * 2,
			'rotate'			=> TRUE,							// 是否自动调整图片方向 ( 拍照上传需要 )
			'remove_image_path'	=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	public function m_tousu_upload()
	{
		// 使 XHR POST 跨域请求支持
		$urls = config_item('url');
		$mobile_url = rtrim($urls['mobile'], '/');
		header("Access-Control-Allow-Origin:" . $mobile_url);
		$this->_tousu_upload();
	}
	
	
	// 后台监理日记编辑器上传
	public function diary_editor_upload(){
		$this->load->library('encode');
		$image_name = $this->encode->getFormEncode('pictitle');
		$upload_settings = array(
			'folder'=>'diary_edit',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024
		);
		$result = $this->upload_model->do_upload($upload_settings);
		//echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
		//echo('{"url":"http://www.jiayumuye.com/resources/images/about_us.jpg", "original":"original content", "title":"image title!", "state":"SUCCESS"}');
		echo('{"url":"'. $result['url'] .'", "original":"original content", "title":"'. $image_name .'", "state":"SUCCESS"}');
	}
	
	// 装修工人修改资料上传
	public function worker_face_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'worker_face',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>2048,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 预算缩略图
	public function budget_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'budget',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 预算附件
	public function budget_attach_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'budget_attach',
			'field'=>'upfile',
			'allowed_types'=>array('xls', 'rar', 'xlsx'),
			'max_size'=>1024*5,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 资讯正文编辑器上传ueditor
	public function article_editor_upload(){
		$this->load->library('encode');
		$image_name = $this->encode->getFormEncode('pictitle');
		$upload_settings = array(
			'folder'=>'art_edit',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'watermark'=>true
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{"url":"'. $result['url'] .'", "original":"original content", "title":"'. $image_name .'", "state":"SUCCESS"}');
	}
	
	// 施工团队LOGO
	public function team_logo_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'team_logo',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	
	// 施工团队成员照片上传
	public function team_face_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'team_face',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 施工团队二维码
	public function team_qrcode_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'team_qrcode',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 施工档案图集图片上传
	public function archive_album_image_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'archive_album',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 施工团队工地编辑器
	public function archive_cas(){
		$this->load->library('encode');
		$image_name = $this->encode->getFormEncode('pictitle');
		$upload_settings = array(
			'folder'=>'archive_cas',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{"url":"'. $result['url'] .'", "original":"original content", "title":"'. $image_name .'", "state":"SUCCESS"}');
	}

	// 施工团队 工地，材料清单，材料照片上传
	public function archive_prod_image_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'temp\\archive_prod',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 装修公司后台, 装修公司新闻发布编辑器
	public function company_news_editor_upload(){
		$this->load->library('encode');
		$image_name = $this->encode->getFormEncode('pictitle');
		$upload_settings = array(
			'folder'		=> 'company_news_editor',
			'field'			=> 'upfile',
			'allowed_types'	=> array('jpg', 'png', 'gif'),
			'max_size'		=> 1024,
			'watermark'		=> TRUE
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{"url":"'. $result['url'] .'", "original":"original content", "title":"'. $image_name .'", "state":"SUCCESS"}');
	}
	
	// 设计师头像
	public function sjs_face_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'sjs_face_image\\temp',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 设计师案例上传
	public function sjs_case_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'sjs_case\\temp',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 设计师频道，招标 图片上传
	public function sjs_zhaobiao_upload(){
		
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'temp\\sjs_zhaobiao',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
		
	}
	
	
	// 建材商城 产品缩略图
	public function product_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'product_image\\temp',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 产品说明 编辑器
	public function product_editor_upload(){
		$this->load->library('encode');
		$image_name = $this->encode->getFormEncode('pictitle');
		$upload_settings = array(
			'folder'=>'product_editor',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{"url":"'. $result['url'] .'", "original":"original content", "title":"'. $image_name .'", "state":"SUCCESS"}');
	}
	
	// 建材商城 装修团购专题页 相关图片 上传
	public function mall_zxtg_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'mall_zxtg\\temp',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}

	// 建材商城 材料品牌LOGO上传
	public function mall_brand_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'temp\\mall_brand',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}

	// 视频缩略图
	public function video_thumb_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'video_thumb',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024,
			'remove_image_path'=>$remove_image_path
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 图库相册中图片
	public function photo_album_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'temp\\photo_album',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024
			,'remove_image_path'=>$remove_image_path
			,'watermark'=>true
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 业主中心LOGO
	public function cust_avatar_upload(){
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'temp\\cust_avatat',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024
			,'remove_image_path'=>$remove_image_path
			,'watermark'=>false
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}

	// 移动端 图片上传 
	private function mobile_upload_tmp()
	{
		
		// 使 XHR POST 跨域请求支持
		$urls = config_item('url');
		$mobile_url = rtrim($urls['mobile'], '/');
		header("Access-Control-Allow-Origin:" . $mobile_url);
		
		// 上传处理
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'			=> 'temp\\mobile_upload_tmp',
			'field'				=> 'file',
			'allowed_types'		=> array('jpg', 'png', 'gif'),
			'max_size'			=> 1024 * 3,					// 最大 允许 3mb 文件
			'remove_image_path'	=> $remove_image_path,
			'watermark'			=> false,
			'rotate'			=> TRUE							// 是否自动调整图片方向 ( 拍照上传需要 )
		);
		
		$result = $this->upload_model->do_upload($upload_settings);
		
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 会员 优惠活动海报
	public function promotion_upload()
	{
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'temp\\youhuiquan',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024 * 2
			,'remove_image_path'=>$remove_image_path
			,'watermark'=>false
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	// 优惠活动 编辑器
	public function promotion_editor_upload()
	{
		$this->load->library('encode');
		$image_name = $this->encode->getFormEncode('pictitle');
		$upload_settings = array(
			'folder'=>'promotion_editor',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>2000
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{"url":"'. $result['url'] .'", "original":"original content", "title":"'. $image_name .'", "state":"SUCCESS"}');
	}
	
	// 企业证书
	public function certificate_upload()
	{
		$this->load->library('encode');
		$remove_image_path = $this->encode->getFormEncode('remove_image_path');
		$upload_settings = array(
			'folder'=>'temp\\cert',
			'field'=>'upfile',
			'allowed_types'=>array('jpg', 'png', 'gif'),
			'max_size'=>1024 * 2
			,'remove_image_path'=>$remove_image_path
			,'watermark'=>false
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{local:"'. str_replace('\\', '\\\\', $result['path']) .'", url:"'. $result['url'] .'", path:"'. $result['local'] .'"}');
	}
	
	
	// 后台 会员公告 编辑器
	public function member_letter_editor_upload()
	{
		$this->load->library('encode');
		$image_name = $this->encode->getFormEncode('pictitle');
		$upload_settings = array(
			'folder'			=> 'member_letter_editor',
			'field'				=> 'upfile',
			'allowed_types'		=> array('jpg', 'png', 'gif'),
			'max_size'			=> 2000
		);
		$result = $this->upload_model->do_upload($upload_settings);
		echo('{"url":"'. $result['url'] .'", "original":"original content", "title":"'. $image_name .'", "state":"SUCCESS"}');
	}
	
}
















?>