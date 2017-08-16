<?php

class DB
{
	
	private static	$db = null;
	private 		$mysqli;
	
	
	private function __construct()
	{
		$this->mysqli = new mysqli(Config::$host, Config::$login, Config::$password, Config::$db_name);
		if ($this->mysqli->connect_error)
		{
			die('Ошибка подключения к базе данных (' . $mysqli->connect_errno) . ')'
				. $mysqli->connect_error;
		}
		$this->mysqli->set_charset("utf8");	
	}
	
	public static function getDB()
	{
		if (self::$db == null) self::$db = new DB();
		return self::$db;
	}

	public function query($query)
	{
	//	echo "<br>" . $query . "<br>";
		$sql = $this->mysqli->query($query);
		$result = $sql->fetch_all(MYSQLI_ASSOC);
		return $result;

	}

	
}

?>