<?php

class Data_Base_Vybory_2014_Ukrayina extends Data_Base_Main
{
	
	public $listOfSearchFields = array (
		'lastname',
		'firstname',
		'middlename',
		'birthdate',
		'birthplace',
		'adress'
	);
	
	protected $listOfSearchFieldsNames = array (
		'lastname'		=> 'vybory_2014_ukrayina.lastname',
		'firstname'		=> 'vybory_2014_ukrayina.firstname',
		'middlename'	=> 'vybory_2014_ukrayina.fathername',
		'birthdate'		=> 'vybory_2014_ukrayina.birthday_date',
		'birthplace'	=> 'vybory_2014_ukrayina.birthday_place',
		'adress'		=> 'vybory_2014_ukrayina.address'		
	);

	protected $listOfSimpleSearchFields = array (
		'lastname',
		'firstname',
		'middlename',
		'birthdate',	
	);
	
	public function manage()
	{
		if ($this->checkFieldsAreNotEmpty() == true)
		{
			//$this->pregMatchFields();
			$no_errors = $this->check_errors->CheckNoErrors();
			if ($no_errors == true)
			{
				$this->prepareWhere();
				$sql = $this->sql($this->where);
				$this->result = $this->db->query($sql);
			}
		}
	}
	
	public function pregMatchFields()
	{
		$this->fillFields['lastname']		= $this->pregMatchOneWord($this->fillFields['lastname']);
		$this->fillFields['firstname']		= $this->pregMatchOneWord($this->fillFields['firstname']);
		$this->fillFields['middlename']		= $this->pregMatchOneWord($this->fillFields['middlename']);
		$this->fillFields['birthdate']		= $this->pregMatchDate($this->fillFields['birthdate']);
		$this->fillFields['birthplace']		= $this->pregMatchAdress($this->fillFields['birthplace']);
		$this->fillFields['adress']			= $this->pregMatchAdress($this->fillFields['adress']);		
	}
	
	protected function prepareWhere()
	{
		$where = ' ';
		foreach ($this->fillFields as $name => $value)
		{
			if ((in_array($name, $this->listOfSimpleSearchFields)) AND ($value != ''))
			{
				$where .= " AND " . $this->listOfSearchFieldsNames[$name] . " LIKE '" .$value ."%'";
			}
		}
		if (isset($this->fillFields['birthplace']['word']))
		{
			$where .= " AND MATCH(`vybory_2014_ukrayina`.`birthday_place`) AGAINST ('";
			foreach ($this->fillFields['birthplace']['word'] as $v)
			{
				$v = $this->replaceFullTextSearchMask($v);
				$where .= "+" . $v . " ";
			}
			$where .= "' IN BOOLEAN MODE)";
		}
		if (isset($this->fillFields['adress']['word']))
		{
			$where .= " AND MATCH(`vybory_2014_ukrayina`.`address`) AGAINST ('";
			foreach ($this->fillFields['adress']['word'] as $v)
			{
				$v = $this->replaceFullTextSearchMask($v);
				$where .= "+" . $v . " ";
			}
			$where .= "' IN BOOLEAN MODE)";						
		}
		if (isset($this->fillFields['adress']['house_digit']))
		{
			$where .= " AND `vybory_2014_ukrayina`.`address` LIKE '%" . $this->fillFields['adress']['house_digit'] . "%'";
		}
		if (isset($this->fillFields['adress']['house_digit_letter']))
		{
			$where .= " AND `vybory_2014_ukrayina`.`address` LIKE '%"
						. $this->fillFields['adress']['house_digit_letter']['digit']
						. $this->fillFields['adress']['house_digit_letter']['letter']
						. "%'";
		}
		if (isset($this->fillFields['adress']['house_digit_split']))
		{
			$where .= " AND `vybory_2014_ukrayina`.`address` LIKE '%"
						. $this->fillFields['adress']['house_digit_split'][0]
						. $this->fillFields['adress']['house_digit_split'][1]
						. "%'";
		}
		if (isset($this->fillFields['adress']['flat']))
		{
			$where .= " AND `vybory_2014_ukrayina`.`address` LIKE '%" . $this->fillFields['adress']['flat'] . "'";
		}				
		$this->where = $where;
	}
	
	protected function sql($where)
	{
		$sql = "
			SELECT UPPER(`vybory_2014_ukrayina`.`lastname`)		AS `lastname`
				 , UPPER(`vybory_2014_ukrayina`.`firstname`)	AS `firstname`
				 , UPPER(`vybory_2014_ukrayina`.`fathername`)	AS `middlename`
				 , `vybory_2014_ukrayina`.`birthday_date`		AS `birthdate`
				 , `vybory_2014_ukrayina`.`birthday_place`		AS `birth_adress`
				 , `vybory_2014_ukrayina`.`address`				AS `adress`
				FROM vybory_2014_ukrayina
				WHERE TRUE
				" . $where;
		return $sql;
	}
	
}

?>