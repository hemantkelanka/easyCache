<?php

class EasyCache {
	const DEFAULT_CLEAR_MODE = 'all';
	private $default_expire;
	function __construct($config){
		$this->default_expire = (@$config['expire'])?$config['expire']:10;
		$host = $config['host'];
		$username = $config['username'];
		$password = $config['password'];

		$c = mysql_connect($host, $username, $password) or die("Unable to connect cache db server!");
		mysql_select_db("cache_system",$c);
	}

	public function get($key){
		$sql="SELECT * FROM cache WHERE id = '".$key."' AND expire_on >= " . time();
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc( $res );
		if($row){
			return $row['content'];
		}
		return false;
	}

	public function save($key, $content, $expire = 0, $tag = ''){
		if($expire==0){
			$expire = $this->default_expire;
		}
		$md5_hash =  md5($content);
		$qr = "INSERT INTO cache SET content = '".$content."', id= '".$key."', tag = '".$tag."', md5 ='".$md5_hash."', expire_on = ".(time()+$expire)." ON DUPLICATE KEY UPDATE content = '".$content."', md5 ='".$md5_hash."', expire_on = ".(time()+$expire);
		if(mysql_query($qr))
			return true;
		else
			throw new Exception("Oops! There is something wrong happend while saving the cache!");
	}

	public function delete($key){
		if($this->_clear_key($key)){
			return 1;
		}else{
			throw new Exception("Oops! No matching key found in cache!");
		}
	}

	public function refresh($type,$item, $expire = 0){
		if($expire==0){
			$expire = $this->default_expire;
		}
		$expire_on =  time()+$expire;
		switch($type){
			case "key":
			$this->_refresh_key($item, $expire_on);
			break;
			case "tags":
			$this->_refresh_tags($item, $expire_on);
			break;
		}
	}

	public function clear($mode=self::DEFAULT_CLEAR_MODE,$tags){
		switch($mode){
			case "tags":
			$this->_clear_tags($tags);
			break;
			case "all":
			$this->_clear_all();
			break;
			default:
			$this->_clear_all();
		}
	}

	protected function _clear_key($key){
		$qr = "DELETE FROM cache WHERE  id= '".$key."'";
		return mysql_query($qr);
	}

	protected function _clear_tags($tags){
		if($tags){
			foreach ($tags as $tag) {
				$qr = "DELETE FROM cache WHERE  `tag`= '".$tag."'";
				mysql_query($qr);	
			}
		}
	}

	protected function _clear_all(){
		$qr = "TRUNCATE cache";
		mysql_query($qr);
	}

	protected function _refresh_key($key, $expire){
		$qr = "UPDATE cache set expire_on = ".$expire." WHERE id= '".$key."'";
		mysql_query($qr);
	}

	protected function _refresh_tags($tags, $expire){
		if($tags){
			foreach ($tags as $tag) {
				$qr = "UPDATE cache set expire_on = ".$expire." WHERE `tag`= '".$tag."'";
				mysql_query($qr);
			}
		}
	}
}
