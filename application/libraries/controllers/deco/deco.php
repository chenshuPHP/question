<?php
class deco extends MY_Controller {
	
	function __construct(){
		parent::__construct();
	}
	
	// 重定向自定义的url
	public function _remap($name, $params=array()){
		
		$args = explode('-', $name);
		
		$method = $args[0];
		
		array_shift($args);
		
		if( method_exists($this, $method) === true ){
			$this->$method($args);
		} else {
			show_404();
		}
	}
	
	// 搜索词分词
	private function segment_key( $key )
	{
		$keys = array();
		
		if( mb_strlen( $key ) <= 3 )
		{
			$keys[] = $key;
		}
		else
		{
			$this->load->library('wenzhi');	// 腾讯文智分词技术
			$keys = $this->wenzhi->segments( $key, array(
				'disallow'		=> array('上海','装饰','设计','有限','公司')	// 自动过滤的词汇
			) );
		}
		return $keys;
		
	}
	
	
	// 构建查询url
	// $params = array('page'=>1, 'shi_id'=>25)
	// $add_params = array('sheng_id'=>998,'shi_id'=>112,'page'=>2,'sort'=>'date')
	// return '/shop/se?page=1&shi_id=25&sheng_id=998&sort=date'
	private function search_build_url($params, $add_params){
		// 得到新的参数键和值
		foreach($params as $key=>$val){
			$add_params[$key] = $val;
		}
		$new_params = array();
		foreach($add_params as $key=>$val){
			if( ! empty($val) ){
				array_push($new_params, $key . '=' . $val);
			}
		}
		
		$urls = $this->config->item('url');

		return $urls['shop'] . '?' . implode('&', $new_params);
	}
	
	// 获取缓存目录
	private function get_cache_dictionary($args){
		$dir = $this->tpl->cache_dir;
		$dir = rtrim(strtolower( str_replace('/', '\\', $dir) ), '\\') . '\\';
		// $temp = ceil($id / 10000);
		$dir .= 'deco\\list\\';
	  	
		if( empty($args['cid']) ){
			$dir .= "000\\";
		} else {
			$dir .= $args['cid'] . "\\";
		}
		
		if( ! empty( $args['tid'] ) ){
			$dir .= $args['tid'] . '\\';
		}
		
		return $dir;
	}
	
