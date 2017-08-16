<?php

class Data_Base_Telefony_Kiev_Kievoblast_2007 extends Data_Base_Main
{
	
	public $listOfSearchFields = array (
			'lastname',
			'firstname',
			'middlename',
			'telephone',
			'company',
			'adress'
		);

	
	protected $listOfSearchFieldsNames = array (
			'lastname'		=> array (
								'kiev'			=> 'telephones_2007_kiev.subscriber',
								'kievoblast'	=> 'telephones_2007_kievoblast.subscriber'
							),
			'firstname'		=> array (
								'kiev'			=> 'telephones_2007_kiev.subscriber',
								'kievoblast'	=> 'telephones_2007_kievoblast.subscriber'
							),
			'middlename'	=> array (
								'kiev'			=> 'telephones_2007_kiev.subscriber',
								'kievoblast' 	=> 'telephones_2007_kievoblast.subscriber'
							),
			'telephone'		=> array (
								'kiev'			=> 'telephones_2007_kiev.telephone',
								'kievoblast'	=> 'telephones_2007_kievoblast.telephone'
							),
			'company'		=> array (
								'kiev'			=> 'telephones_2007_kiev.company',
								'kievoblast'	=> 'telephones_2007_kievoblast.company'
							),
			'adress'		=> array (
								'kiev'			=> 'telephones_2007_kiev.',
								'kievoblast'	=> 'telephones_2007_kievoblast.'
							)	
	);
	
