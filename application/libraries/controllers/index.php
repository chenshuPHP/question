<?php

class index extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function home($tpl = 'index.html'){
		
		
		if( $tpl == 'index.html' ){
			$this->tpl->caching = true;
			$this->tpl->cache_lifetime = 60*60*1;	// 1小时 更新一次 
		}
		
		$cache_dir = $this->tpl->cache_dir . 'site_index/';	// 设置这个模版的缓存目录
		$this->tpl->cache_dir = $cache_dir;
		
		if(! $this->tpl->isCached($tpl) ){
			
		
		$this->load->library('thumb');
		
		// ^^^^^^^
		// 图库
		$this->load->model('photo/album_category_model', 'photo_cat_model');
		$this->load->model('photo/album_image_model');
		
		/*
		$this->load->model('photo/Photo', 'photo_model');
		$photo_cats = $this->photo_cat_model->get_cats(array(19, 39, 61, 1));
		$photo_cats = $this->photo_cat_model->childs_assign($photo_cats);
		
		$album_sizes = array(
			array(403, 326, 'one'),
			array(300, 153, 'two'),
			array(300, 153, 'three'),
			array(188, 326, 'four')
		);
		
		$albums = $this->photo_model->get_albums("select top 4 id, name, fm_image, image_count from photo_album where fm_image <> '' order by rank desc");
		$i = 0;
		foreach($albums as $key=>$val){
			$albums[$key]['thumb'] = $this->thumb->crop($val['fm_image'], $album_sizes[$i][0], $album_sizes[$i][1]);
			$albums[$key]['class_name'] = $album_sizes[$i][2];
			$i++;
		}
		*/
		// ^^^^^^^
		
		// 图库
		// $this->load->model('photo/album_category_model');
		$tuku_hot_categories = $this->photo_cat_model->get_list(
			"select top 12 CAT.id, CAT.name, CAT.name2, RES.icount
			from photo_category as CAT
			LEFT JOIN (select COUNT(*) AS icount, cid from photo_album_image group by cid) as RES
			ON CAT.id = RES.cid
			where CAT.disabled = 0
			order by RES.icount desc"
		);
		
		$tuku_recommend_image_settings = array(
			array('w'=>400, 'h'=>400, 'cls'=>'enter'),
			array('w'=>400, 'h'=>400, 'cls'=>'enter'),
			array('w'=>400, 'h'=>400, 'cls'=>'enter'),
			array('w'=>520, 'h'=>195, 'cls'=>'index_1'),
			array('w'=>255, 'h'=>195, 'cls'=>'index_2'),
			array('w'=>255, 'h'=>195, 'cls'=>'index_3'),
			array('w'=>260, 'h'=>400, 'cls'=>'index_4')
		);
		
		$tuku_recommend_images = $this->album_image_model->get_list("select top 7 id, name, imagePath as path, description from [photo_album_image] where addtime <= '". date('Y-m-d') ."' and recommend = 1 order by addtime desc");
		$i = 0;
		$tuku_recommend_images = $tuku_recommend_images['list'];
		foreach($tuku_recommend_images as $key=>$image)
		{
			
			$image['thumb'] = $this->thumb->crop($image['path'], $tuku_recommend_image_settings[$i]['w'], $tuku_recommend_image_settings[$i]['h']);
			$image['config'] = $tuku_recommend_image_settings[$i];
			$tuku_recommend_images[$key] = $image;
			$i ++;
		}
		
		$this->tpl->assign('tuku_hot_categories', $tuku_hot_categories['list']);
		$this->tpl->assign('tuku_recommend_images', $tuku_recommend_images);
		
		
		
		
		// ********** 装修学堂 *********
		// ==  监理日记 ==
		$this->load->model('diary/diary_model');
		$this->load->model('article/Article', 'article_model');
		$this->load->model('archive/archive_cas_model');
		$this->load->model('archive/archive_album_model');
		
		//$diaries = $this->diary_model->get_list("select top 3 id, title from diary where image_count > 0 order by id desc");
		//$diaries = $diaries['list'];
		
		// == 装修百科 ==
		//$arts = $this->article_model->getCustomNews("select top 3 id, title, clsid from art_art order by id desc", true);
		
		// 推荐新闻 15 天内浏览量最高的文章
		//$rmds = $this->article_model->getCustomNews("select top 5 id, title, clsid, imgpath as path, description, addtime from art_art where datediff(d, addtime, getdate()) < 15 order by showcount desc", true);
		
		//$this->tpl->assign('rmds', $rmds);
		

		//^^^^^^^^ 新模块 资讯 
		$recmds = $this->article_model->getCustomNews("select top 4 id, title, clsid, imgpath from art_art where recmd = 1 order by addtime desc", TRUE);	// 后台设置推荐的资讯最新的
		
		foreach($recmds as $key=>$value)
		{
			$recmds[$key]['thumb'] = $this->thumb->crop($value['imgpath'],  240, 168);
		}
		
		$cats = array(
			array(
				'name'=>'空间设计',
				'id'=>6,
				'label'=>'kongjiansheji',
				'childs'=>array(
					array('name'=>'风格', 'link'=>'http://www.shzh.net/article/list-7.html'),
					array('name'=>'户型', 'link'=>'http://www.shzh.net/article/list-20.html'),
					array('name'=>'客厅', 'link'=>'http://www.shzh.net/article/list-22.html'),
					array('name'=>'玄关', 'link'=>'http://www.shzh.net/article/list-111.html'),
					array('name'=>'厨房', 'link'=>'http://www.shzh.net/article/list-25.html')
				),
				'arts'=>NULL
			),
			array(
				'name'=>'施工验收',
				'id'=>1,
				'label'=>'shigongyanshou',
				'childs'=>array(
					array('name'=>'进场', 'link'=>'http://www.shzh.net/article/list-4.html'),
					array('name'=>'隐蔽', 'link'=>'http://www.shzh.net/article/list-5.html', 'alias'=>'隐蔽工程'),
					array('name'=>'泥瓦', 'link'=>'http://www.shzh.net/article/list-14.html'),
					array('name'=>'油漆', 'link'=>'http://www.shzh.net/article/list-15.html'),
					array('name'=>'验收', 'link'=>'http://www.shzh.net/article/list-18.html')
				),
				'arts'=>NULL
			),
			array(
				'name'=>'装修风水',
				'id'=>55,
				'label'=>'zhuangxiufengshui',
				'childs'=>array(
					array('name'=>'书房风水', 'link'=>'http://www.shzh.net/article/list-88.html'),
					array('name'=>'玄关风水', 'link'=>'http://www.shzh.net/article/list-83.html'),
					array('name'=>'办公室风水', 'link'=>'http://www.shzh.net/article/list-197.html')
				),
				'arts'=>NULL
			),
			array(
				'name'=>'选材常识',
				'id'=>97,
				'label'=>'xuancaichangshi',
				'childs'=>array(
					array('name'=>'地板', 'link'=>'http://www.shzh.net/article/list-98.html'),
					array('name'=>'橱柜', 'link'=>'http://www.shzh.net/article/list-99.html'),
					array('name'=>'电器', 'link'=>'http://www.shzh.net/article/list-194.html'),
					array('name'=>'DIY', 'link'=>'http://www.shzh.net/article/list-267.html')
				),
				'arts'=>NULL
			)
		);
		
		foreach($cats as $key=>$value)
		{
			$cats[$key]['arts'] = $this->article_model->getCustomNews("select top 4 id, title, clsid from art_art where cid like '%,". $value['id'] .",%' order by addtime desc", TRUE);
		}
		
		$diaries2 = $this->diary_model->get_list("select top 6 id, title from diary where image_count > 0 order by id desc");
		$diaries2 = $diaries2['list'];
		
		// == 体验馆 ==
		$samples = $this->archive_cas_model->get_list("select top 3 id, tid, name, fm, huxing from archive_cas where sample = 1 and fm <> '' order by name Asc");
		$samples = $samples['list'];
		$samples = $this->archive_cas_model->format_batch_sample($samples);
		
		foreach($samples as $key=>$value)
		{
			$samples[$key]['thumb'] = $this->thumb->crop($value['fm'], 60, 60);
		}
		
		// var_dump2($samples);
		
		$this->tpl->assign('recmds', $recmds);
		$this->tpl->assign('cats', $cats);
		$this->tpl->assign('diaries2', $diaries2);
		$this->tpl->assign('samples', $samples);
		// ^^^^^^^^^^^^^^^^^^
		







		
		// ***** 装修公司 *****
		$cmps = array(
			array('username'=>'dongshunzhuang', 'name'=>'东顺设计', 'path'=>'ds.jpg', 'path2'=>'ds-01.png', 'current'=>true),
			array('username'=>'sdzs', 'name'=>'苏东装饰', 'path'=>'ssd.jpg', 'path2'=>'ssd-01.jpg', 'current'=>false),
			array('username'=>'20140901', 'name'=>'华唐装饰', 'path'=>'ht.jpg', 'path2'=>'ht-01.png', 'current'=>false),
			array('username'=>'jingshenzh', 'name'=>'精胜装饰', 'path'=>'js.jpg', 'path2'=>'js-01.jpg', 'current'=>false),
			array('username'=>'shuxinzhuangs', 'name'=>'舒心装饰', 'path'=>'shuxin.jpg', 'path2'=>'sx-01.jpg', 'current'=>false),
			array('username'=>'huiqin', 'name'=>'美力达', 'path'=>'mld1.jpg', 'path2'=>'mld-01.png', 'current'=>false),
			array('username'=>'manyouzhuangshi929', 'name'=>'满有装饰', 'path'=>'mmy.jpg', 'path2'=>'mmy-01.jpg', 'current'=>false),
			array('username'=>'new_lanxin', 'name'=>'蓝信设计', 'path'=>'lx.jpg', 'path2'=>'lx-01.png', 'current'=>false),
			array('username'=>'lanyue', 'name'=>'蓝月建筑', 'path'=>'ly1.jpg', 'path2'=>'ly-01.png', 'current'=>false),
			array('username'=>'xiangque', 'name'=>'祥雀建筑', 'path'=>'xq.jpg', 'path2'=>'xq-01.png', 'current'=>false),
			array('username'=>'heshengzh', 'name'=>'和圣装潢', 'path'=>'hesheng.jpg', 'path2'=>'hs.jpg', 'current'=>false),
			array('username'=>'zhihui', 'name'=>'知惠装饰', 'path'=>'zhihui_new.jpg', 'path2'=>'zhihui_01.jpg', 'current'=>false),
		);
		
		
		// 企业设计师
		$this->load->model('company/userteam', 'employee_model');
		$this->load->model('company/company', 'deco_model');
		$this->load->model('company/usercase', 'project_model');
		
		$cmps = $this->employee_model->count_assign($cmps);
		$cmps = $this->project_model->case_count_assign_users($cmps);
		
		$users = array();
		foreach($cmps as $item){
			$users[] = $item['username'];
		}
		$decos = $this->deco_model->get_list("select id, username, company, shortname, zhibaojin, usercode, user_shen, user_city, user_town, address, content, koubei, koubei_total, logo from company where username in ('". implode("','", $users) ."')", '', true);
		$decos = $decos['list'];
		
		// 为公司附加项目
		$decos = $this->project_model->project_assign_decos($decos);
		
		foreach($decos as $key=>$val){
			$desc = $this->encode->get_text_description($val['content'], 120);
			$decos[$key]['desc'] = $desc;
			unset($decos[$key]['content']);
		}
		
		// var_dump2($decos);
		
		foreach($cmps as $key=>$val){
			$val['deco'] = NULL;
			foreach($decos as $item){
				if( $val['username'] == $item['username'] ){
					$val['deco'] = $item;
					break;
				}
			}
			$cmps[$key] = $val;
		}
		
		
		
		$this->load->model('company/employee_category_model');
		$sql = "select top 3 * from ( select id, username, true_name as name, face_image as face, job_id, rs.icount, num = row_number() over(partition by username order by rs.icount desc) from [user_team_member] as u left join ( select count(*) as icount, eid from [user_case_employee] group by eid ) as rs on rs.eid = u.id where face_image <> '' and username in (select username from company where delcode = 0) ) as temp where num <= 1 order by icount desc";
		$employee = $this->employee_model->gets($sql);
		$employee = $this->deco_model->fill_collection($employee['list'], array(
			'fields'=>array('username', 'company', 'shortname')
		), false);
		$employee = $this->employee_category_model->assign_employee($employee, array(
			'fields'=>'job_name, id'
		));
		
		
		foreach($employee as $key=>$value)
		{
			$employee[$key]['thumb'] = $this->thumb->crop($value['face'], 80, 80);
		}
		
		$employee = $this->project_model->case_count_assign_employee($employee);
		
		// var_dump2( $employee );
		$this->tpl->assign('employees', $employee);
		
		$this->tpl->assign('decos', $cmps);
		
		// 推荐装修公司
		$rcmps = array(
			array('username'=>'xinjia', 'name'=>'鑫家建筑装饰设计（上海）有限公司', 'path'=>'xinjia.jpg'),
			array('username'=>'xunyizh', 'name'=>'上海洵艺装饰工程有限公司', 'path'=>'xunyi.jpg'),
			array('username'=>'zhihui', 'name'=>'上海知惠建筑装饰工程有限公司', 'path'=>'zhihui.jpg'),
			array('username'=>'jingshenzh', 'name'=>'上海精胜建筑装饰工程有限公司', 'path'=>'jingsheng.jpg')
		);
		
		$rcmps = $cmps = $this->project_model->case_count_assign_users($rcmps);
		
		$users = array();
		foreach($rcmps as $item){
			$users[] = $item['username'];
		}
		$decoss = $this->deco_model->get_list("select id, username from company where username in ('". implode("','", $users) ."')", '', true);
		$decoss = $decoss['list'];
		foreach($rcmps as $key=>$val){
			$val['deco'] = NULL;
			foreach($decoss as $item){
				if( $val['username'] == $item['username'] ){
					$val['deco'] = $item;
					break;
				}
			}
			$rcmps[$key] = $val;
		}
		
		// var_dump2($rcmps);
		
		$this->tpl->assign('rcmps', $rcmps);
		


		// 预算报价
		$this->load->model('budget/budget_config_model');	// 预算分类
		$this->load->model('budget/budget_model');
		$this->load->model('download/res_download_model');	// 资源下载
		
		$budget_configs = $this->budget_config_model->gets("select top 5 id, name, pid from [budget_config] where recmd = 1 order by sort_id asc");
		$budgets = $this->budget_model->get_list("select top 4 id, name, view_count, download_count, ps from budget order by addtime desc");
		$budgets['list'] = $this->budget_config_model->cfg_assign_budgets($budgets['list']);
		
		$this->tpl->assign('budgets', $budgets['list']);
		$this->tpl->assign('budget_configs', $budget_configs);
		
		
		//var_dump2($budget_configs);
		//var_dump2($budgets);
		
		$budget_download_count = $this->res_download_model->get_download_count();
		$this->tpl->assign('budget_download_count', $budget_download_count);
		
		
		// 装修项目
		if( ! isset($this->project_model) ) $this->load->model('company/usercase', 'project_model');
		$this->load->model('company/project_category_model');
		$projects = $this->project_model->get_case_list("select top 9 * from ( select id, username, casename as name, fm_image as fm, build_type_1, build_type_2, sdate, edate, addtime, num = row_number() over(partition by username order by addtime desc) from user_case where fm_image <> '' and username in ( select username from company where flag = 2 and delcode = 0 ) ) as tmp where num <= 1 order by addtime desc");
		$projects = $projects['list'];
		$projects = $this->project_model->image_count_assign_case($projects);
		$projects = $this->project_category_model->assign_to_project($projects);
		$projects = $this->deco_model->fill_collection($projects, array(
			'fields'=>array('username', 'company', 'shortname')
		), false);
		
		$projects = $this->employee_model->employee_assign_project($projects, array(
			'fields'	=> "id, job_id, username, true_name, face_image as face",
			'size' 		=> 1
		));
		foreach($projects as $key=>$value)
		{
			if( $value['employees'] != false )
			{
				foreach($value['employees'] as $k=>$v)
				{
					$value['employees'][$k]['thumb'] = $this->thumb->crop($v['face'], 60, 60);
				}
				$projects[$key] = $value;
			}
		}
		// var_dump2($projects);
		
		
		$this->tpl->assign('projects', $projects);
		
		
		
		$proj_cates = $this->project_category_model->gets("select top 10 id, name from [user_case_attr] where rmd = 1 order by pid asc", '', array('format'=>true));
		$this->tpl->assign('proj_cates', $proj_cates['list']);
		
		
		
		
		// 装修公司日志
		$this->load->model('company/usernews', 'deco_article_model');
		$sql = "select top 5 * from ( select id, title, username, addtime, num = row_number() over(partition by username order by addtime desc) from [user_news] where username in ( select username from company where delcode = 0 and register = 2 and hangye = '装潢公司' ) ) as temp where num <= 1 order by addtime desc";
		$deco_arts = $this->deco_article_model->gets($sql);
		$deco_arts = $deco_arts['list'];
		$this->tpl->assign('deco_arts', $deco_arts);
		
		// 投诉信息
		$this->load->model('sida/tousumodel', 'tousu_model');
		$this->load->model('sida/tousu_config_model');
		$tousu_complete_total = $this->tousu_model->get_status_count('complete');
		//$tousu_complete_total += $this->tousu_model->get_status_count('ing');
		
		$this->tpl->assign( 'tousu_complete_total', $tousu_complete_total );
		
		$tousu = $this->tousu_model->get_list("select top 4 id, title, puttime, status from [sendMessage1] where recycle = 0 order by id desc");
		$tousu = $this->tousu_config_model->attr_assign( $tousu['list'] );
		
		// var_dump2($tousu);
		
		$this->tpl->assign( 'tousu', $tousu );
		
		
		$this->load->model('Firlink', 'firlink');
		$fir_list = $this->firlink->get_index_links();
		$this->tpl->assign('fir_list', $fir_list);
		
		
		$this->tpl->assign('samples', $samples);
		
		//$this->tpl->assign('arts', $arts);
		//$this->tpl->assign('diaries', $diaries);
		
		//$this->tpl->assign('albums', $albums);
		//$this->tpl->assign('photo_cats', $photo_cats);
		
		$this->tpl->display($tpl);
		
		} else {
			$this->tpl->display($tpl);
			echo('<!-- cache -->');
		}
	}



}
?>