	// 2015-06-10 更新
	// 支持已开通城市选择
	public function index($args = array()){
		
		$this->tpl->assign('body_class', 'w1200');
		
		$config = array(
			'tpl'			=>'company/home.html',
			'caching'		=> true
		);
		
		$config = array_merge($config, $args);
		
		$tpl = $config['tpl'];
		
		$args = array(
			'page'=>$this->encode->get_page('page'),
			'size'=>10
		);
		
		$params_config = array(
			'cid'		=> $this->gr('cid'),
			'tid'		=> $this->gr('tid'),
			'key'		=> $this->gr('key')
		);
		
		$this->tpl->caching = $config['caching'];
		
		if( $params_config['key'] != '' ){
			$this->tpl->caching = false;
		}
		
		
		// 兼容旧URL ==
		if( empty($params_config['cid']) ) $params_config['cid'] = $this->gr('sheng_id');
		if( empty($params_config['tid']) ) $params_config['tid'] = $this->gr('shi_id');
		if( empty($params_config['key']) ) $params_config['key'] = $this->gr('deco_key');
		// ============= end
		if( empty($params_config['cid']) ) $params_config['cid'] = 9969;			// 默认上海市
		
		
		$this->tpl->cache_lifetime = 10;		// 缓存 10s
		$this->tpl->cache_dir = $this->get_cache_dictionary(array(
			'cid'=>$params_config['cid'],
			'tid'=>$params_config['tid']
		));
		
		$cache_id = $args['page'];
		
		
		// 缓存开始
		if( ! $this->tpl->isCached($tpl, $cache_id) ){
		
		$this->load->library('encode');
		$this->load->library('thumb');
		$this->load->library('pagination');
		$this->load->model('city_model');
		$this->load->model('company/company_config');
		$this->load->model('company/company', 'company_model');
		$this->load->model('company/usercase');
		$this->load->model('company/userteam');
		
		$this->load->model('diary/diary_model');
		$this->load->model('article/article');
		$this->load->model('sida/ask_model');
		$this->load->model('publish/reserve', 'reserve_model');
		
		
		$city = $this->city_model->get($params_config['cid']);
		
		if( ! $city ){
			show_error('city not found', 404);
			exit();
		}
		
		$urls = $this->config->item('url');
		
		// 已经开通城市选择
		$citys = $this->city_model->gets("select id, cname, label from [city2] where isopen = 1 order by label asc");
		$city_list = $citys['list'];
		foreach($city_list as $key=>$value){
			$value['link'] = $urls['shop'] . '?cid=' . $value['id'];
			$city_list[$key] = $value;
		}
		$this->tpl->assign('city_list', $city_list);
		
		// 检测当前省份是否属于开通城市
		$is_open_cid = false;
		foreach($city_list as $item){
			if( $item['id'] == $params_config['cid'] ){
				$is_open_cid = true;
			}
		}
		if( $is_open_cid != true ){
			show_error('city not open', 404);
			exit();
		}
		
		if( ! empty( $params_config['tid'] ) ){
			$town = $this->city_model->get($params_config['tid']);
			
			if( ! $town || $town['pid'] != $params_config['cid'] ){
				show_error('town error', 404);
				exit();
			}
				
		} else {
			$town = false;
		}
		
		//转码 字符串
		if( ! empty($params_config['key']) ){
			$params_config['key'] = iconv('gbk', 'utf-8', $params_config['key']);
		}
		
		$city = $this->city_model->child_assign($city);						// 加载市的区
		
		foreach($city['child'] as $key=>$val){
			$city['child'][$key]['link'] = $this->search_build_url(array('tid'=>$val['id'], 'page'=>''), $params_config);	// 为区附加链接
		}
		// 附加一个全部
		$city['child'][count( $city['child'] )] = array(
			'id'=>'',
			'cname'=>'全部',
			'short_cname'=>'全部',
			'link'=>$this->search_build_url(array('tid'=>'', 'page'=>''), $params_config)
		);	
		
		$this->tpl->assign('city', $city);
		
		$where = "delcode=0 and company <> '' and hangye='装潢公司'";
		if( ! empty( $params_config['cid'] ) ){
			$where .= " and user_city = '". $params_config['cid'] ."'";
		}
		
		if( ! empty( $params_config['tid'] ) ){
			$where .= " and user_town = '". $params_config['tid'] ."'";
		}
		
		if( ! empty($params_config['key']) ){
			$keys = $this->segment_key( $params_config['key'] );	// 分词得到数组
			
			$_arr = array();
			foreach($keys as $item)
			{
				if( strpos($item, ' ') != false ) $item = '"'. $item .'"';
				$_arr[] = $item;
			}
			if( count($_arr) > 0 )
			{
				$where .= " and ( Contains(company, '". implode(' and ', $_arr) ."') OR rejion = '". $params_config['key'] ."' )";
			}
			else
			{
				$where .= " and ( Contains(company, '". $params_config['key'] ."') OR rejion = '". $params_config['key'] ."' )";
			}
			
			
		}
		
		// 读取装修公司
		$sql = "select * from (select id, username, company, logo, user_shen, user_city, user_town, company_date, address, koubei, ".
		//"koubei_total, flag, zhibaojin, usercode, row_number() over(order by flag desc, koubei_total desc) as num ".
		"koubei_total, flag, zhibaojin, usercode, row_number() over(order by koubei_total desc) as num ".
		"from company where ". $where .") as tmp ".
		"where num between ". (($args['page'] - 1) * $args['size'] + 1) ." and " . ($args['size'] * $args['page']) ;
		
		$count_sql = "select count(*) as icount from company where " . $where;
		$result = $this->company_model->get_list($sql, $count_sql, true);
		$list = $result['list'];
		
		if( count($list) == 0 ){
			$this->tpl->caching = false;
		}
		
		$list = $this->company_config->zhibaojin_assign($list);
		
		$count = $result['count'];
		unset($result);
		
		
		$this->pagination->currentPage = $args['page'];
		$this->pagination->recordCount = $count;
		$this->pagination->pageSize = $args['size'];
		$this->pagination->url_template = $this->search_build_url(array('page'=>'<{page}>'), $params_config);
		$this->pagination->url_template_first = $this->search_build_url(array('page'=>''), $params_config);
		$pagination = $this->pagination->toString(true);
		
		if( $args['page'] != 1 && $args['page'] > $this->pagination->get_page_count() ) {
			show_error('param page error', 404);
			exit();
		}
		
		// logo 缩略图
		$this->thumb->setPathType(1);
		foreach($list as $key=>$value){
			if( !empty($value['logo']) ){
				//$list[$key]['logo_thumb'] = $this->thumb->resize($value['logo'], 49, 35);
				$list[$key]['logo_thumb2'] = $this->thumb->resize($value['logo'], 280, 200);
				//$list[$key]['logo_thumb2'] = $value['logo'];
			} else {
				$list[$key]['logo_thumb'] = '';
			}
		}
		$this->thumb->setPathType(0);
		
		
		// 装修公司案例信息
		$list = $this->usercase->case_count_assign_users($list);
		
		// 为每个装修公司绑定案例首图 即将取消
		$list = $this->usercase->case_image_assign_users($list);
		foreach($list as $key=>$val){
			if( ! empty($val['case_image']) ){
				$list[$key]['case_thumb'] = $this->thumb->crop($val['case_image'], 175, 125);
			} else {
				$list[$key]['case_thumb'] = '';
			}
		}
		
		// 装修公司员工数量
		$list = $this->userteam->count_assign($list);
		
		// 装修公司被预约数量
		$this->reserve_model->count_assign($list);
		
		/* 排序相关
		$sort_links = array(
			array('name'=>'按默认排序', 'label'=>'id')
			,array('name'=>'按口碑排序', 'label'=>'koubei')
			,array('name'=>'按活跃度排序', 'label'=>'lastlogin')
		);
		foreach($sort_links as $key=>$value){
			$sort_links[$key]['link'] = $this->search_build_url(array('sort'=>$value['label'], 'page'=>1), $params_config);
		}
		$this->tpl->assign('sorts', $sort_links);
		 */
		
		
		$this->tpl->assign('list', $list);
		
		$this->tpl->assign('pagination', $pagination);
		
		$this->tpl->assign('params_config', $params_config);
		
		$this->tpl->assign('town', $town);
		
		//$title = $city['cname'] . $town['short_cname'] . '装修公司, ' . $city['cname'] . $town['short_cname'] . '装饰公司, ' . $city['cname'] . $town['short_cname'] . '装潢公司,上海装修网';
		if( $town['short_cname'] == '' ){
			$title = $city['cname'] . '装修公司,'. $city['cname'] .'装修公司排名,为您精选3000家'. $city['cname'] .'装修公司排行榜-上海装潢网';
			$keywords = $city['cname'] . '装修公司,'. $city['cname'] .'装修公司排名,' . $city['cname'] . '装潢网修公司大全';
			$description = '上海装修公司排行榜是由上海室内装饰协会联合上海装潢网根据装修公司实力和口碑精选得出，目前汇集了各大上海装修公司排名大全,认证公司免费提供装修设计方案与报价；快速免费获取设计报价请拨打全国免费热线：400-728-5580';
		} else {
			$title = $city['cname'] . $town['short_cname'] .'装修公司大全,'. $city['cname'] . $town['short_cname'] .'装修公司排名,上海装潢网';
			$keywords = $city['cname'] . $town['short_cname'] . '装修公司,'. $city['cname'] . $town['short_cname'] .'装修公司排名,'. $city['cname'] . $town['short_cname'] .'装修公司大全';
			$description = '上海装潢网网汇集了最好的'. $city['cname'] . $town['short_cname'] .'装修公司,免费提供装饰装修设计，以及最透明的装修报价清单。免费拨打电话将直接转接到装修公司，无额外收费！全国免费热线：400-728-5580';
		}
		
		$this->tpl->assign('title', $title);
		$this->tpl->assign('keywords', $keywords);
		$this->tpl->assign('description', $description);
		
		// 装修公司总量
		$regist_company_count = $this->company_model->get_regist_count();
		$this->tpl->assign('regist_company_count', $regist_company_count);
		
		
		// 侧边栏
		// 家装排行榜
		$home_top10 = $this->company_model->get_list("select top 6 id, username, company, shortname, koubei, koubei_total ".
		"from company where flag = 2 and hangye = '装潢公司' order by koubei_total desc", '', true);
		$this->tpl->assign('home_topics', $home_top10['list']);
		
		
		
		// 监理日记
		$diary_images = $this->diary_model->get_list("select top 4 id, title, address, detail from diary order by id desc");
		$diary_images = $diary_images['list'];
		foreach($diary_images as $key=>$val){
			$content = $this->encode->htmldecode($val['detail']);
			$diary_images[$key]['images'] = $this->diary_model->get_thumb_image( $content, array('count'=>2) );
			unset($diary_images[$key]['detail']);
		}
		$this->tpl->assign('diary_images', $diary_images);
		
		// var_dump2( $diary_images );
		// $diarys = $this->diary_model->get_list("select top 4 id, title, address from diary where id not in (". $diary_images[0]['id'] .",". $diary_images[1]['id'] .") order by id desc");
		// $this->tpl->assign('diarys', $diarys['list']);
		
		
		// 装修知识
		$image_arts = $this->article->getAttrNews(0, 2, array(), 'latest', 'id, title, imgpath, clsid, description');
		
		foreach($image_arts as $key=>$val){
			$image_arts[$key]['thumb'] = $this->thumb->crop($val['imgpath'], 94, 73);
		}
		$this->tpl->assign('image_arts', $image_arts);
		
		// 文章列表
		$arts = $this->article->getAttrNews(0, 6, array($image_arts[0]['id'], $image_arts[1]['id']), 'latest', 'id, title, clsid');
		$this->tpl->assign('arts', $arts);
		
		// 协会问吧
		$ask_latest = $this->ask_model->get_isans_latest(1);
		
		$ans = $ask_latest->ans[0];
		$ask_latest->ans = $this->encode->removeHtml($ans->detail);
		
		$asks = $this->ask_model->get_asks(
			array(
				'top'=>8,
				'noids'=>implode(', ', array($ask_latest->id)),
				'sort'=>'show_count'
			)
		);
		
		$this->tpl->assign('ask_latest', $ask_latest);
		$this->tpl->assign('asks', $asks->list);
		
		if( count( $_REQUEST ) == 0 ){
			// 友情链接
			$this->load->model('firlink');
			$links = $this->firlink->get_cmp_links();
			$this->tpl->assign('links', $links);
		}
		
		$this->tpl->assign('sp_url', $urls['shop']);
		
		$this->tpl->assign('kf', $this->config->item('kf'));
		
			$this->tpl->display($tpl, $cache_id);
			
		} else {
			$this->tpl->display($tpl, $cache_id);
		}
		
	}
	
