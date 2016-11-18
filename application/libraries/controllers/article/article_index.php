<?php

// 资讯首页
// kko4455@163.com
// 2015-11-06

class article_index extends article_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	private function extraction_article_ids($arts){
		$ids = array();
		foreach($arts as $item){
			$ids[] = $item['id'];
		}
		return $ids;
	}
	
	private function _add_ids(&$nids, $arts){
		$ids = $this->extraction_article_ids($arts);
		foreach($ids as $item){
			$nids[] = $item;
		}
	}
	
	public function home(){
		
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60 * 60 * 1; 	// 单位 分钟, 缓存1小时
		$this->tpl->cache_dir = $this->tpl->cache_dir . 'article\\home2015\\';
		
		$tpl = 'article/home.html';
		
		if( ! $this->tpl->isCached($tpl) ){
		
		$this->load->model('article/Article', 'article_model');
		$this->load->model('article/Category', 'article_category_model');
		
		$sheji_id = 6;
		
		$sheji_no_ids = array();
		$sheji_cat_childs = $this->article_category_model->getChilds($sheji_id, 6);
		
		$sheji_img_arts = $this->article_model->get_list("select top 2 id, title, short_title, imgPath as path, clsid from art_art where cid like '%,". $sheji_id .",%' and imgpath <> '' order by addtime desc");
		$sheji_img_arts = $sheji_img_arts['list'];
		
		$sheji_img_arts = $this->article_category_model->assign($sheji_img_arts);
		
		
		
		$this->_add_ids($sheji_no_ids, $sheji_img_arts);
		
		$sheji_img_arts_2 = $this->article_model->get_list("select top 3 id, title, short_title, imgPath as path, description, clsid from art_art where cid like '%,". $sheji_id .",%' and imgpath <> '' and id not in (". implode($sheji_no_ids, ',') .") order by addtime desc");
		$sheji_img_arts_2 = $sheji_img_arts_2['list'];
		
		$this->_add_ids($sheji_no_ids, $sheji_img_arts_2);
		
		$sheji_arts = $this->article_model->get_list("select top 8 id, title, short_title, imgPath as path, keyword, clsid from art_art where cid like '%,". $sheji_id .",%' and id not in (". implode($sheji_no_ids, ',') .") order by addtime desc");
		$sheji_arts = $sheji_arts['list'];
		
		$sheji_hot_cats = $this->article_category_model->get_max_art_cat(array(
			'like'=>'%,'. $sheji_id .',%',
			'top'=>8
		));
		
		$this->tpl->assign('sheji_cat_childs', $sheji_cat_childs);
		$this->tpl->assign('sheji_img_arts', $sheji_img_arts);
		$this->tpl->assign('sheji_img_arts_2', $sheji_img_arts_2);
		$this->tpl->assign('sheji_arts', $sheji_arts);
		$this->tpl->assign('sheji_hot_cats', $sheji_hot_cats);
		
		$fengshui_id = 55;
		
		$fengshui_no_ids = array();
		$fengshui_cat_childs = $this->article_category_model->getChilds($fengshui_id, 6);
		
		$fengshui_img_arts = $this->article_model->get_list("select top 2 id, title, short_title, imgPath as path, clsid from art_art where cid like '%,". $fengshui_id .",%' and imgpath <> '' order by addtime desc");
		$fengshui_img_arts = $fengshui_img_arts['list'];
		$fengshui_img_arts = $this->article_category_model->assign($fengshui_img_arts);
		
		$this->_add_ids($fengshui_no_ids, $fengshui_img_arts);
		
		$fengshui_img_arts_2 = $this->article_model->get_list("select top 3 id, title, short_title, imgPath as path, description, clsid from art_art where cid like '%,". $fengshui_id .",%' and imgpath <> '' and id not in (". implode($fengshui_no_ids, ',') .") order by addtime desc");
		$fengshui_img_arts_2 = $fengshui_img_arts_2['list'];
		
		$this->_add_ids($fengshui_no_ids, $fengshui_img_arts_2);
		
		$fengshui_arts = $this->article_model->get_list("select top 8 id, title, short_title, keyword, clsid from art_art where cid like '%,". $fengshui_id .",%' and id not in (". implode($fengshui_no_ids, ',') .") order by addtime desc");
		$fengshui_arts = $fengshui_arts['list'];
		
		$fengshui_hot_cats = $this->article_category_model->get_max_art_cat(array(
			'like'=>'%,'. $fengshui_id .',%',
			'top'=>8
		));
		
		$this->tpl->assign('fengshui_cat_childs', $fengshui_cat_childs);
		$this->tpl->assign('fengshui_img_arts', $fengshui_img_arts);
		$this->tpl->assign('fengshui_img_arts_2', $fengshui_img_arts_2);
		$this->tpl->assign('fengshui_arts', $fengshui_arts);
		$this->tpl->assign('fengshui_hot_cats', $fengshui_hot_cats);
		
		// 选材常识
		$xuancai_id = 97;
		$xuancai_no_ids = array();
		$xuancai_cat_childs = $this->article_category_model->getChilds($xuancai_id, 5);
		$xuancai_img_arts = $this->article_model->get_list("select top 1 id, title, short_title, imgPath as path, clsid from art_art where cid like '%,". $xuancai_id .",%' and imgpath <> '' order by addtime desc");
		$xuancai_img_arts = $xuancai_img_arts['list'];
		
		$this->_add_ids($xuancai_no_ids, $xuancai_img_arts);
		
		$xuancai_arts = $this->article_model->get_list("select top 5 id, title, short_title, keyword, clsid from art_art where cid like '%,". $xuancai_id .",%' and id not in (". implode($xuancai_no_ids, ',') .") order by addtime desc");
		$xuancai_arts = $xuancai_arts['list'];
		
		$this->tpl->assign('xuancai_cat_childs', $xuancai_cat_childs);
		$this->tpl->assign('xuancai_img_arts', $xuancai_img_arts);
		$this->tpl->assign('xuancai_arts', $xuancai_arts);
		
		// 施工验收
		$yanshou_id = 1;
		$yanshou_no_ids = array();
		$yanshou_cat_childs = $this->article_category_model->getChilds($yanshou_id, 3);
		$yanshou_img_arts = $this->article_model->get_list("select top 1 id, title, short_title, imgPath as path, clsid from art_art where cid like '%,". $yanshou_id .",%' and imgpath <> '' order by addtime desc");
		$yanshou_img_arts = $yanshou_img_arts['list'];
		
		$this->_add_ids($yanshou_no_ids, $yanshou_img_arts);
		
		$yanshou_arts = $this->article_model->get_list("select top 5 id, title, short_title, keyword, clsid from art_art where cid like '%,". $yanshou_id .",%' and id not in (". implode($yanshou_no_ids, ',') .") order by addtime desc");
		$yanshou_arts = $yanshou_arts['list'];
		
		$this->tpl->assign('yanshou_cat_childs', $yanshou_cat_childs);
		$this->tpl->assign('yanshou_img_arts', $yanshou_img_arts);
		$this->tpl->assign('yanshou_arts', $yanshou_arts);
		
		// 监理日记
		$this->load->model('diary/diary_model');
		$this->load->model('diary/diary_extend_model');
		
		$sql = "select top 2 id, title, town, address, area, budget, deco_name, addtime, detail, image_count from diary order by addtime desc";
		
		$res = $this->diary_model->get_list($sql);
		$res['list'] = $this->diary_extend_model->image_count_assign($res['list']);
		$res['list'] = $this->diary_extend_model->image_path_assign($res['list'], array('size'=>5));
		foreach($res['list'] as $key=>$val){
			$val['desc'] = $this->encode->get_text_description($val['detail'], 150, false);
			$res['list'][$key] = $val;
			unset($res['list'][$key]['detail']);
		}
		$this->tpl->assign('list', $res['list']);
		
		// 样板房视频
		$sample_no_ids = array();
		$this->load->model('archive/archive_cas_model');
		$samples = $this->archive_cas_model->get_list('select top 4 id, tid, name, huxing, area, budget, fm, style, wages, video from archive_cas where sample = 1 order by name Asc');
		$samples = $samples['list'];
		$samples = $this->archive_cas_model->format_batch_sample($samples);
		$this->_add_ids($sample_no_ids, $samples);
		
		$samples_2 = $this->archive_cas_model->get_list('select top 4 id, tid, name from archive_cas where sample = 1 and id not in ('. implode(',', $sample_no_ids) .') order by name Asc');
		$samples_2 = $this->archive_cas_model->format_batch_sample($samples_2['list']);
		
		$this->tpl->assign('samples', $samples);
		$this->tpl->assign('samples_2', $samples_2);
		
		// 装修流程
		$process = $this->article_model->get_list("select top 8 id, title, short_title, clsid, description from art_art where cid like '%,199,%' order by addtime desc");
		
		$this->tpl->assign('process', $process['list']);
		
		$this->tpl->assign('body_class', 'w1200');
		$this->tpl->display( $tpl );
		
		} else {
			$this->tpl->display( $tpl );
			echo('<!-- cache -->');
		}
		
	}
	
	
}


?>