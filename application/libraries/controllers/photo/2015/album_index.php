<?php
class album_index extends album_base {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function get_py_struct() {
		$this->load->model('photo/album_category_model');
		$cats = $this->album_category_model->get_list("select id, name, name2, char from photo_category where pid <> 0 order by char asc");
		$cats = $this->album_category_model->char_merge($cats['list']);
		$labels = array(
			array('name'=>'A B C D', 'pattern'=>'/[ABCD]/', 'childs'=>array()),
			array('name'=>'E F G', 'pattern'=>'/[EFG]/', 'childs'=>array()),
			array('name'=>'H I J K', 'pattern'=>'/[HIJK]/', 'childs'=>array()),
			array('name'=>'L M N', 'pattern'=>'/[LMN]/', 'childs'=>array()),
			array('name'=>'O P Q', 'pattern'=>'/[OPQ]/', 'childs'=>array()),
			array('name'=>'R S T', 'pattern'=>'/[RST]/', 'childs'=>array()),
			array('name'=>'U V W', 'pattern'=>'/[UVW]/', 'childs'=>array()),
			array('name'=>'X Y Z', 'pattern'=>'/[XYZ]/', 'childs'=>array()),
		);
		foreach( $labels as $key=>$value ) {
			foreach($cats as $item) {
				if( preg_match($value['pattern'], $item['py']) ) {
					$value['childs'][] = $item;
				}
			}
			$labels[$key] = $value;
		}
		return $labels;
	}
	
	public function py_labels() {
		$labels = $this->get_py_struct();
		$this->tpl->assign('labels', json_encode( $labels ));
		$this->tpl->display( $this->get_tpl('labels_script_data.html') );
	}
	
	public function home(){
		
		
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60 * 60 * 2;	// 缓存2小时
		$this->tpl->cache_dir = $this->tpl->cache_dir . '\\photo\\index\\';
		
		$tpl = $this->get_tpl('home.html');
	
		if( ! $this->tpl->isCached($tpl) ){
		
		$this->load->model('photo/album_category_model');
		$this->load->model('photo/album_image_model');
		$this->load->library( 'thumb' );
		
		$labels = $this->get_py_struct();
		
		unset($cats);
		
		$config = array(
			array(
				'type'=>'type_1',
				'cat'=>array(62,63,64,65,66,67),
				'xgt'=>array(
					array(229, 342, 248, 0),
					array(229, 342, 489, 0),
					array(229, 342, 730, 0),
					array(229, 342, 971, 0)
				)
			),
			array(
				'type'=>'type_2',
				'cat'=>array(132,16,13,4,144,12,5,114,9),
				'xgt'=>array(
					array(470, 342, 248, 0),
					array(229, 165, 730, 0),
					array(229, 165, 730, 177),
					array(229, 342, 971, 0)
				)
			),
			array(
				'type'=>'type_3',
				'cat'=>array(22,23,79,26,23,28,20,24,31),
				'xgt'=>array(
					array(229, 165, 248, 0),
					array(229, 165, 248, 177),
					array(229, 342, 489, 0),
					array(229, 342, 730, 0),
					array(229, 165, 971, 0),
					array(229, 165, 971, 177)
				)
			),
			array(
				'type'=>'type_4',
				'cat'=>array(76,101,137,113,42,142,41,112,111),
				'xgt'=>array(
					array(470, 342, 248, 0),
					array(229, 165, 730, 0),
					array(229, 165, 730, 177),
					array(229, 342, 971, 0)
				)
			)
		);
		
		$ids = array(62,63,64,65,66,67,132,16,13,4,144,12,5,114,9,22,23,79,26,23,28,20,24,31,76,101,137,113,42,142,41,112,111);
		$cats = $this->album_category_model->get_list( "select id, name from photo_category where id in (". implode(',', $ids) .")" );
		$cats = $cats['list'];
		foreach($config as $key=>$item) {
			$tmp = $item['cat'];
			$item['category'] = array();
			foreach($tmp as $id) {
				foreach ($cats as $it) {
					if( $it['id'] == $id ) {
						$item['category'][] = $it;
						break;
					}
				}
			}
			
			
			
			// 读取图片
			$image = $this->album_image_model->get_list("select top ". count( $item['xgt'] ) ." id, name, ImagePath as path from [photo_album_image] where cid in (". implode(',', $item['cat']) .") order by showcount desc");
			$image = $image['list'];
			
			for($i = 0; $i < count($image); $i++) {
				$image[$i]['thumb'] = $this->thumb->crop($image[$i]['path'], $item['xgt'][$i][0], $item['xgt'][$i][1]);
				$image[$i]['attr'] = $item['xgt'][$i];
			}
			
			unset($item['cat']);
			unset($item['xgt']);
			
			$item['images'] = $image;
			
			$config[$key] = $item;
			
		}
		
		// var_dump( $config );
		
		
		$this->tpl->assign('config', $config);
		$this->tpl->assign('labels', $labels);
		$this->tpl->display( $tpl );
		
			
		} else {
			$this->tpl->display( $tpl );
			echo('<!-- cached -->');
		}
		
		
		
	}
	
