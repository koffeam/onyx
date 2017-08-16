<?php

class Data_Base_Drfo_2007 extends Data_Base_Main
{

	public $listOfSearchFields = array (
		'lastname',
		'firstname',
		'middlename',
		'birthdate',
		'birthplace',
		'adress',
		'telephone',
		'drfo_code',
		'company',
		'edrpou'
	);
	
	private $listOfFieldsNames = array (
		'adress'					=> '',
		'telephone'					=> 'тел.: ',
		'code_registration_date'	=> 'дата реєстр. коду: ',
		'edrpou'					=> 'ЄДРПОУ: ',
		'employer_drfo'				=> 'ДРФО роботодавця: ',
		'year_of_employment'		=> 'Рік працевлаштув.:',
		'date_of_dismissal'			=> 'Рік звільнення:',
		'company'					=> ''
	);
	
	private $listOfDates = array (
		'code_registration_date',
		'registration_date',
		'date_acquired_code',
		'date_acquired_code_2i',
		'change_date',
		'liquidation_date'
	);

	//private $pre_result = array ();
	
	protected $state = array();
	
	public function manage()
	{
		if ($this->checkFieldsAreNotEmpty() == true)
		{
			//$this->pregMatchFields();
			$no_errors = $this->check_errors->CheckNoErrors();
			if ($no_errors == true)
			{
				$this->prepareWhere();
				$this->statesAndSql();
				$this->preAssemble();
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
		$this->fillFields['telephone']		= $this->pregMatchDigits($this->fillFields['telephone']);
		$this->fillFields['drfo_code']		= $this->pregMatchDrfoCode($this->fillFields['drfo_code']);
		$this->fillFields['company']		= $this->pregMatchFewWords($this->fillFields['company']);
		$this->fillFields['edrpou']			= $this->pregMatchDigits($this->fillFields['edrpou']);
	}
	
	protected function prepareWhere()
	{
		$robota = false;
		$adresa = false;
		$osoba  = false;
		$where_robota = '';
		$where_osoba  = '';
		$where_adresa = '';
		if (isset($this->fillFields['company']))
		{
			$robota = true;
			$where_robota .= " AND MATCH(`drfo_2007_misce_roboty`.`work_place`) AGAINST ('";
			foreach ($this->fillFields['company'] as $v)
			{
				$v = $this->replaceFullTextSearchMask($v);
				$where_robota .= "+" . $v . " ";
			}
			$where_robota .= "' IN BOOLEAN MODE) ";
		}
		if (isset($this->fillFields['edrpou']))
		{
			$robota = true;
			$where_robota .= " AND `drfo_2007_misce_roboty`.`edrpou_code` LIKE '" . $this->fillFields['edrpou'] . "'";
		}
		if (!empty($this->fillFields['birthplace'])) // Возможно надо так isset $this->fillFields['birthplace']['word']
		{
			$osoba  = true;
			$where_osoba .= " AND MATCH(`drfo_2007_osoba`.`birth_adress`) AGAINST ('";
			foreach ($this->fillFields['birthplace']['word'] as $v)
			{
				$v = $this->replaceFullTextSearchMask($v);
				$where_osoba .= "+" . $v . " ";
			}
			$where_osoba .= "' IN BOOLEAN MODE)";
		}
		if (isset($this->fillFields['drfo_code']))
		{
			$osoba  = true;
			$where_osoba .= " AND `drfo_2007_osoba`.`drfo_code` = '" . $this->fillFields['drfo_code'] . "'";
		}
		if (isset($this->fillFields['lastname']))
		{
			$osoba  = true;
			$where_osoba .= " AND (`drfo_2007_osoba`.`lastname`    LIKE '" . $this->fillFields['lastname'] . "'
							   OR  `drfo_2007_osoba`.`lastname_ru` LIKE '" . $this->fillFields['lastname'] . "')";
		}
		if (isset($this->fillFields['firstname']))
		{
			$osoba  = true;
			$where_osoba .= " AND (`drfo_2007_osoba`.`firstname`    LIKE '" . $this->fillFields['firstname'] . "'
							   OR  `drfo_2007_osoba`.`firstname_ru` LIKE '" . $this->fillFields['firstname'] . "')";
		}
		if (isset($this->fillFields['middlename']))
		{
			$osoba  = true;
			$where_osoba .= " AND (`drfo_2007_osoba`.`middlename`    LIKE '" . $this->fillFields['middlename'] . "'
							   OR  `drfo_2007_osoba`.`middlename_ru` LIKE '" . $this->fillFields['middlename'] . "')";			
		}
		if (isset($this->fillFields['birthdate']))
		{
			$osoba  = true;
			$where_osoba .= " AND `drfo_2007_osoba`.`birthdate` LIKE '" . $this->fillFields['birthdate'] . "'";
		}
		if (!empty($this->fillFields['adress']))
		{
			$adresa = true;
			if (isset($this->fillFields['adress']['word']))
			{
				$where_adresa .= " AND MATCH(`drfo_2007_adresa`.`full_adress`) AGAINST ('";
				foreach ($this->fillFields['adress']['word'] as $v)
				{
					$v = $this->replaceFullTextSearchMask($v);
					$where_adresa .= "+" . $v . " ";
				}
				$where_adresa .= "' IN BOOLEAN MODE)";
			}
			if (isset($this->fillFields['adress']['house_digit']))
			{
				$where_adresa .= " AND `drfo_2007_adresa`.`house_number` LIKE '" . $this->fillFields['adress']['house_digit'] . "'
								   AND `drfo_2007_adresa`.`house_number_letter` = ''
								   AND `drfo_2007_adresa`.`building_house` = '' ";
			}
			if (isset($this->fillFields['adress']['house_digit_letter']))
			{
				$where_adresa .= " AND (
						(`drfo_2007_adresa`.`house_number` LIKE '" . $this->fillFields['adress']['house_digit_letter']['digit']
																   . $this->fillFields['adress']['house_digit_letter']['letter'] . "')
					OR  	(`drfo_2007_adresa`.`house_number`        LIKE '" . $this->fillFields['adress']['house_digit_letter']['digit']  . "' 
						AND  `drfo_2007_adresa`.`house_number_letter` LIKE '" . $this->fillFields['adress']['house_digit_letter']['letter'] . "')
					OR  	(`drfo_2007_adresa`.`house_number`   LIKE '" . $this->fillFields['adress']['house_digit_letter']['digit']  . "' 
						AND  `drfo_2007_adresa`.`building_house` LIKE '" . $this->fillFields['adress']['house_digit_letter']['letter'] . "')
				)";
			}
			if (isset($this->fillFields['adress']['house_digit_split']))
			{
				$where_adresa .= " AND `drfo_2007_adresa`.`house_number`        LIKE '" . $this->fillFields['adress']['house_digit_split'][0] . "'
								   AND `drfo_2007_adresa`.`house_number_letter` LIKE '" . $this->fillFields['adress']['house_digit_split'][1] . "' ";
			}
			if (isset($this->fillFields['adress']['flat']))
			{
				$where_adresa .= " AND `drfo_2007_adresa`.`flat` LIKE '" . $this->fillFields['adress']['flat'] . "'";
			}

			
		}
		if (isset($this->fillFields['telephone']))
		{
			$adresa = true;
			$where_adresa .= " AND `drfo_2007_adresa`.`telephone` LIKE '" . $this->fillFields['telephone'] . "'";
		}
		$this->where['adresa'] = $where_adresa;
		$this->where['osoba']  = $where_osoba;
		$this->where['robota'] = $where_robota;
		$this->state['adresa'] = $adresa;
		$this->state['osoba']  = $osoba;
		$this->state['robota'] = $robota;
	}
	
	protected function statesAndSql()
	{
		/*
		STATES:
		Rabota +-+-+-+
		Adresa -++--++
		Osoba  ---++++
		*/
		if (($this->state['robota'] == true)  AND ($this->state['adresa'] == false) AND ($this->state['osoba'] == false))
		{
			$sql = $this->robotaSql($this->where['robota'], null);
			$this->pre_result['robota'] = $this->db->query($sql);
			$drfo = $this->setDrfoData($this->pre_result['robota']);
			if (!empty($drfo))
			{
				$sql = $this->adresaSql($this->where['adresa'], $drfo);
				$this->pre_result['adresa'] = $this->db->query($sql);
				$sql = $this->osobaSql($this->where['osoba'], $drfo);
				$this->pre_result['osoba'] = $this->db->query($sql);
			}
		}
		if (($this->state['robota'] == false) AND ($this->state['adresa'] == true)  AND ($this->state['osoba'] == false))
		{
			$sql = $this->adresaSql($this->where['adresa'], null);
			$this->pre_result['adresa'] = $this->db->query($sql);
			$drfo = $this->setDrfoData($this->pre_result['adresa']);
			if (!empty($drfo))
			{
				$sql = $this->osobaSql($this->where['osoba'], $drfo);
				$this->pre_result['osoba'] = $this->db->query($sql);
				$sql = $this->robotaSql($this->where['robota'], $drfo);
				$this->pre_result['robota'] = $this->db->query($sql);
			}
		}
		if (($this->state['robota'] == true)  AND ($this->state['adresa'] == true)  AND ($this->state['osoba'] == false))
		{
			$sql = $this->robotaSql($this->where['robota'], null);
			$this->pre_result['robota'] = $this->db->query($sql);
			$drfo = $this->setDrfoData($this->pre_result['robota']);
			if (!empty($drfo))
			{
				$sql = $this->adresaSql($this->where['adresa'], $drfo);
				$this->pre_result['adresa'] = $this->db->query($sql);
				$drfo = $this->setDrfoData($this->pre_result['adresa']);
				if (!empty($drfo))
				{				
					$sql = $this->osobaSql($this->where['osoba'], $drfo);
					$this->pre_result['osoba'] = $this->db->query($sql);
				}
			}
		}
		if (($this->state['robota'] == false) AND ($this->state['adresa'] == false) AND ($this->state['osoba'] == true))
		{
			$sql = $this->osobaSql($this->where['osoba'], null);
			$this->pre_result['osoba'] = $this->db->query($sql);
			$drfo = $this->setDrfoData($this->pre_result['osoba']);
			if (!empty($drfo))
			{				
				$sql = $this->adresaSql($this->where['adresa'], $drfo);
				$this->pre_result['adresa'] = $this->db->query($sql);
				$sql = $this->robotaSql($this->where['robota'], $drfo);
				$this->pre_result['robota'] = $this->db->query($sql);
			}
		}
		if (($this->state['robota'] == true) AND ($this->state['adresa'] == false) AND ($this->state['osoba'] == true))
		{
			$sql = $this->robotaSql($this->where['robota'], null);
			$this->pre_result['robota'] = $this->db->query($sql);
			$drfo = $this->setDrfoData($this->pre_result['robota']);
			if (!empty($drfo))
			{
				$sql = $this->osobaSql($this->where['osoba'], $drfo);
				$this->pre_result['osoba'] = $this->db->query($sql);
				$drfo = $this->setDrfoData($this->pre_result['osoba']);
				if (!empty($drfo))
				{				
					$sql = $this->adresaSql($this->where['adresa'], $drfo);
					$this->pre_result['adresa'] = $this->db->query($sql);
				}
			}
		}
		if (($this->state['robota'] == false) AND ($this->state['adresa'] == true) AND ($this->state['osoba'] == true))
		{
			$sql = $this->adresaSql($this->where['adresa'], null);
			$this->pre_result['adresa'] = $this->db->query($sql);
			$drfo = $this->setDrfoData($this->pre_result['adresa']);
			if (!empty($drfo))
			{				
				$sql = $this->osobaSql($this->where['osoba'], $drfo);
				$this->pre_result['osoba'] = $this->db->query($sql);
				$drfo = $this->setDrfoData($this->pre_result['osoba']);
				if (!empty($drfo))
				{					
					$sql = $this->robotaSql($this->where['robota'], $drfo);
					$this->pre_result['robota'] = $this->db->query($sql);
				}
			}
		}
		if (($this->state['robota'] == true) AND ($this->state['adresa'] == true) AND ($this->state['osoba'] == true))
		{
			$sql = $this->osobaSql($this->where['osoba'], null);
			$this->pre_result['osoba'] = $this->db->query($sql);
			$drfo = $this->setDrfoData($this->pre_result['osoba']);
			if (!empty($drfo))
			{				
				$sql = $this->adresaSql($this->where['adresa'], $drfo);
				$this->pre_result['adresa'] = $this->db->query($sql);
				$drfo = $this->setDrfoData($this->pre_result['adresa']);
				if (!empty($drfo))
				{					
					$sql = $this->robotaSql($this->where['robota'], $drfo);
					$this->pre_result['robota'] = $this->db->query($sql);
				}
			}
		}
	}
	
	protected function setDrfoData($sql_result)
	{
		$res = array();
		foreach ($sql_result as $line => $data)
		{
			$res[$data['drfo']] = $data['drfo'];
		}
		return $res;
	}
	
	protected function getDrfoData($table_name, $drfo)
	{
		if (!empty($drfo))
		{
			$where_drfo = " AND `" . $table_name . "`.`drfo_code` IN (";
			$first = true;
			foreach ($drfo as $v)
			{
				if ($first == true)
				{
					$comma = '';
					$first = false;
				}
				else
				{
					$comma = ',';
				}
				
				$where_drfo .= $comma . $v;
			}
			$where_drfo .= ") ";
		}
		else
		{
			$where_drfo = '';
		}
		return $where_drfo;
	}
	
	protected function adresaSql($where, $drfo)
	{	
		$where_drfo = $this->getDrfoData('drfo_2007_adresa', $drfo);
		$sql = "
			SELECT
				CONCAT_WS(' ' 
					, ifnull(`drfo_2007_adresa`.`full_adress`, '')
					, ifnull(`drfo_2007_adresa`.`house_number`, '')
					, ifnull(`drfo_2007_adresa`.`house_number_letter`, '')
					, ifnull(`drfo_2007_adresa`.`building_house`, '')
					, ifnull(`drfo_2007_adresa`.`flat`, '')
				)	 												AS `adress`
				, `drfo_2007_adresa`.`telephone` 					AS `telephone`
				, `drfo_2007_adresa`.`code_registration_date`		AS `code_registration_date`
				, `drfo_2007_adresa`.`drfo_code`					AS `drfo` 
				FROM drfo_2007_adresa
				WHERE TRUE " . $where_drfo . $where;
		return $sql;
	}
	
	protected function osobaSql($where, $drfo)
	{
		$where_drfo = $this->getDrfoData('drfo_2007_osoba', $drfo);
		$sql = "
			SELECT	  `drfo_2007_osoba`.`drfo_code` 				AS `drfo`
					, `drfo_2007_osoba`.`lastname` 					AS `lastname`
					, `drfo_2007_osoba`.`lastname_ru` 				AS `lastname_ru`
					, `drfo_2007_osoba`.`firstname` 				AS `firstname`
					, `drfo_2007_osoba`.`firstname_ru` 				AS `firstname_ru`
					, `drfo_2007_osoba`.`middlename` 				AS `middlename`
					, `drfo_2007_osoba`.`middlename_ru` 			AS `middlename_ru`
					, `drfo_2007_osoba`.`date_acquired_code` 		AS `date_acquired_code`
					, `drfo_2007_osoba`.`birthdate` 				AS `birthdate`
					, `drfo_2007_osoba`.`birth_adress` 				AS `birth_adress`
					, `drfo_2007_osoba`.`birth_place_name_abroad` 	AS `birth_place_name_abroad`
					, `drfo_2007_osoba`.`registration_date` 		AS `registration_date`
					, `drfo_2007_osoba`.`drfo_code_2` 				AS `drfo_2`
					, `drfo_2007_osoba`.`language` 					AS `language`
					, `drfo_2007_osoba`.`stat` 						AS `stat`
					, `drfo_2007_osoba`.`date_acquired_code_2i` 	AS `date_acquired_code_2i`
					, `drfo_2007_osoba`.`change_date` 				AS `change_date`
					, `drfo_2007_osoba`.`liquidation_date` 			AS `liquidation_date`
					, `drfo_2007_osoba`.`organ_dpi` 				AS `organ_dpi`
					FROM drfo_2007_osoba
					WHERE TRUE " . $where_drfo . $where;
		return $sql;
	}
	
	protected function robotaSql($where, $drfo)
	{
		$where_drfo = $this->getDrfoData('drfo_2007_misce_roboty', $drfo);
		$sql = "
			SELECT    `drfo_2007_misce_roboty`.`edrpou_code`				AS `edrpou`
					, `drfo_2007_misce_roboty`.`employer_drfo_code`			AS `employer_drfo`
					, `drfo_2007_misce_roboty`.`year_of_employment`			AS `year_of_employment`
					, `drfo_2007_misce_roboty`.`date_of_dismissal`			AS `date_of_dismissal`
					, `drfo_2007_misce_roboty`.`drfo_code`					AS `drfo`
					, CONCAT(
						  ifnull(`drfo_2007_misce_roboty`.`work_place`,'')
						, ifnull(`drfo_2007_misce_roboty`.`work_place_2`,'')
					)														AS `company`
					FROM drfo_2007_misce_roboty
					WHERE TRUE " . $where_drfo . $where;
		return $sql;
	}

	protected function preAssemble()
	{
		//обработка дат, кроме ДР
		foreach ($this->pre_result as $table => $data)
		{
			foreach ($data as $line => $data2)
			{
				foreach ($data2 as $field_name => $field_data)
				{
					if (in_array($field_name, $this->listOfDates))
					{
						$this->pre_result[$table][$line][$field_name] = substr($field_data, 6, 2) . "."
																	  . substr($field_data, 4, 2) . "."
																	  . substr($field_data, 0, 4);
					}
				}
			}
		}
		//подготовка окончательного результата
		$drfo = array();
		foreach ($this->pre_result['osoba'] as $line => $data)
		{
			foreach ($data as $field_name => $field_data)
			{
				$this->result[ $data['drfo'] ][$field_name] = $field_data;
				$drfo[ $data['drfo'] ] = $data['drfo'];
			}
		}
		if (isset($this->pre_result['adresa']))
		{
			foreach ($this->pre_result['adresa'] as $line => $data)
			{
				$adress_first = true;
				foreach ($data as $field_name => $field_data)
				{
					if (in_array($data['drfo'], $drfo))
					{
						if (!empty($data['adress']))
						{
							if ($adress_first == true)
							{
								if (isset($this->result[ $data['drfo'] ]['adress']))
								{
									$this->result[ $data['drfo'] ]['adress'] .= $data['adress'] . " (";
								}
								else
								{
									$this->result[ $data['drfo'] ]['adress'] = $data['adress'] . " (";
								}
								$adress_first = false;
							}
							if ((!empty($field_data)) AND ($field_name != 'drfo') AND ($field_name != 'adress')) 
							{
								$this->result[ $data['drfo'] ]['adress'] .= $this->listOfFieldsNames[$field_name] . $field_data . ", ";
							}
						}
					}
				}
				if ($adress_first == false)
				{
					$this->result[ $data['drfo'] ]['adress'] .= ") ";			
				}
			}
		}
		if (isset($this->pre_result['robota']))
		{
			foreach ($this->pre_result['robota'] as $line => $data)
			{
				$robota_first = true;
				foreach ($data as $field_name => $field_data)
				{
					if (in_array($data['drfo'], $drfo))
					{
						if (!empty($data['company']))
						{
							if ($robota_first == true)
							{
								if (isset($this->result[ $data['drfo'] ]['company']))
								{
									$this->result[ $data['drfo'] ]['company'] .= $data['company'] . " (";
								}
								else
								{
									$this->result[ $data['drfo'] ]['company'] = $data['company'] . " (";
								}
								
								$robota_first = false;
							}
							if ((!empty($field_data)) AND ($field_name != 'drfo') AND ($field_name != 'company'))
							{
								$this->result[ $data['drfo'] ]['company'] .= $this->listOfFieldsNames[$field_name] . $field_data . ", ";
							}
						}
					}
				}
				if ($robota_first == false)
				{
					$this->result[ $data['drfo'] ]['company'] .= ") ";
				}
			}
		}
	}

	
}

?>