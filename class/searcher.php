<?php

class Searcher
{
	private $databases;
	private $view;
	private $searchFieldsFromDb 	= array();
	private $uniqFields 			= array();
	private $fillFields 			= array();
	
	public function __construct($databases)
	{
		$this->databases = $databases;
		$this->view = View::getView();
		$this->getSearchFields();
		$this->setUniqFields();
		$this->viewSearchFields();
		$this->returnSearchFieldsToDb();
	}
	
	private function getSearchFields()
	{
		foreach ($this->databases as $db_class_name => $db_class)
		{
			foreach ($db_class->listOfSearchFields as $key => $field)
			{
				$this->searchFieldsFromDb[$db_class_name][$key] = $field;
			}
		}
	}

	private function setUniqFields()
	{
		foreach ($this->searchFieldsFromDb as $db_class_name => $fields)
		{
			foreach ($fields as $key => $name)
			{
				if (!(in_array($name, $this->uniqFields)))
				{
					$this->uniqFields[] = $name;
				}
			}
		}
	}
	
	private function viewSearchFields()
	{
		$this->view->addStr("<table>");
		foreach ($this->uniqFields as $key => $name)
		{
			if (Core::getRequest('clear') != '')
			{
				$this->fillFields[$name] = '';
			}
			else
			{
				$this->fillFields[$name] = Core::getRequest('search_' . $name);
			}
			$str = "<tr>
						<td>
							<b>" . Config::$listOfSearchFieldsNames[$name] . "</b>
						</td>
						<td>
							<input type='text' name='search_" . $name . "' value='" . $this->fillFields[$name] . "' title='" . Config::$listOfSearchFieldsHints[$name] . "'>
						</td>
						<td>
							
						</td>
					</tr>
			";
			$this->view->addStr($str);
		}
		$this->view->addStr("</table>");
		$this->view->addStr("<input type='submit' name='search' value='Пошук'>");
		$this->view->addStr("&nbsp;&nbsp;&nbsp;&nbsp;");
		$this->view->addStr("<input type='submit' name='clear' value='Очистити'>");
	}
	
	private function returnSearchFieldsToDb()
	{
		foreach ($this->databases as $db_class_name => $db_class)
		{
			foreach ($db_class->listOfSearchFields as $key => $field)
			{
				foreach ($this->fillFields as $field_name => $data)
				{
					if ($field == $field_name) //AND ($data != '')
					{
						$db_class->fillFields[$field] = $data;
					}
				}
			}
		}
	}
	
}

?>