	private function test(){
	
	
		$this->tpl->caching = true;
		$this->tpl->cache_lifetime = 60 * 60 * 2;	// 缓存2小时
		$this->tpl->cache_dir = $this->tpl->cache_dir . '\\photo\\index\\';
		
		$tpl = $this->get_tpl('home.html');
	
		if( ! $this->tpl->isCached($tpl) ){
	
		$this->load->model('photo/album_category_model');
		$this->load->model('photo/Photo', 'album_model');
		$this->load->model('photo/album_image_model');
		
		
		$this->load->library('thumb');
		
		$navs = array(
			array('id'=>61, 'alias'=>'户型'),
			array('id'=>19, 'alias'=>'空间'),
			array('id'=>39, 'alias'=>'局部'),
			array('id'=>1, 'alias'=>'风格')
		);
		
		$navs = $this->album_category_model->assign_childs($navs, array(
			'size'=>6,
			'fields'=>'id, pid, name',
			'format'=>true
		));
		
		// 精选相册
		$rank_attrs = array(
			array('class'=>'xgt_photo_big', 'width'=>702, 'height'=>369),
			array('class'=>'xgt_photo_bd xgt_photo_one', 'width'=>232, 'height'=>232),
			array('class'=>'xgt_photo_bd xgt_photo_two', 'width'=>232, 'height'=>232),
			array('class'=>'xgt_photo_bd xgt_photo_three', 'width'=>232, 'height'=>232),
			array('class'=>'xgt_photo_bd xgt_photo_four', 'width'=>232, 'height'=>232),
			array('class'=>'xgt_photo_bd xgt_photo_five', 'width'=>232, 'height'=>232)
		);
		$rank_albums = $this->album_model->get_albums("select top 6 id, name, fm_image as fm from [photo_album] where fm_image <> '' order by rank desc");
		for($i = 0; $i < count($rank_albums); $i++){
			$rank_albums[$i]['class'] = $rank_attrs[$i]['class'];
			if( $i == 0 ){
				$rank_albums[$i]['thumb'] = $this->thumb->crop($rank_albums[$i]['fm'], $rank_attrs[$i]['width'], $rank_attrs[$i]['height']);
			} else {
				$rank_albums[$i]['thumb'] = $this->thumb->crop($rank_albums[$i]['fm'], $rank_attrs[$i]['width'], $rank_attrs[$i]['height']);
			}
		}
		
		// 赞值排序相册
		$sql = "select top 7 sum(assist_base) as assist_count, albumid as id, photo_album.name, photo_album.fm_image as fm from photo_album_image
				left join photo_album
				on photo_album_image.albumid = photo_album.id
				where albumid <> 0 
				group by albumid, photo_album.name, photo_album.fm_image order by assist_count desc;";
		$assists = $this->album_model->get_albums($sql);
		$assists = $this->album_image_model->count_assign($assists);
		
		// 家居空间
		$home_cats = array(
			array('id'=>23, 'name'=>'卧室', 'path'=>'layout_one.jpg', 'color'=>'#97cce0'),
			array('id'=>26, 'name'=>'厨房', 'path'=>'layout_two.jpg', 'color'=>'#c9a671'),
			array('id'=>22, 'name'=>'客厅', 'path'=>'layout_three.jpg', 'color'=>'#8bc296')
		);
		$home_cats = $this->album_category_model->format_batch($home_cats);
		$home_cats = $this->album_image_model->image_count_assign_category($home_cats);
		$this->tpl->assign('home_cats', $home_cats);
		
		// 局部之美
		// 背景墙 &　橱柜
		$part_cats = array(
			array('id'=>100, 'name'=>'背景墙', 'class'=>'hometype_one'),
			array('id'=>41, 'name'=>'橱柜', 'class'=>'hometype_two')
		);
		$part_cats = $this->album_category_model->format_batch($part_cats);
		$part_cats = $this->album_image_model->image_assign_category($part_cats, array(
			'size'=>5
		));
		
		//echo('<!--');
		//var_dump($part_cats);
		//echo('-->');
		
		$this->tpl->assign('part_cats', $part_cats);
		
		// 其他局部
		$part2_cats = array(
			array('id'=>'50', 'img'=>'taipeng.jpg', 'name'=>'台盆'),
			array('id'=>'56', 'img'=>'canzhuo.jpg', 'name'=>'餐桌'),
			array('id'=>'46', 'img'=>'shafa.jpg', 'name'=>'沙发'),
			array('id'=>'112', 'img'=>'yigui.jpg', 'name'=>'衣柜'),
			array('id'=>'111', 'img'=>'xiegui.jpg', 'name'=>'鞋柜'),
		);
		$part2_cats = $this->album_category_model->format_batch($part2_cats);
		$this->tpl->assign('part2_cats', $part2_cats);
		
		// 装修风格
		$style_cats = array(
			array('id'=>'16', 'path'=>'med_style', 'name'=>'地中海风格'),
			array('id'=>'5', 'path'=>'simple_style', 'name'=>'简约风格'),
			array('id'=>'6', 'path'=>'countryside_style', 'name'=>'田园风格'),
			array('id'=>'13', 'path'=>'european_style', 'name'=>'欧式风格'),
			array('id'=>'4', 'path'=>'modern_style', 'name'=>'现代风格'),
			array('id'=>'114', 'path'=>'southeast_style', 'name'=>'东南亚风格')
		);
		
		$style_cats = $this->album_category_model->format_batch($style_cats);
		$this->tpl->assign('style_cats', $style_cats);
		
		$off_cats = array(
			array('id'=>'120', 'name'=>'酒店', 'path'=>'jiudian.jpg'),
			array('id'=>'76', 'name'=>'宾馆', 'path'=>'binguan.jpg'),
			array('id'=>'82', 'name'=>'KTV', 'path'=>'ktv.jpg'),
			array('id'=>'79', 'name'=>'餐厅', 'path'=>'canting.jpg'),
			array('id'=>'129', 'name'=>'办公室', 'path'=>'bangongshi.jpg'),
			array('id'=>'80', 'name'=>'咖啡厅', 'path'=>'kafeiting.jpg'),
			array('id'=>'127', 'name'=>'酒吧', 'path'=>'jiuba.jpg'),
			array('id'=>'81', 'name'=>'会所', 'path'=>'huisuo.jpg', 'class'=>'gz_last')
		);
		$off_cats = $this->album_category_model->format_batch($off_cats);
		$this->tpl->assign('off_cats', $off_cats);
		
		
		$this->tpl->assign('rank_albums', $rank_albums);
		$this->tpl->assign('album_count', $this->album_model->get_album_count());
		$this->tpl->assign('assists', $assists);
		
		$this->tpl->assign('navs', $navs);
		
		
		$this->tpl->display( $tpl );
			
		} else {
			$this->tpl->display( $tpl );
			echo('<!-- cached -->');
		}
		
	}
	
	
}

?>