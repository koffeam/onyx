<?php

class View
{

	public static	$view	= null;
	private			$str	= array();
	
	
	private function __construct()
	{
		
	}
	
	public static function getView()
	{
		if (self::$view == null)
		{
			self::$view = new View();
		}
		return self::$view;
	}

	public function mainView()
	{
		echo "
			<html>
				<head>
					<title>ONYX</title>
					<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
					<link rel='stylesheet' type='text/css' href='view/style.css'>
				</head>
				<body>
					<form method='post' name='onyx' action='" . $_SERVER['PHP_SELF'] . "'>
		";
			foreach ($this->str as $value)
			{
				echo $value;
			}
		
		echo "
					</form>
				</body>
			</html>
		";
	} #<input type='submit' name='search' value='search'>

	public function addStr($string)
	{
		$this->str[] = $string . PHP_EOL;
	}
	
	public function stageOne()
	{
		$view = "
			<table width='100%' height='100%'>
				<tr>
					<td align='center'>
		";
		$this->addStr($view);
			$view = "<p style='font-size:32px'>Пошукова база даних</p>";
		$this->addStr($view);
			$view = "<p style='font-size:40px'>ONYX</p>";
		$this->addStr($view);
			$view = "<input type='submit' name='start' value='Почати роботу' style='width: 250px; height: 80px; font-size: 18pt'>";
		$this->addStr($view);
		$view = "	
					</td></tr>
				<tr><td height='25%'></td></tr>
			</table>
		";
		$this->addStr($view);
	}
	
	public function stageTwo()
	{
		
	}
	
	
	
}

?>