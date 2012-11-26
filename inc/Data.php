<?php

// model layer

require_once('Database.php');

class Song {
	public static function add($title, $album, $artist, $url){
		$sql = 'INSERT IGNORE INTO `song` (`title`, `album`, `artist`, `url`) VALUES("%s", "%s", "%s", "%s")';
		Database::getInstance()->query($sql, $title, $album, $artist, $url);
	}
}

class Tag {
	
}
