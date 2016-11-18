<?php

if(! defined('BASEPATH') ) exit('BASEPATH ERROR!');

// 编码类

class Encode {
	
	public function __construct(){}
	
	
	public function htmlencode($str = '', $mode = ENT_QUOTES){
		
		// htmlspecialchars(string, mode) 函数把一些预定义的字符转换为 HTML 实体
		// mode:
		// ENT_COMPAT 默认，仅编码双引号
		// ENT_QUOTES 编码单引号和双引号
		// ENT_NOQUOTES 不编码任何符号
		
		return htmlspecialchars($str, $mode);
		
	}
	
	// $br_option 是否将回车换行转换为<br />
	public function htmldecode($str = '', $br_option = false, $mode = ENT_QUOTES){
		
		// htmlspecialchars_decode($string, $mode) 函数把一些预定义的 HTML 实体转换为字符。
		// $mode 同 htmlspecialchars 的 mode 语法一致
		
		$str = htmlspecialchars_decode($str, $mode);
		
		if( $br_option == true ){
			$str = preg_replace('/\n/s', '<br />', $str);
		}
		
		return $str;
		
	}
	
	// 移除内容中的html标签
	public function removeHtml($str){
		return preg_replace('/<.*?>/', '', $str);
	}
	
	// 移除链接
	public function removeLink($string){
		if( empty($string) ) return '';
		if( strpos($string, '<') == false ){
			$string = $this->htmldecode($string);
		}
		$string = preg_replace('/<a(\s.+?)?>/i', '', $string);
		$string = preg_replace('/<\/a>/i', '', $string);
		return $string;
	}
	
	public function removeSpace($str, $Encode='GBK'){
		return preg_replace('/(&nbsp;)|\s*?/i','',$str);
	}

	// 移除内容中的html标签和空格
	public function removeHtmlAndSpace($str){
		if( strpos($str, '>') == false ){
			$str = $this->htmldecode($str);
		}
		$str = $this->removeHtml($str);
		$str = $this->removeSpace($str);
		return $str;
	}
	
	
	// 获取POST提交的值，且进行编码
	// $name 表单控件名称
	// $trim 是否过滤头尾空白, 默认：是
	// 默认ajax 提交的复杂对象键值会丢失，因为_encode()方法原使用array_map()导致
	// 现在新增参数$args 可设置retain_key = true带入到 _encode() 方法中将不使用array_map()而是使用递归实现保留键值
	public function getFormEncode($name, $trim = true, $args = array()){
		$config = array(
			'retain_key'=>false	// 是否保留数组键值
		);
		$config = array_merge($config, $args);
		if( isset( $_POST[$name] ) ){
			return $this->_encode($_POST[$name], $trim, $config);
		} else {
			return '';
		}
	}
	
	// 获取 get 方式的值
	// CodeIgniter 的 path_info 方式，在 $_GET 中获取不到值
	// 所以通过 $_REQUEST 方式获取
	public function get_request_encode($name, $trim = true){
		if( isset( $_REQUEST[$name] ) ){
			return $this->_encode($_REQUEST[$name], $trim);
		} else {
			return '';
		}
	}
	
	public function gbk_to_utf8($string){
		return iconv('gbk', 'utf-8', $string);
	}

	
	/*
	private function __encode($value, $trim = true){
		if( is_array($value) ){
			foreach($value as $key=>$value){
				$value[$key] = $this->__encode($value, $trim);
			}
		} else {
			$value = $this->htmlencode($value);
			if( $trim ) $value = trim($value);
		}
	}
	*/

	private function _encode($value, $trim = true, $args = array()){
		
		$config = array(
			'retain_key'=>false	// 是否保留数组键值
		);
		$config = array_merge($config, $args);
		
		if( is_array($value) ){
			if( $config['retain_key'] == false ){
				$value = array_map(array($this, '_encode'), $value, array($trim));
			} else {
				foreach($value as $key=>$val){
					$value[$key] = $this->_encode($val, $trim, $config);
				}
			}
		} else {
			$value = $this->htmlencode($value);
			if( $trim ) $value = trim($value);
		}
		return $value;
	}
	
	// 获取分页数字
	public function get_page($name = 'page'){
		
		$page = $this->get_request_encode($name);
		if( ! preg_match('/^[1-9]\d*$/', $page) ) $page = 1;
		return $page;
		
	}
	
