<?php
include_once 'cache.class.php';

error_reporting(E_ALL ^ E_DEPRECATED);


$cache =  new EasyCache(array('host'=>'127.0.0.1','username'=>'root','password'=>'root'));
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