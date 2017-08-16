<?php

class Checking_Errors
{
	
	private static	$checking_errors = null;
	private			$view;
	
	private			$errors_list = array(
		// список ошибок которые вобще могут быть
						'lessParameters'
	);

	private			$errors = array();
	
	public			$errors_messages = array (
		// name => message ; вывод сообщений об ошибках
						'lessParameters'	=> 'Мало параметрів пошуку'
	);
	
	private function __construct()
	{
		
	}
	
	public static function getCheckingErrors()
	{
		if (self::$checking_errors == null)
		{
			self::$checking_errors = new Checking_Errors();
		}
		return self::$checking_errors;
	}
	
	public function addError($code, $error_value)
	{
		if ((!isset($this->errors[$code])) OR (!in_array($error_value, $this->errors[$code])))
		{
			$this->errors[$code][] = $error_value;
		}
	}
	
	public function CheckNoErrors()
	{
		if (empty($this->errors))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function errorMessage()
	{
		$this->view = View::getView();
		foreach ($this->errors as $code => $data)
		{
			foreach ($data as $line => $value)
			{
				if (in_array($code, $this->errors_list))
				{
					$str = "<br><b style='color:#bb0000'>&#39;" . $value . "&#39; Помилка! " . $this->errors_messages[$code] . "</b>";
					$this->view->addStr($str);
				}
				else
				{
					$str = "<br><b style='color:#bb0000'>&#39;" . $value . "&#39; Помилка! " . Config::$listOfSearchFieldsHints[$code] . "</b>";
					$this->view->addStr($str);
				}
			}
		}
	}
	
	

}

?>