	public function rurlencode($string)
	{
		$string = str_replace('&amp;', '&', $string);
		$string = str_replace(';=', '=', $string);
		return $string;
	}
	
	// 回去返回页面
	// 默认参数名称 r
	public function get_rurl($name = 'r'){
		if( isset($_REQUEST[$name]) ){
			$tmp = $_REQUEST[$name];
			$tmp = $this->rurlencode($tmp);
			return $tmp;
		} else {
			return '';
		}
	}
	

	// 计算中文字符串长度
	function utf8_strlen($string = null) {
		// 将字符串分解为单元
		//preg_match_all('/./us', $string, $match);
		// 返回单元个数
		//return count($match[0]);
		return mb_strlen($string, 'UTF-8');
	}
	
	// 获取纯文本描述，不会截断完整的句子
	// $content 原文
	// $length 最大长度的文字
	// $brOpt 是否保留换行<br />
	function get_text_description($content, $length = 150, $brOpt = true){
		
		$content = $this->htmldecode($content);
		
		if( $brOpt == true ){
			$content = str_replace("</p>", "</p>{next}", $content);
		}
		
		$content = $this->removeHtmlAndSpace($content);
		$content = preg_split('/\.|？|\!|！|。|。|,|，/', $content, -1, PREG_SPLIT_NO_EMPTY);
		$result = '';
		for($i = 0; $i < count($content); $i ++){
			if( $this->utf8_strlen($result . $content[$i]) > $length ){
				$result = rtrim($result, '。') . "。";
				break;
			}
			$result .= $content[$i] . "。";
		}
		$result = trim(str_replace('{next}', "\n", $result), "\n");
		$result = preg_replace("/\n+/", "\n", $result);	// 替换多个连续的\n 为一个\n
		return $result;
	}
	
	// 移除文章内容中的外部链接
	public function remove_outside_link($content, $allow_urls = array('shzh.net')){
		$rule = join('|', $allow_urls);
		$rule = preg_replace('/[\n\r]/', '', $rule);
		$rule = str_replace('.', "\\.", $rule);
		$rule = str_replace('/', "\\/", $rule);
		$arr = '';
		$content = str_replace("\r", '<{$r$}>', $content);
		$content = str_replace("\n", '<{$n$}>', $content);
		preg_match_all("/<a([^>]*)>(.*?)<\/a>/i", $content, $arr);
		if( is_array($arr[0]) ){
			$arr_labels = array();
			$arr_texts = array();
			foreach($arr[0] as $key=>$value){
				if( ! preg_match('/'. $rule .'/i', $value) ){
					$arr_labels[] = $value;
					$arr_texts[] = $arr[2][$key];
				}
			}
			if( count($arr_labels) > 0 ){
				foreach($arr_labels as $key=>$value){
					$content = str_replace($value, $arr_texts[$key], $content);
				}
			}
		}
		$content = str_replace('<{$r$}>', "\r", $content);
		$content = str_replace('<{$n$}>', "\n", $content);
		return $content;
	}
	
	// 从身份证号码中得到生日日期
	public function get_birthday($code, $code_length = 18){
		$arr['year'] = substr($code, 6, 4);
		$arr['month'] = substr($code, 10, 2);
		$arr['day'] = substr($code, 12, 2);
		return implode('-', $arr);
	}
	
	public function get_ip(){
		
		// ali cdn 提供的访客 IP
		if( isset( $_SERVER['HTTP_ALI_CDN_REAL_IP'] ) ){
			$ip = $_SERVER['HTTP_ALI_CDN_REAL_IP'];
		}

		if( empty($ip) ) $ip = $_SERVER["REMOTE_ADDR"];
		if( empty($ip) ) $ip = $_SERVER['REMOTE_HOST'];
		return $ip;
	}

	// 301 跳转
	public function moved_permanently($url){
		header('HTTP/1.1 301 Moved Permanently');
		header('Location:' . $url);
		exit();
	}
	
	public function get_current_url(){
		return urlencode( 'http://' . rtrim($_SERVER['HTTP_HOST'], '/') . '/' . ltrim($_SERVER['REQUEST_URI'], '/') );
	}
	
}

?>