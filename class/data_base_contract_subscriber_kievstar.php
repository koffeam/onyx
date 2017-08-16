<?php

class Data_Base_Contract_Subscriber_Kievstar extends Data_Base_Main
{
	
	public $listOfSearchFields = array (
		'telephone',
		'lastname',
		'firstname',
		'middlename',
		'company',
		'adress',
		'edrpou',
		'drfo_code'
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
				$this->result = $this->preAssemble($this->result);
			}
 		}
	}
	
	public function pregMatchFields()
	{
		$this->fillFields['telephone']	= $this->pregMatchDigits($this->fillFields['telephone']);
		$this->fillFields['lastname']	= $this->pregMatchOneWord($this->fillFields['lastname']);
		$this->fillFields['firstname']	= $this->pregMatchOneWord($this->fillFields['firstname']);
		$this->fillFields['middlename']	= $this->pregMatchOneWord($this->fillFields['middlename']);
		$this->fillFields['company']	= $this->pregMatchFewWords($this->fillFields['company']);
		$this->fillFields['adress']		= $this->pregMatchAdress($this->fillFields['adress']);
		$this->fillFields['edrpou']		= $this->pregMatchDigits($this->fillFields['edrpou']);
		$this->fillFields['drfo_code']	= $this->pregMatchDigits($this->fillFields['drfo_code']);
	}
	
	protected function prepareWhere()
	{
		$where = '';
		if ((isset($this->fillFields['lastname'])) OR (isset($this->fillFields['firstname'])) OR (isset($this->fillFields['middlename'])))
		{
			$where .= " AND MATCH (`kontrakt_abonenty_kievstar`.`subscriber_company`) AGAINST ('";
				if (isset($this->fillFields['lastname']))
				{
					$where .= "+" . $this->fillFields['lastname'] . " ";
				}
				if (isset($this->fillFields['firstname']))
				{
					$where .= "+" . $this->fillFields['firstname'] . " ";
				}
				if (isset($this->fillFields['middlename']))
				{
					$where .= "+" . $this->fillFields['middlename'] . " ";
				}
			$where .= "' IN BOOLEAN MODE)";
		}
		if (isset($this->fillFields['company']))
		{
			$where .= " AND MATCH (`kontrakt_abonenty_kievstar`.`subscriber_company`) AGAINST ('";
			foreach ($this->fillFields['company'] as $v)
			{
				$v = $this->replaceFullTextSearchMask($v);
				$where .= "+" . $v . " ";
			}
			$where .= "' IN BOOLEAN MODE)";
		}
		if (isset($this->fillFields['telephone']))
		{
			$where .= " AND (
					(`kontrakt_abonenty_kievstar`.`telephone` LIKE '" . $this->fillFields['telephone'] . "%')
				OR  (`kontrakt_abonenty_kievstar`.`additional_telephone` LIKE '" . $this->fillFields['telephone'] . "%')
				)";
		}
		if (isset($this->fillFields['edrpou']))
		{
			$where .= " AND `kontrakt_abonenty_kievstar`.`drfo_edrpou_code` LIKE '" . $this->fillFields['edrpou'] . "%'";
		}
		if (isset($this->fillFields['drfo_code']))
		{
			$where .= " AND `kontrakt_abonenty_kievstar`.`drfo_edrpou_code` LIKE '" . $this->fillFields['drfo_code'] . "%'";
		}
		//Big Fucking Adress!!!
		if (isset($this->fillFields['adress']['word']))
		{
			$where .= " AND MATCH(`kontrakt_abonenty_kievstar`.`adress`) AGAINST ('";
			foreach ($this->fillFields['adress']['word'] as $v)
			{
				$v = $this->replaceFullTextSearchMask($v);
				$where .= "+" . $v . " ";
			}
			$where .= "' IN BOOLEAN MODE)";
		}
		if (isset($this->fillFields['adress']['house_digit']))
		{
			$where .= " AND `kontrakt_abonenty_kievstar`.`adress` LIKE '%" . $this->fillFields['adress']['house_digit'] . "%'";
		}
		if (isset($this->fillFields['adress']['house_digit_letter']))
		{
			$where .= " AND `kontrakt_abonenty_kievstar`.`adress` LIKE '%"
						. $this->fillFields['adress']['house_digit_letter']['digit'] 
						. "%"
						. $this->fillFields['adress']['house_digit_letter']['digit']
						. "%'";
		}
		if (isset($this->fillFields['adress']['house_digit_split']))
		{
			$where .= " AND `kontrakt_abonenty_kievstar`.`adress` LIKE '%"
						. $this->fillFields['adress']['house_digit_split'][0]
						. "/"
						. $this->fillFields['adress']['house_digit_split'][1]
						. "%'";
		}
		if (isset($this->fillFields['adress']['flat']))
		{
			$where .= " AND `kontrakt_abonenty_kievstar`.`adress` LIKE '%" . $this->fillFields['adress']['flat'] . "%'";
		}
		$this->where = $where;
	}

	protected function sql($where)
	{
		$sql = "
			SELECT `kontrakt_abonenty_kievstar`.`telephone`				AS `telephone`
				 , `kontrakt_abonenty_kievstar`.`registration_date`		AS `registration_date_telephone`
				 , `kontrakt_abonenty_kievstar`.`adress`				AS `adress`
				 , `kontrakt_abonenty_kievstar`.`additional_telephone`	AS `additional_telephone`
				 , `kontrakt_abonenty_kievstar`.`subscriber_company`	AS `subscriber_company`
				 , `kontrakt_abonenty_kievstar`.`drfo_edrpou_code`		AS `drfo_edrpou_code`
				FROM kontrakt_abonenty_kievstar
				WHERE TRUE" . $where;
		return $sql;
	}
	
	protected function preAssemble($result)
	{
		//Обработка полей subscriber_company и drfo_edrpou_code в которых инфа по 2м позициям
		foreach ($result as $line_number => $line)
		{
			$length = strlen($line['drfo_edrpou_code']);
			if ($length == 10) // 10 цифр это ДРФО
			{
				$result[$line_number]['drfo'] = $line['drfo_edrpou_code'];
				$result[$line_number]['lastname'] = $line['subscriber_company'];
			}
			else if ($length == 0) // непонятно кто это
			{
				$result[$line_number]['lastname'] = $line['subscriber_company'];
				$result[$line_number]['company'] = $line['subscriber_company'];
			}
			else // если цифр не 10 и не 0 это ЕДРПОУ
			{
				$result[$line_number]['edrpou'] = $line['drfo_edrpou_code'];
				$result[$line_number]['company'] = $line['subscriber_company'];

			}
			unset($result[$line_number]['drfo_edrpou_code']);
			unset($result[$line_number]['subscriber_company']);	
		}
		
		return $result;
	}
	
	
	
	
	
	
}

?>