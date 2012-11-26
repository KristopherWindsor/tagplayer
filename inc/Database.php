<?php

// this is a faux-oop mysql wrapper I have used in a few different personal projects, slightly modified

require_once('config.php');

class Database
	{
	private static $instance;
	
	private function __construct()
		{
		mysql_connect($GLOBALS['db_config']['host'], $GLOBALS['db_config']['username'], $GLOBALS['db_config']['password']) or die(mysql_error());
		mysql_select_db($GLOBALS['db_config']['database']);
		}
	
	public static function getInstance()
		{
		if (!self::$instance)
			self::$instance = new Database();
		return self::$instance;
		}
	
	function query()
		{
		$args = func_get_args();
		$query = new Query();
		call_user_func_array(array($query, '__construct'), $args);
		return $query;
		}
	}

class Query
	{
	private $query;
	private $result;
	private $cache;

	function __construct()
		{
		$args = func_get_args();
		if (!count($args))
			return;
		
		$format = array_shift($args);
		foreach ($args as &$arg)
			$arg = $this->escape($arg);
		$this->query = $args ? vsprintf($format, $args) : $format;

		$this->result = mysql_query($this->query) or die(mysql_error());
		if ($this->result !== true && $this->result !== false)
			$this->cache = mysql_fetch_object($this->result);
		}

	function fetch()
		{
		$res = $this->cache;
		if ($this->result !== true && $this->result !== false)
			$this->cache = mysql_fetch_object($this->result);
		return $res;
		}

	function fetchAll()
		{
		// should be re-implemented
		$res = array();
		while ($row = $this->fetch())
			{
			if ($row === true)
				break;
			$res[] = $row;
			}
		return $res;
		}
	
	private static function escape($param)
		{
		return mysql_real_escape_string($param);
		}
	}
