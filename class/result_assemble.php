<?php

class Result_Assemble
{
	
	private $view;							// ссылка на вьюшку для вывода инфы
	public  $results			= array();	// Масив SQL ответов пришедших от всех баз
	public  $out				= array();	// Масив на вывод после отработки
	
	public function __construct()
	{
		$this->view = View::getView();
	}
	
	public function assemble()
	{
		$this->singleOutPerson();
		$this->transferData();
		$this->saveDataForReload();
	//	var_dump($this->results);
	//	var_dump($this->out);
	}
	
	private function singleOutPerson()
	{
		//ФИО + ДР объеденяем в единое поле персона, на него будет равняться вся другая инфа из баз
		foreach ($this->results as $db_name => $line_of_results)
		{
			if (!empty($line_of_results))
			{
				foreach ($line_of_results as $line_number => $values)
				{
					foreach ($values as $field_name => $field_data)
					{
						$this->results[$db_name][$line_number]['person'] = '';
						if (!empty($values['lastname']))
						{
							$values['lastname'] = mb_strtoupper($values['lastname']);
							$this->results[$db_name][$line_number]['person'] .= $values['lastname'] . " ";
						}
						if (!empty($values['firstname']))
						{
							$values['firstname'] = mb_strtoupper($values['firstname']);
							$this->results[$db_name][$line_number]['person'] .= $values['firstname'] . " ";
						}
						if (!empty($values['middlename']))
						{
							$values['middlename'] = mb_strtoupper($values['middlename']);
							$this->results[$db_name][$line_number]['person'] .= $values['middlename'] . " ";
						}
						if (!empty($values['birthdate']))
						{
							$birthdate = substr($values['birthdate'], 6, 2) . "."
									   . substr($values['birthdate'], 4, 2) . "."
									   . substr($values['birthdate'], 0, 4);
							$this->results[$db_name][$line_number]['person'] .= $birthdate;
						}
						unset($this->results[$db_name][$line_number]['lastname']);
						unset($this->results[$db_name][$line_number]['firstname']);
						unset($this->results[$db_name][$line_number]['middlename']);
						unset($this->results[$db_name][$line_number]['birthdate']);
						if ($this->results[$db_name][$line_number]['person'] == '')
						{
							if (!empty($values['company']))
							{
								$this->results[$db_name][$line_number]['person'] = $values['company'];
								unset($this->results[$db_name][$line_number]['company']);
							}
						}
					}
				}
			}
		}
	}
	
	private function transferData()
	{
		foreach ($this->results as $db_name => $line_of_results)
		{
			if (!empty($line_of_results))
			{
				foreach ($line_of_results as $line_number => $values)
				{
					foreach ($values as $field_name => $field_data)
					{
						$this->out[ $values['person'] ][$field_name][][$field_data] = $db_name;
					}
				}
			}
		}
	}
	
	// Сохранить результат при очистке полей ввода
	private function saveDataForReload()
	{
		if (Core::getRequest('search') != '')
		{
			$_SESSION['out'] = '';
		}		
		if (!empty($this->out))
		{
			$_SESSION['out'] = $this->out;
		}
		if (Core::getRequest('clear') != '')
		{
			$this->out = $_SESSION['out'];
		}
	}
	
	public function resultView()
	{
		if (!empty($this->out))
		{
			$this->view->addStr("<table border=1>");
			$this->view->addStr("<tr><td><b>Кількість записів у БД: " . count($this->out) . "</b></td></tr>");
			foreach ($this->out as $person => $data)
			{
				$this->view->addStr("<tr><td>");
				$str = '';
				$person_first = true;
				foreach ($data as $field_name => $data2)
				{
					if ($person_first == true)
					{
						$str .= "<b style='color:#006600'>" . $person . "</b><br>";
						$person_first = false;
					}
					if ($field_name != 'person')
					{
						$field_name_first = true;
						foreach ($data2 as $same_fields => $data3)
						{
							foreach ($data3 as $field_data => $db_name)
							{
								if (!empty($field_data))
								{
									if ($field_name_first == true)
									{
										$str .= "<b style='color:#000088'>" . Config::$listOfResultFields[$field_name] . "</b> ";
										$field_name_first = false;
									}
									$str .= "<b>" . $field_data . "</b>(<i style='color:#555555'>" . Config::$listOfDb[$db_name] . "</i>)";
									
								}
							}
							if ($field_name_first == false)
							{
								$str .= "; ";
							}
						}
						if ($field_name_first == false)
						{
							$str .= "<br>";
						}
					}
				}
				$this->view->addStr($str);
				$this->view->addStr("</td></td>");
			}
			$this->view->addStr("</table>");
		}
	}
	
	
}

?>