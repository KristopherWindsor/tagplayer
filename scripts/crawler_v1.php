<?php

require_once('../inc/Data.php');

function crawl($dir, $public_dir){
	$contents = file_get_contents($dir);
	$contents = explode('<a href="', $contents);
	array_shift($contents);
	
	foreach ($contents as $i){
		$i = substr($i, 0, strpos($i, '"'));
		if (!$i || $i[0] == '/' || $i[0] == '?')
			continue;
		
		if (substr($i, strlen($i) - 1) == '/')
			crawl($dir . $i, $public_dir . $i);
		else if (strpos($i, '.mp3'))
			add($public_dir . $i);
	}
}

function add($url){
	$data = array_map('urldecode', array_reverse(explode('/', $url)));
	
	$title = substr($data[0], 0, strlen($data[0]) - 4);
	$album = $data[1];
	$artist = $data[2];
	
	$t = explode(' ', $title);
	if (is_numeric($t[0])){
		unset($t[0]);
		$title = implode(' ', $t);
	}
	
	Song::add($title, $album, $artist, $url);
}

$public_url = 'http://67.188.70.238/music/';
$private_url = 'http://192.168.0.150/music/';

set_time_limit(20 * 60); // 20 minutes
crawl($private_url, $public_url);