	private $listOfDates = array (
		'installation_date',
		'correction_date'
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
				$sql = $this->sql($this->where['kiev'], $this->where['kievoblast']);
				$this->pre_result = $this->db->query($sql);
				$this->preAssemble();
			}
		}
	}
	
	public function pregMatchFields()
	{
		$this->fillFields['lastname']	= $this->pregMatchOneWord($this->fillFields['lastname']);
		$this->fillFields['firstname']	= $this->pregMatchOneWord($this->fillFields['firstname']);
		$this->fillFields['middlename']	= $this->pregMatchOneWord($this->fillFields['middlename']);
		$this->fillFields['telephone']	= $this->pregMatchDigits($this->fillFields['telephone']);
		$this->fillFields['company']	= $this->pregMatchFewWords($this->fillFields['company']);
		$this->fillFields['adress']		= $this->pregMatchAdress($this->fillFields['adress']);
	}
	
	protected function prepareWhere()
	{
		$where_kiev 		= ' ';
		$where_kievoblast	= ' ';
		if ((isset($this->fillFields['lastname'])) OR (isset($this->fillFields['firstname'])) OR (isset($this->fillFields['middlename']))) {
			$subscriber = '';
			if (isset($this->fillFields['lastname']))
			{
				$subscriber .= $this->fillFields['lastname'] . ' ';
			}
			else
			{
				$subscriber .= '%';
			}
			if (isset($this->fillFields['firstname']))
			{
				$subscriber .= mb_substr($this->fillFields['firstname'], 0, 1);
			}
			else
			{
				$subscriber .= '_';
			}
			if (isset($this->fillFields['middlename']))
			{
				$subscriber .= mb_substr($this->fillFields['middlename'], 0, 1);
			}
			else
			{
				$subscriber .= '_';
			}
			$where_kiev 		.= " AND " . $this->listOfSearchFieldsNames['lastname']['kiev'] . " LIKE '" . $subscriber . "'";
			$where_kievoblast	.= " AND " . $this->listOfSearchFieldsNames['lastname']['kievoblast'] . " LIKE '" . $subscriber . "'";
		}
		if (isset($this->fillFields['telephone']))
		{
			$where_kiev			.= " AND " . $this->listOfSearchFieldsNames['telephone']['kiev'] . " LIKE '" . $this->fillFields['telephone'] . "'"; 
			$where_kievoblast	.= " AND " . $this->listOfSearchFieldsNames['telephone']['kievoblast'] . " LIKE '" . $this->fillFields['telephone'] . "'"; 
		}
		if (isset($this->fillFields['company']))
		{
			$where_kiev       .= " AND MATCH (`telephones_2007_kiev`.`company`) AGAINST ('";
			$where_kievoblast .= " AND MATCH (`telephones_2007_kievoblast`.`company`) AGAINST ('";
			foreach ($this->fillFields['company'] as $v)
			{
				$v = $this->replaceFullTextSearchMask($v);
				$where_kiev       .= "+" . $v . " ";
				$where_kievoblast .= "+" . $v . " ";
			}
			$where_kiev		  .= "' IN BOOLEAN MODE)";
			$where_kievoblast .= "' IN BOOLEAN MODE)";
		}
		/*BIG FUCKING Адресс*/
		if (isset($this->fillFields['adress']))
		{
			if (isset($this->fillFields['adress']['word']))
			{
				$where_kiev       .= " AND MATCH (`telephones_2007_kiev`.`street`) AGAINST ('";
				$where_kievoblast .= " AND MATCH (`telephones_2007_kievoblast`.`street`) AGAINST ('";
				foreach ($this->fillFields['adress']['word'] as $v)
				{
					$v = $this->replaceFullTextSearchMask($v);
					$where_kiev       .= "+" . $v . " ";
					$where_kievoblast .= "+" . $v . " ";
				}
				$where_kiev		  .= "' IN BOOLEAN MODE)";
				$where_kievoblast .= "' IN BOOLEAN MODE)";
			}
			if (isset($this->fillFields['adress']['house_digit']))
			{
				$where_kiev       .= " AND `telephones_2007_kiev`.`house` = '" . $this->fillFields['adress']['house_digit'] . "'";
				$where_kievoblast .= " AND `telephones_2007_kievoblast`.`house` = '" . $this->fillFields['adress']['house_digit'] . "'";
			}
			if (isset($this->fillFields['adress']['house_digit_letter']))
			{
				$where_kiev       .= " AND `telephones_2007_kiev`.`house` = '"
										. $this->fillFields['adress']['house_digit_letter']['digit']
										. $this->fillFields['adress']['house_digit_letter']['letter']
									    . "'";
				$where_kievoblast .= " AND `telephones_2007_kievoblast`.`house` = '"
										. $this->fillFields['adress']['house_digit_letter']['digit']
										. $this->fillFields['adress']['house_digit_letter']['letter']
									    . "'";
			}
			if (isset($this->fillFields['adress']['house_digit_split']))
			{
				$where_kiev		  .= " AND `telephones_2007_kiev`.`house` = '"
										. $this->fillFields['adress']['house_digit_split'][0]
										. "/"
										. $this->fillFields['adress']['house_digit_split'][1]
										. "'";
				$where_kievoblast .= " AND `telephones_2007_kievoblast`.`house` = '"
										. $this->fillFields['adress']['house_digit_split'][0]
										. "/"
										. $this->fillFields['adress']['house_digit_split'][1]
										. "'";
			}	
			if (isset($this->fillFields['adress']['flat']))
			{
				$where_kiev		  .= " AND `telephones_2007_kiev`.`flat` = '" . $this->fillFields['adress']['flat'] . "'";
				$where_kievoblast .= " AND `telephones_2007_kievoblast`.`flat` = '" . $this->fillFields['adress']['flat'] . "'";
			}
		}
		$this->where['kiev']		= $where_kiev;
		$this->where['kievoblast']	= $where_kievoblast;
	}
	
	protected function sql($where_kiev, $where_kievoblast)
	{
		$sql = "
		SELECT   `telephones_2007_kiev`.`telephone`				AS `telephone`
			   , `telephones_2007_kiev`.`subscriber`			AS `lastname`
			   , `telephones_2007_kiev`.`company`				AS `company`
			   , `telephones_2007_kiev`.`installation_date`		AS `installation_date`
			   , `telephones_2007_kiev`.`accountant_telephone`	AS `accountant_telephone`
			   , `telephones_2007_kiev`.`okpo`					AS `okpo`
			   , `telephones_2007_kiev`.`correction_date`		AS `correction_date`
			   , CONCAT_WS(' '
					, ifnull(`telephones_2007_kiev`.`street`, '')
					, ifnull(`telephones_2007_kiev`.`house`, '')
					, ifnull(`telephones_2007_kiev`.`building_house`, '')
					, ifnull(`telephones_2007_kiev`.`flat`, '')
			   )												AS `adress`
			FROM telephones_2007_kiev
				WHERE TRUE" . $where_kiev . "

		UNION SELECT `telephones_2007_kievoblast`.`telephone`			AS `telephone`
			   , `telephones_2007_kievoblast`.`subscriber`				AS `lastname`
			   , `telephones_2007_kievoblast`.`company`					AS `company`
			   , `telephones_2007_kievoblast`.`installation_date`		AS `installation_date`
			   , `telephones_2007_kievoblast`.`accountant_telephone`	AS `accountant_telephone`
			   , `telephones_2007_kievoblast`.`okpo`					AS `okpo`
			   , `telephones_2007_kievoblast`.`correction_date`			AS `correction_date`
			   , CONCAT_WS(' '
					, ifnull(`telephones_2007_kievoblast`.`street`, '')
					, ifnull(`telephones_2007_kievoblast`.`house`, '')
					, ifnull(`telephones_2007_kievoblast`.`building_house`, '')
					, ifnull(`telephones_2007_kievoblast`.`flat`, '')
			   )														AS `adress`
			FROM telephones_2007_kievoblast
				WHERE TRUE" . $where_kievoblast;
		return $sql;
	}
	
	protected function preAssemble()
	{
		//обработка дат
		foreach ($this->pre_result as $line => $data)
		{
			foreach ($data as $field_name => $field_data)
			{
				if ((in_array($field_name, $this->listOfDates)) AND (!empty($field_data)))
				{
					$this->pre_result[$line][$field_name] = substr($field_data, 6, 2) . "."
														  . substr($field_data, 4, 2) . "."
														  . substr($field_data, 0, 4);
				}
			}
		}

		$result = array();
		
		foreach ($this->pre_result as $line => $data)
		{
			foreach ($data as $field_name => $field_data)
			{
				if (!empty($data['lastname']))
				{
					if ($field_name == 'telephone')
					{
						$result[ $data['lastname'] ]['telephone'][$field_data] = $data['installation_date'];
					}
					else if ($field_name == 'installation_date')
					{
						// do nothing
					}
					else
					{
						$result[ $data['lastname'] ][$field_name][$field_data] = $field_data;
					}
				}
				else if (!empty($data['company']))
				{
					if ($field_name == 'telephone')
					{
						$result[ $data['company'] ]['telephone'][$field_data] = $data['installation_date'];
					}
					else if ($field_name == 'installation_date')
					{
						// do nothing
					}
					else 
					{
						$result[ $data['company'] ][$field_name][$field_data] = $field_data;
					}
				}
			}
		}
		
		foreach ($result as $object => $data)
		{
			foreach ($data as $field_name => $data2)
			{
				foreach ($data2 as $field_data => $field_data_again)
				{
					if (isset($this->result[$object][$field_name]))
					{
						if ($field_name == 'telephone')
						{
							$this->result[$object][$field_name] .= ", " . $field_data . " (" . Config::$listOfResultFields['installation_date'] . " " . $field_data_again . ") ";
						}
						else
						{
							$this->result[$object][$field_name] .= ", " . $field_data;
						}
					}
					else
					{
						if ($field_name == 'telephone')
						{
							$this->result[$object][$field_name] = $field_data . " (" . Config::$listOfResultFields['installation_date'] . " " . $field_data_again . ") ";
						}
						else
						{
							$this->result[$object][$field_name] = $field_data;
						}
					}
				}
			}
		}
	}
	
	
}

?>