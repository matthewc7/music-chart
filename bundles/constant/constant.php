<?php

class Constant {

	/**
	 * Constant year array. 
	 * @param  integer $range [range of years.]
	 * @param  integer $extra [extra of years from current years.]
	 * @return array.
	 */
	public static function year($range = 3, $extra = 0)
	{
		$default = date('Y') - ($range - 1);	
		$size = $default + $range + $extra;
		
		for($year = $default; $year < $size; $year++)
		{
			$array[$year] = $year;
		}
		
		return $array;
	}


	/**
	 * Constant month array with integer month.
	 * @return array.
	 */
	public static function month()
	{
		for($month = 1; $month <= 12; $month++)
		{
			$monthName = date("F", mktime(0, 0, 0, $month, 10));
			$array[$month] = substr($monthName, 0, 3);
		}

		return $array;
	}


	/**
	 * Constant month array with alphabetic month.
	 * @return array.
	 */
	public static function month_alpha()
	{
		for($month = 1; $month <= 12; $month++)
		{
			$monthName = substr(date("F", mktime(0, 0, 0, $month, 10)), 0, 3);
			$array[$monthName] = $monthName;
		}

		return $array;
	}


	/**
	 * Constant day array.
	 * @return array.
	 */
	public static function day()
	{
		for($day = 1; $day <= 31; $day++)
		{
			$array[$day] = $day;
		}

		return $array;
	}





}