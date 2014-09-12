<?php

class EasyCache {
	const DEFAULT_EXPIRY_TIME = 10;
	const DEFAULT_CLEAR_MODE = 'all';

	function __construct($host,$user,$pass){
		$c = mysql_connect($host,$user,$pass) or die("Unable to connect cache db server!");
		mysql_select_db("cache_system",$c);
	}

	public function get($key){
		$sql="SELECT * FROM cache WHERE id = '".$key."' AND expire_on >= " . time();
		$res = mysql_query(  $sql);
		$row = mysql_fetch_assoc( $res );
		if($row){
			return $row['content'];
		}
	}

	public function save($key, $content, $expire=self::DEFAULT_EXPIRY_TIME, $tag =''){
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

	public function refresh($type,$item, $expire=self::DEFAULT_EXPIRY_TIME){
		$expire =  time()+$expire;
		switch($type){
			case "key":
			$this->_refresh_key($item, $expire);
			break;
			case "tags":
			$this->_refresh_tags($item, $expire);
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


error_reporting(E_ALL ^ E_DEPRECATED);
$cache =  new EasyCache("127.0.0.1","root","root");
$key = 'abc';
if($content = $cache->get($key)){
	echo "Displaying from cache\n";
	echo "Key:".$key."\n";
	echo "Content:".$content."\n";
}else{
	$content = "Key of this cache is ". $key;
	echo  "Key:".$key."\n";
	echo  "Content:".$content."\n";
	try{
		$cache->save($key,$content,20);
	}catch(Exception $e){
		echo $e;
	}
}