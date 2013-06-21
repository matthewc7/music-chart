<?php

class Format {


	/**
	 * Format date from datetime into date only. 
	 * @param  strsing 	$value  [value]
	 * @param  string 	$format [date format]
	 * @return string
	 */
	public static function date($value, $format = 'Y-m-d')
	{
		return date_format(date_create($value), $format);
	}


	/**
	 * Replace characters in string. 
	 * @param  string 	$value 	[value]
	 * @param  string 	$from   [characters to be replaced]
	 * @param  string 	$to     [new characters to replace]
	 * @return string.
	 */
	public static function replace($value, $from = " ", $to = "_")
	{
		return str_replace($from, $to, $value);
	}


	/**
	 * Trim the text with a specific length.
	 * @param  string 	$value  	[value]
	 * @param  integer 	$length 	[length]
	 * @param  integer 	$default  	[default value]
	 * @return string.
	 */
	public static function trim($value, $length, $default = 0)
	{
		return substr($value, $default, $length);
	}


	/**
	 * Check if the string has certain elements.
	 * @param  type  			$value  	[value]
	 * @param  array|string   	$pattern 	[pattern]
	 * @return boolean.
	 */
	public static function check($value, $patterns)
	{
		foreach ((array) $patterns as $pattern) 
		{
			$pos = strpos($value, $pattern);

			if($pos !== FALSE)
			{
				return TRUE;
			}
		}

		return 0;
	}


	/**
	 * Format the numbers into english format number (2,345.00).
	 * @param  integer $value   [value]
	 * @param  integer $decimal [number of decimal places]
	 * @return integer.
	 */
	public static function number($value, $decimal = 0)
	{
		return number_format($value, $decimal, '.', ',');
	}


	/**
	 * Prepend zero in front of a digit for numbers less than 10.
	 * @param  integer $value [number]
	 * @return integer.
	 */
	public static function prependzero($number, $zeros = 2)
	{
		$format = "%0".$zeros."d";
		return sprintf($format, $number);
	}


	/**
	 * URL encode the value. 
	 * @param  string $value [value]
	 * @return string.
	 */
	public static function encode($value)
	{
		return urlencode($value);
	}


	/**
	 * URL decode the value. 
	 * @param  string $value [value]
	 * @return string.
	 */
	public static function decode($value)
	{
		return urldecode($value);
	}




 }
