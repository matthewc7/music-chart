<?php

class Logs {

	/**
	 * Write logs
	 * @param  string 	$function 	[function name]
	 * @param  string 	$status  	[status]
	 * @param  string 	$message  	[message]
	 * @return void.
	 */
	public static function write($function, $status, $message)
	{
		$logfile = Config::get('logs::default.log_path').Config::get('logs::default.log_filename').'.log';
		$format = '%1$s 	: 	%2$s 	'.Config::get('logs::default.log_time_format').'	--> %3$s'."\n";

		$content = sprintf($format, Str::upper($function), Str::upper($status), $message);

		if(!file_exists($logfile))
		{
			$identifier = '=======================  ' . $_SERVER['SERVER_NAME'] . '  ======================='; 
			file_put_contents($logfile, $identifier, LOCK_EX);
		}

		file_put_contents($logfile, $content, LOCK_EX | FILE_APPEND);
	}

}