<?php

// model layer

require_once('Database.php');

class Song {
	public static function add($title, $album, $artist, $url){
		$sql = 'INSERT IGNORE INTO `song` (`title`, `album`, `artist`, `url`) VALUES("%s", "%s", "%s", "%s")';
		Database::getInstance()->query($sql, $title, $album, $artist, $url);
	}
	
	public static function get($queries, $sort_col, $sort_dir, $offset = 0, $limit = 10){
		// validate
		if ($sort_col && !in_array($sort_col, array('title', 'album', 'artist', 'tags')))
			$sort_col = null;
		if ($sort_dir != 'desc')
			$sort_dir = null;
		
		// build sql
		$where = implode(' OR ', array_filter(array_map(array('Song', 'sqlQuery'), $queries)));
		if (!$where)
			$where = '1';
		$sql = 'SELECT song.*, GROUP_CONCAT(IFNULL(CONCAT(tag.tag, ","), "")) AS tags FROM song LEFT JOIN song_tag ON song.id = song_tag.song_id LEFT JOIN tag ON song_tag.tag_id = tag.id GROUP BY song.id ORDER BY tag.tag desc';
		$sql = "SELECT * FROM ({$sql}) q WHERE {$where}";
		if ($sort_col){
			$sql .= " ORDER BY `{$sort_col}`";
			if ($sort_dir)
				$sql .= ' ' . $sort_dir;
		}
		$sql .= " LIMIT {$offset}, {$limit}";
		
		// go
		return Database::getInstance()->query($sql)->fetchAll();
	}
	
	private static function sqlQuery($query){
		$likes = implode(' AND ', array_filter(array(
			self::sqlMatch('title',  @$query->title),
			self::sqlMatch('album',  @$query->album),
			self::sqlMatch('artist', @$query->artist),
			self::sqlMatch('tags',   @$query->tags),
		)));
		if (!$likes)
			return null;
		return "($likes)";
	}
	
	private static function sqlMatch($field, $filter){
		if (!$filter)
			return null;
		if (!is_string($filter) || strlen($filter) > 256 || !preg_match('/^[.a-z0-9 ]+$/iD', $filter))
			return null;
		return "{$field} LIKE '%%{$filter}%%'";
	}
}

class Tag {
	public static function add($tag){
		if (!$tag || !is_string($tag) || strlen($tag) > 256 || !preg_match('/^[a-z0-9 ]+$/iD', $tag))
			return null;
		$sql = "SELECT `id` FROM tag WHERE tag.tag = '{$tag}'";
		if ($id = Database::getInstance()->query($sql)->fetch()->id)
			return $id;
		$sql = "INSERT INTO tag (`tag`) VALUES('{$tag}')";
		return Database::getInstance()->query($sql)->insertId();
	}
	
	public static function tagSong($song_id, $tags){
		// tags is an array of tag names; they might not all exist in the db yet
		$song_id = (int) $song_id;
		
		$sql = "DELETE FROM song_tag WHERE song_id = {$song_id}";
		Database::getInstance()->query($sql);
		foreach ($tags as $i)
			if ($tag_id = self::add($i)){
				$sql = "INSERT INTO song_tag (song_id, tag_id) VALUES({$song_id}, {$tag_id})";
				Database::getInstance()->query($sql);
			}
	}
}
