<?php

// handle AJAX requests

require_once('../inc/Data.php');

$sort_col = @$_POST['sort_col'];
$sort_dir = @$_POST['sort_dir'];
$query    = @$_POST['query'];
$echo     = (int) @$_POST['echo'];

$queries = array();
if ($query){
	$parts = array_filter(explode(' ', $query));
	for ($i = 0; $i <= count($parts); $i++)
	{
		$p1 = implode(' ', array_slice($parts, 0, $i));
		$p2 = implode(' ', array_slice($parts, $i));
		$queries[] = (object) array('title'   => $p1, 'artist' => $p2);
		$queries[] = (object) array('title'   => $p2, 'artist' => $p1);
		$queries[] = (object) array('title'   => $p1, 'album'  => $p2);
		$queries[] = (object) array('title'   => $p2, 'album'  => $p1);
		$queries[] = (object) array('artist'  => $p1, 'album'  => $p2);
		$queries[] = (object) array('artist'  => $p2, 'album'  => $p1);
	}
	
	$queries[] = (object) array('tags' => $query);
}

header('Content-type: application/json');
header("echo: {$echo}");
echo json_encode(Song::get($queries, $sort_col, $sort_dir, 0, 50));
