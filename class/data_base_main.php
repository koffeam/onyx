<?php

abstract class Data_Base_Main
{
	
	public 		$listOfSearchFields 	 	= array();
	protected	$listOfSearchFieldsNames 	= array();
	protected	$db;
	public 		$fillFields 				= array();
	protected	$where						= '';
	protected	$pre_result					= array();
	public		$result						= array();
	protected	$check_errors;
	
	
	public function __construct()
	{
		$this->db = DB::getDB();
		$this->check_errors = Checking_Errors::getCheckingErrors();
	}
		
	abstract public function manage();
	
	abstract protected function pregMatchFields();
	
	protected function checkFieldsAreNotEmpty()
	{
		$not_empty = false;
		foreach ($this->fillFields as $k => $v)
		{
			if (!empty($v))
			{
				$not_empty = true;
			}
		}
		return $not_empty;
	}
	
	protected function pregMatchOneWord($value)
	{
		$res = null;
		$pattern = "/^[A-Za-zА-Яа-яЁёІіЇїЄє%_-]+$/msiu";
		if (preg_match($pattern, $value, $match))
		{
			$res = $match[0];
		}
		if (($value != '') AND ($res == null))
		{
			$this->check_errors->addError('lastname', $value);
		}
		return $res;
	}
	
	protected function pregMatchDate($value)
	{
		$res = null;
		$pattern1 = "/^(0[1-9]|[12][0-9]|3[01])[-,\.](0[1-9]|1[012])[-,\.](19|20)\d\d$/";
		$pattern2 = "/^(19|20)\d\d$/";
		$pattern3 = "/^(19|20)\d\d(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])$/";
		if (preg_match($pattern1, $value, $match))
		{
			$res = substr($match[0], 6, 4) . substr($match[0], 3, 2) . substr($match[0], 0, 2);
		}
		else if (preg_match($pattern2, $value, $match))
		{
			$res = $match[0];
		}
		else if (preg_match($pattern3, $value, $match))
		{
			$res = $match[0];
		}
		if (($value != '') AND ($res == null))
		{
			$this->check_errors->addError('birthdate', $value);
		}
		return $res;
	}

	protected function pregMatchFewWords($value)
	{
		$res = null;
		$values = explode(" ", $value);
		$pattern = "/^[A-Za-zА-Яа-яЁёІіЇїЄє%_-]+$/msiu";
		foreach ($values as $k => $v)
		{
			if (preg_match($pattern, $v, $match))
			{
				$res[$k] = $match[0];
			}
		}
		if (($value != '') AND ($res == null))
		{
			$this->check_errors->addError('birthplace', $value);
		}
		return $res;
	}
	
