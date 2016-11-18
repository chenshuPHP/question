<?php if( !defined('BASEPATH') ) exit('~/controllers/member/member_base.php');

class member_base extends MY_Controller {
	
	public $login = NULL;
	public $base_user;
	
	public $member_url = '';
	
	public $userinfo = NULL;
	
	public function __construct(){
		
		parent::__construct();
		
		$this->load->model('company/Company', 'deco_model');
		
		/*
			exp: $login = array(
				'username'=>'dongshunzhuang',
				'member'=>1
			)
		*/
		
		## 检测是否有登录权限
		$login = $this->check_login();
		
		if( $login === false ){
			exit('您已退出登录 <a href="/">返回首页</a>');
		} else {
			$this->login = $login;
			$this->base_user = $login['username'];
		}
		
		
		
		$this->tpl->assign('login', $login);
		$this->tpl->assign('shop_url', $this->get_shop_url());
		$this->tpl->assign('r', urlencode($_SERVER['REQUEST_URI']));
		
		$urls = $this->config->item('url');
		$this->member_url = $urls['member'];
		
		$res = rtrim($urls['res'], '/') . '/member/';
		
		$this->tpl->assign('member_url', $this->member_url);
		$this->tpl->assign('member_res_url', $res);
		$this->tpl->assign('assist_url', $urls['assist']);
		
		$this->tpl->registerPlugin('modifier', 'member_tpl', array($this, 'member_tpl'));
		$this->tpl->registerPlugin('modifier', 'member_url', array($this, 'get_complete_url'));
		$this->tpl->registerPlugin('modifier', 'member_res_url', array($this, 'member_res_url'));
		
		// 站内信统计
		$this->load->model('company/member_letter_model');
		$new_letter_count = $this->member_letter_model->counter($this->base_user, array(
			'isVIP'				=> $this->isVIP(),
			'username'			=> $this->base_user,
			'open'				=> 0
		));
		$this->tpl->assign('new_letter_count', $new_letter_count);
		
		// 输出公共公司信息
		$this->userinfo = $this->deco_model->get("select id, username, company, logo, delcode, flag from company where username = '". $this->base_user ."'");
		$this->tpl->assign('common', $this->userinfo);
		
		$_kf = config_item('kf');
		$this->tpl->assign('53kf_url', $_kf['53kf_url']);
	
		$this->tpl->assign('module', 'member');
	}
	
	protected function userinfo( $args = array() )
	{
		if( $this->userinfo === NULL )
		{
			$config = array_merge(array(
				'fields'		=> 'id, username, delcode, flag'
			), $args);
			$this->userinfo = $this->deco_model->get("select ". $config['fields'] ." from [company] where username = '". $this->base_user ."'");
		}
	}
	
	// 是否高级会员
	protected function isVIP()
	{
		$this->userinfo();
		return $this->userinfo['flag'] == 2;
	}
	
	public function member_tpl($path = '')
	{
		return 'member/' . ltrim($path, '/');
	}


	// 检测用户是否登录
	// 2016-10-31 新增 $option 兼容token登录检测
	public function check_login( $option = array() ){
		
		if( isset( $option['token'] ) && $option['token'] != '' )
		{
			
		}
		else
		{
			$login = array('member'=>'', 'username'=>'');
			
			if( isset($_COOKIE['MEMBER']) ){
				$login['member'] = $_COOKIE['MEMBER'];
			}
			
			if( isset($_COOKIE['MEMBER_USER']) ){
				$login['username'] = $_COOKIE['MEMBER_USER'];
			}
			
			// 打开 SESSION 验证
			//if( ! isset( $_SESSION ) ) session_start();
			//if( ! isset( $_SESSION['MEMBER_USERNAME'] ) ) return FALSE;
			
			if( $login['username'] != '' && $login['member'] != '' ) {
				return $login;
			} else {
				return false;
			}
		}
		
	}

	
	
	
	private function get_shop_url(){
		$tmp = $this->deco_model->formatCompany( array('username'=>$this->login['username']) );
		$url = $tmp['link'];
		return $url;
	}
	
	public function get_complete_url($url){
		return $this->member_url . ltrim($url, '/');
	}
	
	public function member_res_url( $url )
	{
		static $urls = NULL;
		if( $urls === NULL ) $urls = config_item('url');
		return rtrim($urls['res'], '/') . '/member/' . ltrim($url, '/');
	}
	
	public function get_tpl( $tpl )
	{
		if( empty( $tpl ) ) return ;
		$_tmp = $this->tpl->template_dir[0];
		return rtrim($_tmp, '\\') . '\\member\\'. trim(str_replace('/', '\\', $tpl), '\\');
	}
	
	// 通用公告板
	public function billboard($args = array())
	{
		$config = array_merge(array(
			'text'		=> '',
			'display'	=> TRUE
		), $args);
	
		$content = '';
		
		if( $config['text'] != '' )
		{
			$content = 
			'<!-- 温馨提醒 -->' .
			'<div class="page_union">' .
				'<div class="page_union_ico"><img src="/member/images/img.png" /></div>' .
				'<div class="page_union_content">' .
					'<span class="page_union_tit">温馨提醒：</span>'. $config['text'] .
				'</div>' .
			'</div>';
		}
		return $content;
		
	}
	
}

?>