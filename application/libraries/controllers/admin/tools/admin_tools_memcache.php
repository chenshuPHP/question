<?php

// memcache 管理
// 2016-06-02
class admin_tools_memcache extends admin_base {
	
	public function __construct() {
		parent::__construct();
		
		$this->load->library('mem');
		
	}
	
	public function manage() {
		$keys = $this->mem->get_keys();
		$version = $this->mem->get_version();
		$online = $this->mem->serverIsOnline();
		$stats = $this->mem->getStats();
		$this->tpl->assign('keys', $keys);
		$this->tpl->assign('version', $version);
		$this->tpl->assign('online', $online);
		$this->tpl->assign('stats', $stats);
		$this->tpl->display( $this->get_tpl('tools/memcache/manage.html') );
	}
	
	public function view() {
		$key = $this->gr('key');
		$item = $this->mem->get( $key );
		$content = print_r($item, true);
		$this->tpl->assign('key', $key);
		$this->tpl->assign('content', $content);
		$this->tpl->display( $this->get_tpl('tools/memcache/view.html') );
	}
	
	public function delete() {
		$key = $this->gr('key');
		try{
			
			if( ! is_array($key) )
			{
				$key = array($key);
			}
			
			foreach($key as $item)
			{
				$this->mem->delete($item);
			}
			
			if( $this->gr('ptype') != 'ajax' )
			{
				echo('<script>location.href="'. $this->get_complete_url('tools/memcache/manage') .'";</script>');
			}
			else
			{
				echo('success');
			}
		}catch( Exception $e ) {
			echo( $e->getMessage() );
		}
		
	}
	
}

?>