	protected function pregMatchAdress($value)
	{
		$values = explode(" ", $value);
		$pattern_word				= "/^[0-9]*[A-Za-zА-Яа-яЁёІіЇїЄє%_-]{2,}$/msiu";
		$pattern_house_digit		= "/^буд.[0-9]+$/msiu";
		$pattern_house_digit_letter = "/^буд.[0-9]+[A-Za-zА-Яа-яЁёІіЇїЄє%_-]$/msiu";
		$pattern_house_digit_split	= "/^буд.[0-9]+[\/][0-9]+$/msiu";
		$pattern_flat				= "/^кв.[0-9]+$/msiu";
		$pattern_digit_letter		= "/^[0-9]+[A-Za-zА-Яа-яЁёІіЇїЄє%_-]$/msiu";
		$pattern_digit_split		= "/^[0-9]+[\/][0-9]+$/msiu";
		$pattern_digit_only			= "/^[0-9]+$/msiu";	
		$res = array();
		$house_found = false;
		$flat_found   = false;
		foreach ($values as $v)
		{
			if (preg_match($pattern_word, $v, $match))
			{
				$res['word'][] = $match[0];
			}
			
			if (preg_match($pattern_house_digit, $v, $match))
			{
				$res['house_digit'] = mb_substr($match[0], 4, (mb_strlen($match[0])-4));
				$house_found = true;
			}
			
			if (preg_match($pattern_house_digit_letter, $v, $match))
			{
				$result = mb_substr($match[0], 4, (mb_strlen($match[0])-4));
				$pattern_find_digit  = "/[0-9]+/";
				$pattern_find_letter = "/[A-Za-zА-Яа-яЁёІіЇїЄє%_-]+/msiu";
				if (preg_match($pattern_find_digit, $result, $match))
				{
					$res['house_digit_letter']['digit'] = $match[0];
				}
				if (preg_match($pattern_find_letter, $result, $match))
				{
					$res['house_digit_letter']['letter'] = $match[0];
				}
				$house_found = true;
			}
			if (preg_match($pattern_digit_letter, $v, $match))
			{
				$result = $match[0];
				$pattern_find_digit  = "/[0-9]+/";
				$pattern_find_letter = "/[A-Za-zА-Яа-яЁёІіЇїЄє%_-]+/msiu";
				if (preg_match($pattern_find_digit, $result, $match))
				{
					$res['house_digit_letter']['digit'] = $match[0];
				}
				if (preg_match($pattern_find_letter, $result, $match))
				{
					$res['house_digit_letter']['letter'] = $match[0];
				}
				$house_found = true;
			}			
			
			if (preg_match($pattern_house_digit_split, $v, $match))
			{
				$result = mb_substr($match[0], 4, (mb_strlen($match[0])-4));
				$res['house_digit_split'] = explode('/', $result);
				$house_found = true;
			}
			if (preg_match($pattern_digit_split, $v, $match))
			{
				$result = $match[0];
				$res['house_digit_split'] = explode('/', $result);
				$house_found = true;
			}			
			
			if (preg_match($pattern_flat, $v, $match))
			{
				$res['flat'] = mb_substr($match[0], 3, (mb_strlen($match[0])-3));
				$flat_found = true;
			}			
		}
		
		if (($house_found != true) AND ($flat_found != true))
		{
			$house_first = true;
			foreach ($values as $v)
			{
				if (preg_match($pattern_digit_only, $v, $match))
				{
					if (($house_found == true) AND ($flat_found == false))
					{
						$res['flat'] = $match[0];
					}
					else if (($house_found == false) AND ($flat_found == true))
					{
						$res['house_digit'] = $match[0];
					}
					else if (($house_found == false) AND ($flat_found == false))
					{
						if ($house_first == true)
						{
							$res['house_digit'] = $match[0];
							$house_first = false;
						}
						else
						{
							$res['flat'] = $match[0];
						}
					}
				}
			}
		}
		if ($value != '')
		{
			if ($res == null)
			{
				$this->check_errors->addError('adress', $value);
			}
			if ((count($res) < 2) OR (count($res['word']) < 2))
			{
				$this->check_errors->addError('lessParameters', $value);
			}
		}
		return $res;
	}
	
	protected function pregMatchDigits($value)
	{
		$res = null;
		$pattern = "/^[0-9%_-]+$/";
		if (preg_match($pattern, $value, $match))
		{
			$res = preg_replace("/-/", "", $match[0]);
		}
		if (($value != '') AND ($res == null))
		{
			$this->check_errors->addError('edrpou', $value);
		}
		return $res;
	}
	
	protected function pregMatchDrfoCode($value)
	{
		$res = null;
		$pattern1 = "/^[0-9]+$/";
		if (preg_match($pattern1, $value))
		{
			$pattern2 = "/^0[0-9]+$/";
			for ($i = 0; $i < strlen($value); $i++)
			{
				if (preg_match($pattern2, $value, $match))
				{
					$value = substr($match[0], 1);
				}
			}
			$res = $value;
		}
		if (($value != '') AND ($res == null))
		{
			$this->check_errors->addError('drfo_code', $value);
		}
		return $res;
	}
	
	protected function replaceFullTextSearchMask($value)
	{
		$value = preg_replace("/_/", "*", $value);
		$value = preg_replace("/%/", "*", $value);
		return $value;
	}
	
	
}

?>