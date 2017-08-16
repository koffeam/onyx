<?php

class Core
{
	
	private $databases;
	private $view;
	private $searcher;
	private $result_assemble;
	private $errors;
	
	
	public function __construct()
	{
		$this->view = View::getView();
		$this->errors = Checking_Errors::getCheckingErrors();
		$this->session();
		$this->buttons();
		if (!isset($_SESSION['stage']))
		{ 
			$_SESSION['stage'] = 100;
		}
		if ($_SESSION['stage'] == 100)
		{
			$this->stageOne();
		}
		else if ($_SESSION['stage'] == 200)
		{
			$this->stageTwo();
		}
		else if ($_SESSION['stage'] == 300)
		{
			$this->stageThree();
		}
		$this->view->mainView();
	}

	public static function getRequest($param)
	{
		$value = '';
		if (isset($_REQUEST[$param]))
		{
			$value = $_REQUEST[$param];
		}
		return $value;
	}
	
	private function session()
	{
		ini_set('session.use_cookies', 0);
		ini_set('session.use_trans_sid', 1);
		ini_set('session.save_path', $_SERVER['DOCUMENT_ROOT'] . '/onyx2/sessions');
		if (isset($_REQUEST['PHPSESSID']))
		{
			session_id($_REQUEST['PHPSESSID']);
		}
		session_start();
		$session_id = "<input hidden type='text' name='PHPSESSID' value='" . session_id() . "'>";
		$this->view->addstr($session_id);
/*					echo "<br>Counter:";
					if (!isset($_SESSION['counter']))
					{
						$_SESSION['counter'] = 0;
					} else {
						$_SESSION['counter']++;
					}
					echo $_SESSION['counter'];
*/
	}
	
	public static function inSession($var_name, $param)
	{
		$_SESSION[$var_name] = $param;
	}
	
	public static function getSession($var_name)
	{
		if (isset($_SESSION[$var_name]))
		{
			return $_SESSION[$var_name];
		} else {
			return '';
		}
	}
	
	private function buttons()
	{
		if ($this->getRequest('start') != '')
		{
			$_SESSION['stage'] = 200;
		}
		if ($this->getRequest('search') != '')
		{
			$_SESSION['stage'] = 300;
		}
	}
	
	private function stageOne()
	{
		$this->view->stageOne();
	}
	
	private function startBasesAndSearcher()
	{
		include("/class/data_base_main.php");
		foreach (Config::$listOfDb as $db_class_name => $db_name)
		{
			include("/class/" . $db_class_name . ".php");
			$this->databases[$db_class_name] = new $db_class_name;
		}
		$this->searcher = new Searcher($this->databases);
	}
	
	private function stageTwo()
	{
		$this->startBasesAndSearcher();
	}
	
	private function stageThree()
	{
		$this->startBasesAndSearcher();
		$this->result_assemble = new Result_Assemble();
		foreach ($this->databases as $db_class_name => $object)
		{
			$object->pregMatchFields();
		}
		foreach ($this->databases as $db_class_name => $object)
		{
			$object->manage();
			$this->result_assemble->results[$db_class_name] = $object->result;
		}
		$this->result_assemble->assemble();
		$this->errors->errorMessage();
		$this->result_assemble->resultView();
		
	}
	
	
}

?>