	// 装修公司预约
	public function appoint(){
		$urls = $this->config->item('url');
		$u = $this->gr('u');
		$this->tpl->assign('username', $u);
		$this->tpl->assign('urls', $urls);
		$this->tpl->display('company/appoint.html');
	}
	
	public function appoint_validate(){
		session_start();
		$this->load->library('kaocode');
		$this->kaocode->doimg();
		$_SESSION['appoint_validate'] = $this->kaocode->getCode();
	}
	
	public function appoint_handler(){
		$info = $this->get_form_data();
		
		$ajax = $info['ajax'];
		
		unset($info['ajax']);
		
		if( empty($info['true_name']) || empty($info['tel']) ){
			$this->alert('请输入称呼和电话');
			exit();
		}
		
		if( !$this->_check_appoint_validate($info['validate']) ){
			$this->alert('验证码错误或已失效，请重新提交');
			exit();
		}
		
		$this->load->model('publish/reserve');
		
		try{
			
			$this->_clear_appoint_validate();	// 清除验证码防止重复提交
			
			$this->reserve->add($info);
			
			if( $ajax == 1 )
			{
				echo(json_encode( array('type'=>'success') ));
			}
			else
			{
				$this->tpl->display('company/appoint_handler_success.html');
			}
			
		}catch(Exception $e){
			exit($e->getMessage());
		}
		
	}
	
	public function appoint_validate_check(){
		if( $this->_check_appoint_validate($this->gf('validate')) == false ){
			echo(0);
		} else {
			echo(1);
		}
	}
	
	private function _check_appoint_validate($code){
		session_start();
		if( empty( $_SESSION['appoint_validate'] ) || empty($code) ) return false;
		return strtolower($code) == strtolower($_SESSION['appoint_validate']);
	}
	
	private function _clear_appoint_validate(){
		if( ! isset( $_SESSION ) )session_start();
		$_SESSION['appoint_validate'] = '';
		unset($_SESSION['appoint_validate']);
	}
	
}

?>